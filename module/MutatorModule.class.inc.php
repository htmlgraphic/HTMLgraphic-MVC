<?php

Loader::load('module', 'Module');

abstract class MutatorModule extends Module
{

  private $mutation_pointer;

  abstract function getMutationNames();

  /**
   * Attempts the mutation.
   * 
   * If the mutation completes abnormally, previous mutators are aborted.
   * 
   * Otherwise returns the results of the mutation.
   * 
   */
  final function mutate($class, $name, $arguments)
  {
    $this->mutation_pointer[] = $name;
    $result = DBObject::mutator($class, $name, $arguments)->activate();
    if ($result)
    {
      $mutation = new stdclass();
      $mutation->result = $result;
      $mutation->class = $class;
      $mutation->name = $name;
      $mutation->arguments = $arguments;

      $this->mutated($mutation);

      return $result;
    }
    else
    {
      $this->abort();
    }
  }

  function activate($mutation_name)
  {
    $this->mutation_name = $mutation_name;
    if ($this->execute() === false)
      return false;
  }

  final function execute()
  {
    $func = $this->mutation_name;
    if (is_callable(array($this, $func)))
      $this->$func();
  }

  final function mutated($mutation)
  {
    $this->mutated[$mutation->class][$mutation->result->getID()] = $mutation;
  }

  /**
   * 
   * HIGHLY EXPERIMENTAL!!!!! May do more harm than good.
   * 
   * 
   */
  final function abort()
  {
    $func = "abort_" . $this->mutation_name;
    if (is_callable(array($this, $func)))
    {
      $this->$func();
      return;
    }

    //Generic abort logic goes here.
  }

}

?>