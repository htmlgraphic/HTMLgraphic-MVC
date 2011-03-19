<?php

Loader::load('utility', "database/mysql/Database.class.inc.php");

class DatabaseFactory
{

  private static function get_db_settings($settings_file)
  {
    include(Config::get('UtilityRoot') . "/database/mysql/settings/" . $settings_file . ".php");
    if (!isset($settings))
    {
      $settings = array();

      if (isset($dbhost))
        $settings['host'] = $dbhost;
      if (isset($dbuser))
        $settings['username'] = $dbuser;
      if (isset($dbpass))
        $settings['passwd'] = $dbpass;
      if (isset($db))
        $settings['dbname'] = $db;
      if (isset($port))
        $settings['port'] = $port;
      if (isset($timeout))
        $settings['timeout'] = $timeout;
    }
    //if dev box, intercept here.
    /* if (Config::isMyDevBox() || Config::isTheDevBox())
      {
      //$pool = "development-personal";
      $pool = "development-shared";
      //$pool = "production-reporting";

      switch ($pool)
      {
      case "development-shared":
      if ($settings["host"] == "172.16.3.21" || $settings["host"] == "172.16.3.22" || $settings["host"] == "10.40.79.2")
      $settings["host"] = "10.9.16.250"; // DBD-A-01.xyz_company.lan – 10.9.16.250
      else if ($settings["host"] == "172.16.3.16" || $settings["host"] == "172.16.3.15")
      $settings["host"] = "10.9.16.251"; // DBD-H-01.xyz_company.lan – 10.9.16.251
      else if ($settings["host"] == "172.16.3.12" || $settings["host"] == "172.16.3.11")
      {
      $settings["port"] = 3306;
      $settings["host"] = "10.9.16.252"; // DBD-S-01 .xyz_company.lan - 10.9.16.252
      }
      else if ($settings["host"] == "172.16.20.2") //fs2
      $settings["host"] = "10.9.16.250"; // DBD-A-01.xyz_company.lan – 10.9.16.250
      break;
      case "production-reporting":
      if ($settings["host"] == "172.16.3.21" || $settings["host"] == "172.16.3.22")
      $settings["host"] = "172.16.40.3"; // DBS-A-05:.xyz_company.lan – 10.16.254.10
      else if ($settings["host"] == "172.16.3.16" || $settings["host"] == "172.16.3.15")
      $settings["host"] = "172.16.40.9"; // DBS-H-02.xyz_company.lan – 10.16.254.20
      else if ($settings["host"] == "172.16.3.12" || $settings["host"] == "172.16.3.11")
      {
      $settings["port"] = 3306;
      $settings["host"] = "172.16.50.4"; // DBS-S-02 .xyz_company.lan - 10.16.254.30
      }
      break;
      case "development-personal":
      default:

      $stats = array("stats");
      if (in_array($settings["dbname"], $stats))
      {
      $settings["port"] = 3307;
      }
      //error_log("use localhost: " . print_r($settings,true));
      $settings['host'] = "127.0.0.1";
      }
      //print_r($settings);
      }
     */

    return $settings;
  }

  private static $passinggreen_db_instance = null;

  static function passinggreen_db()
  {
    if (DatabaseFactory::$passinggreen_db_instance == null)
    {
      $write = DatabaseFactory::get_db_settings("com/passinggreen/cfg_write");
      $read = DatabaseFactory::get_db_settings("com/passinggreen/cfg_read");

      DatabaseFactory::$passinggreen_db_instance = new Database($read, $write);
    }
    return DatabaseFactory::$passinggreen_db_instance;
  }

  private static $passinggreen_master_db_instance = null;

  static function passinggreen_master_db()
  {
    if (DatabaseFactory::$passinggreen_master_db_instance == null)
    {
      $write = DatabaseFactory::get_db_settings("com/passinggreen/cfg_write");
      $read = DatabaseFactory::get_db_settings("com/passinggreen/cfg_master_read");

      DatabaseFactory::$passinggreen_master_db_instance = new Database($read, $write);
    }
    return DatabaseFactory::$passinggreen_master_db_instance;
  }

}

?>