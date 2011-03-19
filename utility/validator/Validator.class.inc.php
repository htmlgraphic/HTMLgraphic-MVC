<?php

class Validator
{

  protected $text, $fields, $object;

  function __construct($class, $fields, $object)
  {
    $this->object = $object;
    $this->fields = $fields;
    foreach ($fields as $field)
      $this->text .= call_user_func(array($object, "get{$field}"));
  }

  function _log($message, $field, $replaces)
  {
    if (is_array($replaces))
    {
      foreach ($replaces as $key => $val)
        $message = str_replace("%__{$key}__%", $val, $message);
    }

    MessageLogger::log(str_replace("%__FIELD__%", $field, $message), "{$field}_issue");
  }

  function logMessages($array=null)
  {
    foreach ($this->fields as $field)
      $this->_log($this->message, $field, $array);
  }

}

?>