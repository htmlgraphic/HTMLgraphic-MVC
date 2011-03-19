<?php

Loader::load("utility", "response/MessageLogger");

abstract class Mutator
{

  function __construct()
  {
    
  }

  abstract protected function execute();

  protected final function aborting($reason=null)
  {
    $this->abortTransaction($reason);
  }

  private $success_data;

  final function setData($data)
  {
    $this->success_data = $data;
  }

  final function logUserMessage($message, $type = "msg")
  {
    MessageLogger::log($message, $type);
  }

  public final function activate()
  {
    if ($this->execute() === false)
      return;
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

  function getTransactionsForDelete()
  {
    return null;
  }

  function getMessagesForDelete()
  {
    return null;
  }

  function getEmailsForDelete()
  {
    return null;
  }

  function getTransactionsForChanges($insert = false)
  {
    return null;
  }

  function getMessagesForChanges($insert = false)
  {
    return null;
  }

  function getEmailsForChanges($insert = false)
  {
    return null;
  }

}

?>