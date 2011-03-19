<?php

Loader::load("utility", "response/MessageLogger");

abstract class Module
{

  protected $success, $failure, $hasFailures = false;

  function __construct()
  {
    Debugger::log("MODULE: <span style=\"color:#B72F09;\">" . get_class($this) . "</span>");
  }

  function activate()
  {
    return $this->execute();
  }

  abstract protected function execute();

  function logSuccess($message)
  {
    MessageLogger::log($message, get_class($this) . "_success");
  }

  function logFailure($message)
  {
    MessageLogger::log($message, get_class($this) . "_failure");
  }

  function getSuccessMessages()
  {
    return MessageLogger::getMessages(get_class($this) . "_success");
  }

  function getFailureMessages()
  {
    return MessageLogger::getMessages(get_class($this) . "_failure");
  }

}

?>