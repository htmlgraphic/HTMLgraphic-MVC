<?php

Loader::load("utility", array(
            "database/DatabaseFactory",
            "response/MessageLogger"));

abstract class Cron
{

  function __construct()
  {
    
  }

  abstract protected function execute();

  abstract protected function name();

  protected final function aborting($reason=null)
  {
    $this->abortTransaction($reason);
  }

  final function logUserMessage($message, $type = "msg")
  {
    MessageLogger::log($message, $type);
  }

  public final function activate()
  {
    if ($this->execute() === false)
    {
      error_log("Cron {$this->name()} failed at " . date("Y-m-d H:i:s"));
      return;
    }
    else
      return $this->success_data;
  }

  private $failed_msg;
  private $failed = false;

  final protected function setFailedMessage($msg)
  {
    $this->failed = true;
    if (isset($msg))
      $this->failed_msg = $msg;
  }

}

?>