<?php

class ControllerResponse implements IteratorAggregate
{

  private $user_message;
  private $data;
  private $aborted;
  private $abort;
  private $transaction;

  private function transactionResponse()
  {
    if (!isset($this->transaction[$this->transaction_index]))
    {
      $this->transaction[$this->transaction_index] = new ControllerResponse();
    }

    return $this->transaction[$this->transaction_index];
  }

  private function clearTransactionResponse()
  {
    if (isset($this->transaction[$this->transaction_index]))
    {
      //Debugger::log("remove transaction at {$this->transaction_index}");
      unset($this->transaction[$this->transaction_index]);
    }
  }

  private $transaction_index = 0;

  public function beginTransaction($name)
  {
    //Debugger::log("begin transaction $name");
    $this->name = $name;
    $this->transaction_index++;
  }

  private $name;

  public function endTransaction()
  {
    //if($this->name != $name)
    //	throw new Exception("Unable to end transaction $name. Currently executing {$this->name}.");
    //Debugger::log("end transaction {$this->name}");
    $response = $this->transactionResponse();
    //Debugger::log("response: $response");

    foreach ((array)$response->user_message as $key => $value)
    {
      $this->user_message[$key] = array_merge((array)$this->user_message[$key], $value);
    }

    $data = $response->data;
    /*
      if(is_object($response->data))
      $this->data[$this->name][] = $response->data;
      else if(is_array($response->data))
      {
      $this->data[$this->name] = array_merge((array)$this->data[$this->name],$response->data);

      }
      else
      $this->data[$this->name][] = (array)$response->data;
     */

    $this->clearTransactionResponse();
    $this->transaction_index--;
    return $data;
  }

  public function abortTransaction($msg=null)
  {
    //if($this->name != $name)
    //	throw new Exception("Unable to abort transaction $name. Currently executing {$this->name}.");
    //Debugger::log("abort transaction {$this->name}");	
    if (isset($msg))
      $this->abort[$this->name][] = $msg;
    $this->aborted[$this->name] = true;
    $this->clearTransactionResponse();
    $this->transaction_index--;
  }

  public function addUserMessage($msg, $type="msg")
  {
    if ($this->transaction_index == 0)
      $this->user_message[$type][] = $msg;
    else
      $this->transactionResponse()->user_message[$type][] = $msg;
  }

  public function addErrorMessage($msg, $type="error_msg")
  {
    $this->addUserMessage($msg, $type);
  }

  public function getUserMessages()
  {
    return $this->user_message;
  }

  public function setData($data)
  {
    //Debugger::log("{$this->name} setting data");
    $this->transactionResponse()->data = $data;
  }

  /*
    public function getData($name)
    {
    if(isset($this->data[$name]))
    return $this->data[$name];
    }
   */

  private $internal = array();

  public function internal($msg)
  {
    $this->internal[] = $msg;
  }

  private $profiler = array();

  public function profiler($msg)
  {
    $this->profiler[] = $msg;
  }

  public function withCallback($callback)
  {
    echo "$callback($this)";
  }

  public function getIterator()
  {
    $array["messages"] = $this->user_message;
    $array["abort"] = $this->abort;
    return new ArrayIterator($array);
  }

  public function __toString()
  {
    return json_encode($this->getIterator());
  }

}

?>
