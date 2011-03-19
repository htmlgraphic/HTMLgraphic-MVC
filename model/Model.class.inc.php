<?php

Loader::load('controller', '/controller/ModelController');

class Model
{

  function __destruct()
  {
    foreach ($this as $key => $val)
    {
      if (is_object($this->$key))
      {
        unset($this->$key);
      }
    }
  }

  const HIGH_CONCURRENCY_MODE = "high";
  const NORMAL_CONCURRENCY_MODE = "normal";
  const LOW_CONCURRENCY_MODE = "low";

  private static function getConcurrencyMode()
  {
    return self::$concurrency_mode;
  }

  private $mutator;

  public function setMutator(Mutator& $mutator)
  {
    $this->mutator = $mutator;
  }

  public function getMutator()
  {
    return $this->mutator;
  }

  public function hasMutator()
  {
    return isset($this->mutator);
  }

  //private $model_controller;
  public function setModelController($controller)//ModelController &$controller)
  {
    $this->mutator = $controller;
    //$this->model_controller = $controller;
  }

  public function getModelController()
  {

    return $this->getMutator();
    //return $this->model_controller;
  }

  public function hasModelController()
  {
    return $this->hasMutator();
    //return isset($this->model_controller);
  }

  private static function get_class_path($class)
  {
    $reflection = new ReflectionClass($class);
    $path = $reflection->getFileName();
    $start_pos = "class-repository/model/";
    $start = strpos($path, "class-repository/model/");
    $model_include = substr($path, $start + strlen($start_pos));

    $end_pos = "{$class}.class.inc.php";
    $end = strpos($model_include, $end_pos);
    $path = substr($model_include, 0, $end);

    return $path;
  }

  public static function collection($class, $consistency = null)
  {

    $reflection = new ReflectionClass($class);
    $object = $reflection->newInstance();

    if (is_a($object, "CassandraObject"))
    {
      Loader::load("model", "CassandraObjectCollection");
      /*
        if(isset($consistency) && is_string($consistency))
        {
        switch($consistency)
        {

        case self::CONSISTENCY_ABSOLUTE:
        if(!$reflection->hasMethod("master_db"))
        throw new Exception("No master database connection defined. Unable to provide consistency level required.");
        $db = $object->master_db();
        break;
        case self::CONSISTENCY_REPORTING:
        if(!$reflection->hasMethod("reporting_db"))
        throw new Exception("No reporting database connection defined. Unable to provide consistency level required.");
        $db = $object->reporting_db();
        break;

        }
        }
       */

      $collection = new CassandraObjectCollection($class, $object->keyspace(), $object->column_family(), $object->thrift_connection(), $consistency);
    }

    return $collection;
  }

  public static function mutator($class, $type, $args=null)
  {
    $reflection = new ReflectionClass($class);

    $path = self::get_class_path($class);

    Debugger::log("MUTATOR: <span style=\"color: #DAA2FF;\">$path{$type}{$class}</span>");

    Loader::load("mutator", "$path{$type}{$class}.class.inc.php");

    $creatorReflection = new ReflectionClass("{$type}{$class}");
    if (isset($args))
      $creator = $creatorReflection->newInstanceArgs($args);
    else
      $creator = $creatorReflection->newInstance();

    return $creator;
  }

}

?>