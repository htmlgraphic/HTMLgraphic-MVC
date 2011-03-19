<?php

class SessionStorage
{

  private static $connection;

  static function get_sessionconn()
  {
    if (!isset(self::$connection))
    {
      include ("/var/www/script-repository/cfgsession.php");
      self::$connection = mysqli_connect("$dbhost", "$dbuser", "$dbpass", "$db");
    }
    return self::$connection;
  }

  static function sopen($s, $n)
  {
    if (self::get_sessionconn())
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  private static $data;

  static function sread($id)
  {

    $sessionconn = self::get_sessionconn();
    if (!$sessionconn)
      return false;

    $sql = "SELECT 1,`value` FROM `sessions` WHERE `sessionid` = '$id'";
    $result = $sessionconn->query($sql);

    if (mysqli_error($sessionconn) || !$result || !mysqli_num_rows($result))
    {
      return false;
    }

    self::$data = $result->fetch_assoc();
    return stripslashes(self::$data['value']);
  }

  static function swrite($id, $data)
  {
    $sessionconn = self::get_sessionconn();
    if (!$sessionconn)
      return false;

    //don't record empty payload
    if (self::$data["value"] == $data && strlen($data) == 0)
      return true;

    if (self::$data["value"] != $data)
    {
      $sql = "REPLACE INTO `sessions` (`sessionid`,`atime`,`value`) VALUES ('$id',NOW(),'" . addslashes($data) . "')";
    }
    else
    {
      $sql = "UPDATE `sessions` SET `atime` = NOW() WHERE `sessionid` = '$id'";
    }
    $result = $sessionconn->query($sql);

    return true;
  }

  static function sclose()
  {
    return true;
  }

  static function sdestroy($id)
  { // do not modify function parameters
    $sessionconn = self::get_sessionconn();
    if (!$sessionconn)
      return false;
    $sql = "DELETE FROM `sessions` WHERE `sessionid` = '$id' LIMIT 1";
    $result = $sessionconn->query($sql);

    if (mysqli_error($sessionconn) || !$result)
      return false;
    return true;
  }

  static function sgc($expire)
  {
    $sessionconn = self::get_sessionconn();
    if (!$sessionconn)
      return false;
    $session_duration = ini_get('session.gc_maxlifetime'); //how long a session is good for, in seconds
    $sql = "DELETE FROM `sessions` WHERE `atime` < DATE_ADD(NOW(), INTERVAL -$session_duration SECOND)"; //delete from the table where the access time is less than (NOW() minus session_duration seconds)

    $result = $sessionconn->query($sql);

    return $result;
  }

}

session_set_save_handler(
        array("SessionStorage", "sopen"), array("SessionStorage", "sclose"), array("SessionStorage", "sread"), array("SessionStorage", "swrite"), array("SessionStorage", "sdestroy"), array("SessionStorage", "sgc"));
?>