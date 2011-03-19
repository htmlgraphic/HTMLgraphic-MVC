<?php

class Template
{

  static function insert($view_file, $args = array())
  {
    extract($args);
    include(Loader::getPath('view', $view_file));
  }

  static function get($file, $args = array())
  {
    ob_start();
    self::insert($file, $args);
    $output = ob_get_contents();
    ob_end_clean();

    return (string)$output;
  }

}

?>