<?php

Loader :: load('utility', "database/mysql/DatabaseFactory");

/**
 * A class for managing sessions across all websites
 */
class Session
{

  private static $MEMBER_LOGIN_KEY = 'hg_login_user_id'; //members
  private static $EMPLOYEE_LOGIN_KEY = 'hg_login_admin_id'; //employees
  /**
   * Construct a new session. Typically session_id is transparent and not needed.
   * Pass a session here if you are administratively hijacking a session - to invalidate it upon account terminiation, etc
   *
   * @param mixed $session_id
   */
  private static $instance;

  public function instance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new Session();
    }
    return self::$instance;
  }

  private function __construct($session_id= null)
  {
    //TODO: is there a better place for these ini settings?
    ini_set("session.cookie_path", "/");
    Loader::load("utility", "url/URL");
    $parts = explode(".", URL::getCurrentArr('host'));
    $domain = implode(".", array_slice($parts, -2));
    ini_set("session.cookie_domain", $domain);
    ini_set("session.use_only_cookies", "1"); //forces users to accept cookies, but more secure
    //ini_set("session.use_trans_sid", "0"); //Do not enable this! Session IDs in the URL are bad, m'kay?

    if (!is_null($session_id))
    {
      session_id($session_id); //must be set BEFORE starting a session, if null, session id will pull from COOKIE, if no COOKIE, a new session si created
    }

    session_start(); //we call this in the constructor as it is vital this is done before any kind of output is generated
  }

  public function clearValueForKey($key)
  {
    $_SESSION[$key] = null;
  }

  public function getValueForKey($key)
  {
    if (isset($_SESSION[$key]))
      return $_SESSION[$key];
  }

  public function setValueForKey($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function removeKey($key)
  {
    unset($_SESSION[$key]);
  }

  public function getModelDefaultForClassName($name)
  {
    $key = strtoupper($name) . "_LOGIN_KEY";

    $value = $this->getValueForKey(self::$$key);
    if (isset($value) && strlen($value))
    {
      $reflection = new ReflectionClass($name);

      $instance = $reflection->newInstanceArgs(array($value));

      return $instance;
    }
  }

  public function setModelDefault(Model $model)
  {
    $name = get_class($model);
    $key = strtoupper($name) . "_LOGIN_KEY";

    $this->setValueForKey(self::$$key, $model->getID());
  }

  public function clearModelDefaultForClassName($name)
  {
    $key = strtoupper($name) . "_LOGIN_KEY";

    $value = $this->clearValueForKey(self::$$key);
  }

}

?>