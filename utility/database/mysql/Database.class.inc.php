<?php

/*
 *
 * A database class!
 *
 * You should avoid creating one of these directly. (Translation: DO NOT CREATE A DATABASE DIRECTLY!!!!1111one!!!!)
 * Instead use DatabaseFactory to create common Database types.
 *
 * The constructor takes in read write settings. If only read settings are provied. Execute will fail every time.
 * If read settings are not provided, the write settings will be used.
 *
 * As errors are encountered they are stored in an array. Chances are you'll only ever need to call get_last_error()
 * Errors are wrapped in the MySqliError class, which contains the error number and the mysql error message.
 *
 * query() will use the read only connection settings. Query returns a SQL result.
 * execute() will use the write only connection settings. If write settings are not provided, it will return false.
 * 	There is no real reason for this, but it's for future expandability.
 *
 * get_result() returns an object called MySqliResult which has convience functions.
 *
 */

Loader::load('utility', array(
            'database/mysql/MySqliError',
            'database/mysql/MySqliResult'
        ));

class Database
{

  private $read = null;
  private $write = null;
  private $read_settings = null;
  private $write_settings = null;
  private $server = null;
  private $username = null;
  private $password = null;
  private $name = null;
  private $errors = array();
  private $prepared_connections = array();
  private $state = null;
  private static $MAX_ERRORS = 100;

  //Create a database object with the given settings. No connections are made until they are needed.	
  function __construct($read=null, $write=null)
  {
    $this->write_settings = $write;
    $this->read_settings = $read;
  }

  public function getName()
  {
    if (isset($this->read_settings) && isset($this->read_settings["dbname"]))
      return $this->read_settings["dbname"];
    if (isset($this->write_settings) && isset($this->write_settings["dbname"]))
      return $this->write_settings["dbname"];
  }

  //Destory a database object. All we need to do it close the connection.
  function __destruct()
  {
    $this->disconnect();
  }

  private function create_connection_with_settings($settings, $retry_count = 0)
  {
    $dbc = mysqli_init();

    //set options
    if (isset($settings["timeout"]))
    {
      $dbc->options(MYSQLI_OPT_CONNECT_TIMEOUT, (int)$settings["timeout"]); //if unable to connect after x seconds, give up
    }
    //end set options
    //create actual connection

    if (isset($settings["port"]))
    {
      $dbc->real_connect($settings["host"], $settings["username"], $settings["passwd"], $settings["dbname"], $settings["port"]);
    }
    else
    {
      $dbc->real_connect($settings["host"], $settings["username"], $settings["passwd"], $settings["dbname"]);
    }

    //end creation of connect
    //check connection success
    if (mysqli_connect_errno())
    {
      if ($retry_count < 3)
      {
        $retry_count++;
        Debugger::log("Failed connection to database. Retry count: {$retry_count}. Retrying again in 1 second");
        error_log("Failed connection to database. Retry count: {$retry_count}. Retrying again in 1 second");
        sleep(1);
        return self::create_connection_with_settings($settings, $retry_count);
      }
      error_log("Database connection failed: " . mysqli_connect_error());

      /* record error */
      $error = new MySqliError(mysqli_connect_errno(), mysqli_connect_error());
      $this->connection_errors[] = $error;

      //$this->errors[] = $error;
      return null;
    }
    else
    {
      $sql = "SHOW SLAVE STATUS";
      $result = $dbc->query($sql);
      if ($result && $slave_status = $result->fetch_object())
      {
        $concurrency = $slave_status->Seconds_Behind_Master;
        if ($concurrency >= 30 && $retry_count < 3)
        {
          $retry_count++;
          //error_log("Reading from stale slave. $concurrency seconds behind master. Retry count $retry_count");
          Debugger::log("Reading from stale slave. $concurrency seconds behind master. Retry count $retry_count");
          return $this->create_connection_with_settings($settings, $retry_count);
        }
        else if ($retry_count >= 5)
        {
          error_log("Reading from stale slave. $concurrency seconds behind master. Out-of-retry count $retry_count");
          Debugger::log("Reading from stale slave. $concurrency seconds behind master. Out-of-retry count $retry_count");
        }
      }
    }
    return $dbc;
  }

  private function write_connection()
  {
    if ($this->write == null || !$this->write->ping())
    {
      if ($this->write_settings == null)
        return false;
      $dbc = $this->create_connection_with_settings($this->write_settings);
      if ($dbc == null)
        return false;
      $this->write = $dbc;
    }
    return $this->write;
  }

  //Create the read database connection. If there is an error it is stored and false is returned. Otherwise true.
  //If not read settings are given, the write settings are tried.
  private function read_connection()
  {
    if ($this->read == null || !$this->read->ping())
    {
      if ($this->read_settings == null)
      {
        //no separate settings for reading, use the write settings.
        $dbc = $this->write_connection();
        if ($dbc)
          $this->read = $dbc;
      }
      else
      {
        $dbc = $this->create_connection_with_settings($this->read_settings);
        if ($dbc == null)
          return false;
        $this->read = $dbc;
      }
    }
    return $this->read;
  }

  //If we've actaully managed to create a connection, this will close it.
  private function disconnect()
  {
    if ($this->write_settings != null && $this->write != null)
      $this->write->close();
    if ($this->read_settings != null && $this->read != null)
      $this->read->close();

    unset($this->write);
    unset($this->read);
  }

  public function escape_string($string)
  {
    if (!$this->read_connection())
    {
      $settings = ($this->read_settings == null) ? $this->write_settings : $this->read_settings;
      $attempt = "host: " . $settings["host"] . " user: " . $settings["username"] . " db: " . $settings["dbname"];
      $attempt .= "(using " . (($this->read_settings == null) ? "write" : "read") . " settings";
      throw new Exception("Database Connection is not Available. $attempt " . print_r($this->connection_errors, true));
    }
    return $this->read->real_escape_string($string);
  }

  public static function filter_user_string($input)
  {
    $reg = "/\b((delete)|(update)|(union)|(insert)|(drop)|(alter)|(set)|(into)|(join))\b/i";
    return(preg_replace($reg, "", $input));
  }

  //Query the database. If the database connection doesn't exist this will attempt to make it.
  //If this returns false, you can use get_last_error() or get_errors() for error handling.
  //If the query is successful, the MySQL result is returned.
  function query($sql)
  {
    $start = microtime(true);
    $this->result = null;

    if (substr($sql, 0, 5) == "SELECT")
    {
      throw new Exception("Only SELECT is allowed. Use execute() for all other SQL Statements.");
      return false;
    }
    if (!$this->read_connection())
    {

      $settings = ($this->read_settings == null) ? $this->write_settings : $this->read_settings;
      $attempt = "host: " . $settings["host"] . " user: " . $settings["username"] . " db: " . $settings["dbname"];

      throw new Exception("Read connection not established.\n $attempt\n " . print_r($this->connection_errors, true));
      return false;
    }
    $start = microtime();
    $start = explode(" ", $start);
    $start = $start[1] + $start[0];

    if ((isset($this->read_settings) && isset($this->read_settings["allow_query_comments"]) && !$this->read_settings["allow_query_comments"] ) ||
            (isset($this->write_settings) && isset($this->write_settings["allow_query_comments"]) && !$this->write_settings["allow_query_comments"]))
    {
      //disabled for sql engines that don't support comments, such as SphinxQL
    }
    else if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI']))
    {
      $sql .= " /* REQUEST=" . str_replace("*", "", $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . " */"; //add debug output for slow query log, processlist, etc
    }
    else
    {
      $sql .= " /* REQUEST=" . $_SERVER['SCRIPT_FILENAME'] . " */"; //crons and other command line scripts
    }

    $this->result = $this->read->query($sql);
    $end = microtime();
    $end = explode(" ", $end);
    $end = $end[1] + $end[0];

    if (isset($this->read_settings['server']))
      $database_name = $this->read_settings['server'] . " (" . $this->read_settings["host"];
    else
      $database_name = " (" . $this->read_settings["host"];

    if (isset($this->read_settings["port"]))
      $database_name .= ":" . $this->read_settings["port"];

    $database_name .= ")";

    Debugger::query($sql, $end - $start, $database_name);

    /*
      $output = "$sql<br><br>";
      foreach(debug_backtrace() as $trace)
      {
      if(isset($trace['file']) && isset($trace['line']))
      $output .= "File: {$trace['file']} Line: {$trace['line']}<br>";
      }
      Debugger::log($output);
     */
    if (!$this->result && $this->read->errno == 1205)
    {
      error_log("Database::query() failed with error 1205, retrying in 3 seconds.");
      sleep(3);
      $this->result = $this->read->query($sql);
    }

    if (!$this->result)
    {
      if (count($this->errors) > self::$MAX_ERRORS)
      {
        array_shift($this->errors);
      }

      $error = new MySqliError($this->read->errno, $this->read->error, $sql);
      $this->errors[] = $error;
      return false;
    }

    return $this->result;
  }

  function prepare_query($prepared_statement)
  {
    if (substr($prepared_statement, 0, 5) == "SELECT")
      return false;

    $db = new Database($this->read_settings, $this->write_settings);
    $this->prepared_connections[] = $db;
    if (!$db->read_connection())
      return false;

    $start = microtime();
    $start = explode(" ", $start);
    $start = $start[1] + $start[0];

    if ($stmt = $db->read->prepare($prepared_statement))
    {
      $end = microtime();
      $end = explode(" ", $end);
      $end = $end[1] + $end[0];


      if (isset($this->read_settings['server']))
        $database_name = $this->read_settings['server'] . " (" . $this->read_settings["host"];
      else
        $database_name = " (" . $this->read_settings["host"];
      if (isset($this->read_settings["port"]))
        $database_name .= ":" . $this->read_settings["port"];
      $database_name .= ")";

      Debugger::query("STATEMENT PREPARED: {$prepared_statement}", $end - $start, $database_name);
      return $stmt;
    }
    else
    {
      $error = new MySqliError($db->read->errno, $db->read->error, $prepared_statement);
      $this->errors[] = $error;
      return false;
    }
  }

  function prepare_execution($prepared_statement)
  {
    if (!$this->write_connection())
    {
      return false;
    }

    return $this->write->prepare($prepared_statement);
  }

  //This wraps the last query result (if there is one) in a MySqliResult object.
  function get_result()
  {
    if ($this->result == null)
      return null;
    return new MySqliResult($this->result);
  }

  //Execute a change on the database. If the database connection doesn't exist this function will attempt to make one.
  //Returns either true is the statement executed successfully.
  //Returns false is the statement did not execute successfully, and stores the error. Use get_last_error or get_errors for handling those.
  function execute($sql)
  {
    if (!$this->write_connection())
    {
      throw new Exception("Write connection not established");
      return false;
    }
    //echo "<br> " . $sql . "<br>";
    $start = microtime(true);
    $result = $this->write->query($sql);
    $end = microtime(true);
    $database_name = $this->write_settings['server'] . " (" . $this->write_settings["host"];
    if (isset($this->write_settings["port"]))
      $database_name .= ":" . $this->write_settings["port"];
    $database_name .= ")";

    if (!$result && $this->write->errno == 1205)
    {
      error_log("Database::execute() failed with error 1205, retrying in 3 seconds.");
      sleep(3);
      $this->result = $this->write->query($sql);
    }
    Debugger::query($sql, $end - $start, $database_name);
    if (!$result)
    {
      $error = new MySqliError($this->write->errno, $this->write->error, $sql);
      if (count($this->errors) > self::$MAX_ERRORS)
        array_shift($this->errors);
      $this->errors[] = $error;
      return false;
    }

    return $result;
  }

  function read_info()
  {
    return $this->read();
  }

  function last_insert_id()
  {
    return $this->write->insert_id;
  }

  function getInsertID()
  {
    return $this->write->insert_id;
  }

  function rows_affected()
  {
    return $this->write->affected_rows;
  }

  function get_last_connection_errors()
  {
    if (count($this->connection_errors) == 0)
      return null;

    end($this->connection_errors);
    return current($this->connection_errors);
  }

  function get_connection_errors()
  {
    $this->getConnectionErrors();
  }

  function getConnectionErrors()
  {
    $this->connection_errors;
  }

  //Returns the last error if there is one.
  function get_last_error()
  {
    return $this->getLastError();
  }

  function getLastError()
  {
    if (count($this->errors) == 0)
      return null;

    end($this->errors);
    return current($this->errors);
  }

  //Returns all the errors that have occurred since the class was created.
  function get_errors()
  {
    return $this->getErrors();
  }

  function getErrors()
  {
    return $this->errors;
  }

  public function __tostring()
  {
    return $this->read_settings['host'] . ":" . $this->read_settings['dbname'];
  }

}

?>