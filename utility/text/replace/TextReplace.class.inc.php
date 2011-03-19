<?php

class TextReplace
{

  private $text;
  private $replacement_variables = array();

  function __construct($replacements = null)
  {
    if (isset($replacements) && is_array($replacements))
      $this->replacement_variables = $replacements;
  }

  function replaceString($string)
  {
    $variables = $this->getReplacementVariables();

    if (is_string($string) && isset($variables) && is_array($variables))
    {

      foreach ($variables as $variable => $replacement)
      {
        $variable = "%__{$variable}__%";
        $string = str_replace($variable, $replacement, $string);
      }
    }

    return $string;
  }

  public function clearReplacements()
  {
    $this->replacement_variables = array();
  }

  public function addReplacementsForObject($object)
  {

    $interfaces = class_implements(get_class($object));

    if (isset($interfaces) && is_array($interfaces) && in_array('CommuniqueTokenReplacement', $interfaces))
    {
      $array = $object->communiqueTokenReplacements();
      $this->addReplacementsFromArray($array);
    }
    else if (isset($interfaces) && is_array($interfaces) && in_array('EmailReplace', $interfaces))
    {
      $array = $object->email_substitutions();
      $this->addReplacementsFromArray($array);
    }
  }

  public function addReplacementsFromArray($array)
  {
    if (is_array($array))
    {

      $variables = $this->getReplacementVariables();
      if (is_array($variables))
        $this->replacement_variables = array_merge($array, $variables);
      else
        $this->replacement_variables = $array;
    }
  }

  private function getReplacementVariables()
  {
    if (!isset($this->replacement_variables))
    {
      $array = array();

      if (isset($_SERVER['REMOTE_ADDR']))
        $array["IP"] = $_SERVER['REMOTE_ADDR'];

      if (isset($_SERVER['HTTP_USER_AGENT']))
        $array["HTTP_USER_AGENT"] = $_SERVER['HTTP_USER_AGENT'];

      if (isset($_SERVER['REMOTE_ADDR']))
        $array["REMOTE_ADDR"] = $_SERVER['REMOTE_ADDR']; //no host, we don't do reverse lookups for performance

      if (isset($_SERVER['HTTP_HOST']))
        $array["HTTP_HOST"] = $_SERVER['HTTP_HOST'];

      if (isset($_SERVER['SERVER_ADDR']))
        $array["SERVER_ADDR"] = $_SERVER['SERVER_ADDR'];

      if (isset($_SERVER['SCRIPT_FILENAME']))
        $array["SCRIPT_FILENAME"] = $_SERVER['SCRIPT_FILENAME'];

      if (isset($_SERVER['HTTP_USER_AGENT']))
        $array["HTTP_USER_AGENT"] = $_SERVER['HTTP_USER_AGENT'];

      $array["DATE"] = date("Y/m/d H:i:s");

      $this->replacement_variables = $array;
    }

    return $this->replacement_variables;
  }

}

?>
