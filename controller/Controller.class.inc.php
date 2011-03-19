<?php

abstract class Controller
{

  function __construct()
  {
    
  }

  abstract function activate();

  /*
   * Method: loadView
   *
   * Outputs a view
   *
   */

  protected function loadView($view, $data = array())
  {
    Debugger::log("VIEW: <span style=\"color:#d28c00;\">$view</span>");
    extract($data);
    include(Loader::getPath('view', $view));
  }

  /*
   * Method: getView
   *
   * Returns a view instead of outputting it (for saving and such)
   *
   */

  protected function getView($view, $data = array())
  {
    ob_start();
    self::loadView($view, $data);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
  }

  /*
   * Method: loadModel
   */

  protected function loadModel($model, $args = array(), $class = null)
  {
    Debugger::log("MODEL: <span style=\"color:#b147bf;\">$model</span>");
    return Loader::loadNew('model', $model, $args, $class);
  }

  protected function redirect($link, $type=301)
  {
    Config::set("Redirect", $link);
    Config::set("RedirectType", $type);

    Loader::loadNew('controller', '/controller/RedirectController')->activate();
    exit();
  }

  protected function error404()
  {
    $controller = '/controller/Error404Controller';
    if (file_exists(Loader::getPath('controller', 'Error404Controller')))
    {
      $controller = 'Error404Controller';
    }
    Loader::loadNew('controller', $controller)->activate();
    exit();
  }

  /*
   * Methods for hitcount
   *
   * Returns true/false based on visitor environment
   */

  protected function isAdmin()
  {
    if (isset($_GET['opt']) && $_GET['opt'] == 'admin')
      return true;
  }

  protected function isBot()
  {
    if (!isset($_SERVER['HTTP_USER_AGENT']) || preg_match('/(bot|java|google|crawler|msn|teoma|baidu|slurp)/i', $_SERVER['HTTP_USER_AGENT']))
      return true;
  }

  protected function referral_terms()
  {
    if (isset($_SERVER['HTTP_REFERER']) &&
            preg_match("/[\?&\/]([qp]|query)=([^\?&\/]*)/i", $_SERVER['HTTP_REFERER'], $matches) &&
            isset($matches[2]))
    { //google, bing
      return urldecode($matches[2]);
    }
  }

}

?>