<?php

abstract class SiteRouter
{

  private $rules = array(),
  $redirect_rules = array(),
  $keys = array(),
  $controller;

  abstract function route();

  function __construct()
  {
    Loader::load('utility', array(
                'url/URL',
                'Request'
            ));
  }

  protected function loadController($controller, $args = array(), $class = null)
  {
    Debugger::log("CONTROLLER: <span style=\"color:#4882c0;\">$controller");
    return Loader::loadNew('controller', $controller, $args, $class);
  }

}

?>