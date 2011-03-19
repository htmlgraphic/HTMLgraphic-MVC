<?php

/**
 * Created on Oct 17, 2008
 *
 * This class is to be used whenever you're creating an object based on a single row in a database table
 *
 * If an object exists across several databases/tables, it is not a DBObject but rather a MutliDBObject
 * 	which is a class that doesn't currently exist. So you could write that, or rethink your class structure
 * 
 * September 21, 2009
 *
 * So this class is a year old now, and contains no documentation on how to create one of these awesome DBObjects.
 *
 * So class, let's start off from the beginning.
 *
 * When working with database there are number of queries that might be sent to a database, this class attempts
 * to assume all responsiblity for queries that apply to a single row.
 * INSERT INTO `table`;
 * SELECT * FROM `table` WHERE `primary_key` = $id LIMIT 1;
 * UPDATE `table` VALUES () WHERE `primary_key` = $id;
 *
 * If a piece of data can be represented by this relationship, it can have a DBObject representation.
 *
 *
 * What is a DBObject?
 *
 * A DBObject requires three functions be defined.
 *
 * 	protected function db();
 * 	protected function table();
 * 	where_clause();
 * 	can_load(); // IMPORTANT: PHP will not force you to implement this method, unlike the others which you must define.
 *
 * The db function should return the Database (class) where the object can be found or inserted.
 * The table function should return a string representing where the object can be found or inserted.
 * The where_clause function returns how to reference this instance in the database and table.
 *
 *
 * What does DBObject do for me?
 *
 * Since DBObject represents a single row in mysql it's main purpose is to manage its state in the table.
 * But wait, there's more!
 *
 * "There's more?"
 * "Oh yeah, there's more."
 *
 * Since DBObject tracks what values in the table have changed, it has functionality to track what
 *  the original value was.
 *
  <code>
  $previous_name = $this->previous_value("name");
  if(isset($previous_name))
  {
  if($previous_name == self::$never_set)
  {
  echo "$previous_name\n";
  }
  else
  {
  echo "Previous name: $previous_name\n";
  }
  }
  else
  {
  echo "Name has not changed\n";
  }
  </code>

 * This means it can be used to determine details for a History Transaction.
 *
 * You subclass can also use these values, to determine when specific transaction, email or message should be sent.
 * SEE:
 * 	getTransactionsForChanges($insert);
 *  getMessagesForChanges($insert);
 *  getEmailsForChanges($insert);
 *  getTransactionsForDelete();
 *  getMessagesForDelete();
 *  getEmailsForDelete();
 *
 *  When the Database insert or update is successful on a save or delete, the Transaction, Email,
 *   or Message objects will be told to store/deliver their contents.
 *
 *
 * So What's this "__get overload is NOT allowed on DBObjects. Sorry." message mean?
 *
 * __get and __set are called when a variable that is not defined in the current context is not defined.
 * Current context creates a mess with inheritence, so it is not used.
 *
 * Instead use:
 * 	$this->getDBValue($column);
 *  $this->setDBValue($column,$new_value);
 *
 * But these are private.
 *
 * Proper construction (Minimal):
 *
  <code>

  class KeyValue extends DBObject
  {
  private static $PRIMARY_KEY = "key";
  private static $VALUE = "value";

  //Your __constructor should only take in the primary_key for the table.
  public function __constructor($id)
  {
  $this->setDBValue(self::$PRIMARY_KEY,$id);
  }

  public function getID()
  {
  return $this->getDBValue(self::$PRIMARY_KEY);
  }

  static function primary_key()
  {
  return self::$PRIMARY_KEY;
  }

  public function can_load()
  {
  $id = $this->getDBValue(self::$PRIMARY_KEY);
  return isset($id);
  }

  protected function db()
  {
  return DatabaseFactory::key_value_db();
  }

  protected function table()
  {
  return "keys_and_values";
  }

  protected function where_clause()
  {
  return "WHERE `".self::$PRIMARY_KEY."` = '" . $this->getDBValue(self::$PRIMARY_KEY) . "'";
  }

  public function getValue()
  {
  return $this->getDBValue(self::VALUE);
  }

  public function setValue($value)
  {
  return $this->setDBValue(self::VALUE,$value);
  }

  //Always create a __destruct() with this definition:
  public function __destruct()
  {
  foreach($this as $key => $val)
  {
  if(is_object($this->$key))
  {
  unset($this->$key);
  }
  }
  parent::__destruct();
  }
  </code>
 *
 *
 *
 * Memcache support:
 * 	To allow your object to be used in MemCache, your object must only be updated through your object.
 *
 * static function allowMemCache()
  {
  return false;
  }
 *
 *
 *
 */
Loader::load('utility', array('memcache/MemCache', 'memcache/HGMemCache',));
Loader::load("model", "Model");
Loader::load('utility', array('email/EmailReplace'));

abstract class DBObject extends Model implements EmailReplace
{
  const CONSISTENCY_ABSOLUTE = "CONSISTENCY_ABSOLUTE";
  const CONSISTENCY_REPORTING = "CONSISTENCY_REPORTING";

  private static $MEMCACHE_TTL = 86400;
  private static $DELETED_FROM_MEMCACHE = "**********DELETED DELETED DELETED DELETED**********";
  private $loaded_from_memcache = false;
  private $object_manager;
  private $attempting_to_load_dbobject_values = false;
  protected $previous = array();
  private $changes = null;
  private $db_data = null;
  private $data = null;
  //place holder for new values.
  protected static $never_set = "Previously unset";
  protected $loaded = false;
  private $is_loading = false;
  private $is_valid = false;
  protected $failures;
  protected $ignore_changes = array('is_valid', 'is_loading');
  protected $unsettable = array();

  //the database this object's table exists in (or should be inserted into).
  abstract protected function db();

  abstract protected function table();

  abstract protected function where_clause();

  protected function db_name()
  {
    return $this->db()->getName();
  }

  final public function getDatabase()
  {
    return $this->db();
  }

  final public function getTableName()
  {
    return $this->table;
  }

  //hack to allow for the horror that is the rating system.
  protected function row_limit()
  {
    return 1;
  }

  /*
    public function test_where_clause()
    {
    $sql = "SELECT * FROM " . $this->table() . " WHERE " . $this->where_clause();
    $results = $this->db()->query($sql);
    $results = new MySqliResult($results);
    $all = $results->fetch_all();
    return count($all) == 1;
    }
   */

  function __construct($id=null)
  {
    if (isset($id))
    {
      $primary_key = $this->primary_key();
      if (isset($primary_key))
        $this->setDBValue($primary_key, $id);
    }
  }

  protected function can_load()
  {
    $primary_key = $this->primary_key();
    if (isset($primary_key))
    {
      $id = $this->getDBValue($primary_key);
      return isset($id);
    }
    return true;
  }

  public function getID()
  {
    return $this->getDBValue($this->primary_key());
  }

  public function __destruct()
  {

    foreach ($this as $key => $val)
    {
      if (is_object($this->$key))
        unset($this->$key);
    }

    parent::__destruct();
  }

  public function dump()
  {
    $dump = array();
    foreach ($this as $key => $val)
    {
      if (is_object($val))
      {
        $dump[$key] = $val->dump();
      }
      else
        $dump[$key] = $val;
    }
    return $dump;
  }

  private $notify_on_field_change;

  final function addNotifyOnChange($object, $field)
  {
    if (!isset($this->notify_on_field_change))
      $this->notify_on_field_change[$field][] = $object;
  }

  protected function add_persistent_model_connection($key, DBObject $object)
  {
    return $this->persistent_model_connections[$key] = $object;
  }

  private $persistent_model_connections;

  private function get_persistent_model_connections()
  {
    return $this->persistent_model_connections;
  }

  /*
    final function getManager()
    {
    return $this->object_manager;
    }
   */

  //array of columns that can only be incremented.
  public function getIncrementalKeys()
  {
    
  }

  final protected function getDBValue($key)
  {
    if ($key != $this->primary_key())
      $this->load();

    return $this->data($key);
  }

  final protected function incrementDBValue($key)
  {
    $increment_keys = $this->getIncrementalKeys();
    if (isset($increment_keys) && count($increment_keys) &&
            in_array($key, $this->getIncrementalKeys()))
    {
      $value = $this->getDBValue($key);
      $value++;
      $this->setDBValue($key, $value);
    }
    else
      throw new Exception("$key is not in incremental keys list");
  }

  final protected function setDBValue($key, $value)
  {

    $this->load();
    //echo "changed $key\n";	
    $this->changed_field($key, $this->getDBValue($key), $value);


    $this->data->$key = $value;
    /*
      $manager = $this->getManager();
      if(isset($manager))
      {
      $manager->objectFieldChanged($this,$key);
      }
      foreach((array)$this->notify_on_field_change as $field => $object)
      {
      $object->objectFieldChanged($this,$key);
      }
     */
  }

  final public function __get($key)
  {

    throw new Exception("__get overload is NOT allowed on DBObjects. Sorry.");
    return $this->getDBValue($key); //)$this->db$key;
  }

  final public function __set($key, $value)
  {
    $this->setDBValue($key, $value);
    //throw new Exception("__set overload is NOT allowed on DBObjects. Sorry.");
    $this->$key = $value;
  }

  final protected function getOriginalDBValue($key)
  {
    if ($this->did_change_field($key))
    {
      return $this->previous_value($key);
    }
    else
    {
      return $this->getDBValue($key);
    }
  }

  final protected function previous_value($key)
  {
    return $this->previous[$key];
  }

  final protected function did_change_field($key)
  {
    return isset($this->previous[$key]);
  }

  protected function add_failure($failure)
  {
    $this->failures[] = $failure;
  }

  public function get_failures()
  {
    return $this->failures;
  }

  protected function changed_field($key, $old, $value, $force=false)
  {
    if ($this->is_loading || $key == "is_loading" || $key == 'failures' || in_array($key, $this->ignore_changes))
    {
      return false;
    }


    if (!isset($this->previous[$key]) && ($old != $value || $force))
    {

      if ($old == null)
        $this->previous[$key] = self::$never_set;
      else
        $this->previous[$key] = $old;
    }
  }

  static function primary_key()
  {
    
  }

  final protected function store($key, $value)
  {

    $this->data->$key = $value;
  }

  static function dummyMode()
  {
    return true;
  }

  //Quick Memcache List:
//	static function allowMemCache()	{	return true;	}
//	protected function getMemCacheKey()	{	return "unique_memcache_key({$this->getID()})";	}
  static function allowMemCache()
  {
    return false;
  }

  static function getMemcachedServer()
  {
    $instance = Config::get("MemcachedInstance");

    if (isset($instance) && strlen($instance))
      return $instance;
    return "EAMemCache";
  }

  static function isMemcachedAvailable()
  {
    return call_user_func(array(self::getMemcachedServer(), "isAvailable"));
  }

  static function getMemcached($key)
  {
    return call_user_func_array(array(self::getMemcachedServer(), "get"), array($key));
  }

  static function setMemcached($key, $value, $ttl=0)
  {
    return call_user_func_array(array(self::getMemcachedServer(), "set"), array($key, $value, $ttl));
  }

  private function loadFromMemCache()
  {

    if ($this->allowMemCache() && self::isMemcachedAvailable())
    {
      $key = $this->getMemCacheKey();
      $data = self::getMemcached($key);
      if (isset($data) && is_object($data))
      {
        $this->loaded = true;
        $this->load_from_sql_result($data);
        $this->loaded_from_memcache = true;
        if ($this->getMemCacheKey() != $key)
        {
          error_log("Memcache returned unexpected results. This is not good. {$this->getMemCacheKey()} != $key");
          $this->loaded = false;
          $this->loaded_from_memcache = false;
        }
      }
    }
    else if ($this->allowMemCache() && !self::isMemcachedAvailable())
    {
      error_log("mem cache server unavailable " . $this->getMemCacheKey());
    }
  }

  private function updateInMemCache()
  {
    if ($this->allowMemCache() && self::isMemcachedAvailable())
    {
      $memcacheData = (array)$this->data();
      $memcacheData["_memcache_mtime"] = time();
      $memcacheData = (object)$memcacheData;
      self::setMemcached($this->getMemCacheKey(), $memcacheData, self::$MEMCACHE_TTL);
    }
    else if ($this->allowMemCache())
    {
      error_log("error: object exists in memcache (and needs to be UPDATED!), but memcache is no longer available.");
    }
  }

  private function removeFromMemCache()
  {
    if ($this->allowMemCache() && self::isMemcachedAvailable())
    {
      $data = self::setMemcached($this->getMemCacheKey(), self::$DELETED_FROM_MEMCACHE, self::$MEMCACHE_TTL);
    }
    else if ($this->allowMemCache())
    {
      error_log("error: object exists in memcache (and needs to be DELETED!), but memcache is no longer available.");
    }
  }

  protected function getMemCacheKey()
  {
    return self::get_select_sql($this);
  }

  /*
   * Load the values from the table, if no values are given. All columns are loaded.
   */

  final protected function load($values=null)
  {

    if ($this->attempting_to_load_dbobject_values)
      return;
    else
      $this->attempting_to_load_dbobject_values = true;

    if (!$this->loaded && $this->can_load())
    {
      if ($this->allowMemCache() && self::isMemcachedAvailable())
      {
        $this->loadFromMemCache();
      }

      if (!$this->loaded && $this->can_load())
      {
        $this->loaded = true;
        DBObject::_create($this, $values);
        $this->additional_loads($values);
      }
    }

    $this->attempting_to_load_dbobject_values = false;
  }

  protected function additional_loads($values = null)
  {
    
  }

  /*
   * Reload the object from the table.
   * 
   */

  public function reload($values=null)
  {
    $this->loaded = false;
    $this->load($values);
  }

  static private function get_select_sql($object, $values=null)
  {
    if ($values == null)
      $sql = "SELECT * FROM `" . $object->db_name() . "`.`" . $object->table() . "` WHERE " . $object->where_clause() . " LIMIT {$object->row_limit()}";
    else
    {
      $sql = "SELECT ";
      foreach ($values as $value)
      {
        $sql .= "`" . $value . "`,";
      }
      $sql = self::remove_last_comma($sql);
      $sql .= "FROM `" . $object->db_name() . "`.`" . $object->table() . "` ";


      $sql .= "WHERE " . $object->where_clause() . " LIMIT {$this->row_limit()}";
    }
    //error_log($sql);
    return $sql;
  }

  /*
   * Create a new object from the database and table, with the clause. 
   * The clause should not specify a limit as this is already included.
   * 
   * If no values are specified, all columns will be loaded.
   */

  private static function _create($object, $values=null)
  {

    $sql = self::get_select_sql($object, $values);
    //error_log($sql);
    //if(is_a($object,"Commission"))
    //	echo "\n\n\n$sql\n\n\n<br>";
    if ($object->db()->query($sql))
    {
      $result = $object->db()->get_result();
      //error_log("is valid: " . $result->rows());
      $object->is_valid = ($result->rows() == 1);


      $object->is_loading = true;
      while ($obj = $result->fetch_next())
      {
        if ($object->allowMemCache() && self::isMemcachedAvailable())
        {
          //error_log("storing in memcache " . $object->getMemCacheKey());
          $obj->_memcache_ctime = time();
          self::setMemcached($object->getMemCacheKey(), $obj, self::$MEMCACHE_TTL);
        }
        else if ($object->allowMemCache())
        {
          //	error_log("would store in memcache " . $object->getMemCacheKey() . " but memcache not available");
        }

        $object->load_from_db_row($obj);
      }
      $object->is_loading = false;
    }
    else
    {
      $object->is_valid = false;
    }
  }

  public function load_from_sql_result($row)
  {

    $this->is_loading = true;
    $this->is_valid = true;
    $this->load_from_db_row($row);
    $this->is_loading = false;
    $this->loaded = true;
  }

  private function data($key = null)
  {
    if (isset($key))
    {
      if (isset($this->data->$key))
        return $this->data->$key;
      else
        return;
    }
    return $this->data;
  }

  protected function load_from_data($data)
  {
    //echo "***************<br>";
    if ($data != null && count($data) != 0)
    {
      $this->previous = array();
      $this->data = $data;
    }
  }

  protected
  function load_from_db_row($row)
  {
    $this->load_from_data($row);
  }

  final public function is_valid()
  {
    return $this->isValid();
  }

  public function isValid()
  {
    if (!$this->can_load())
      return false;

    $this->load();
    return $this->is_valid;
  }

  public function isModified()
  {
    return $this->previous != null && count($this->previous) != 0;
  }

  public function modified_fields()
  {
    $changes = array();

    if ($this->previous == null || count($this->previous) == 0)
      return $changes;

    foreach ($this->previous as $key => $previous)
    {
      $changes[] = $key;
      //echo "$key ";
    }

    return $changes;
  }

  /*
   * The variables that have changed since the object was read from the database.
   * 
   */

  protected function changes()
  {
    $changes = array();

    if ($this->previous == null || count($this->previous) == 0)
      return $changes;

    foreach ($this->previous as $key => $previous)
    {

      $current = $this->getDBValue($key);

      $changes[] = array("key" => $key, "new" => $current, "prev" => $previous);
    }

    return $changes;
  }

  protected function delete_as_sql($addslashes = true)
  {
    $data = $this->data;

    if (!count($this->data()))
    {
      return null;
    }

    $sql = "DELETE FROM `" . $this->db_name() . "`.`" . $this->table() . "` WHERE {$this->where_clause()}";
    return $sql;
  }

  function columnsToValues($columns)
  {
    $values = array();
    foreach ($columns as $column)
    {
      $values[$column] = $this->getDBValue($column);
    }

    return $values;
  }

  protected function changes_as_sql_insert($addslashes = true)
  {

    if (!count($this->data()))
    {
      return null;
    }

    $sql = "INSERT INTO `" . $this->db_name() . "`.`" . $this->table() . "` ( ";

    foreach ($this->data() as $key => $value)
    {
      if (!in_array($key, $this->ignore_changes) && $key != "changes")
        $sql .= "`" . $key . "`,";
    }
    //remove the extra comma
    $sql = substr($sql, 0, -1);
    $sql .= " ) Values ( ";

    $update_keys = array();
    $primary_keys = (array)$this->primary_key();
    foreach ($this->data() as $key => $value)
    {
      if (!in_array($key, $this->ignore_changes) && $key != "changes")
      {
        if ($addslashes)
        {
          $sql .= "'" . addslashes($value) . "',";
          if (!in_array($key, $primary_keys))
            $update_keys[$key] = "`$key`='" . addslashes($value) . "'";
        }
        else
        {
          $sql .= $value . ",";
          if (!in_array($key, $primary_keys))
            $update_keys[$key] = "`$key`='$value'";
        }
      }
    }
    //remove the extra comma
    $sql = substr($sql, 0, -1);
    $sql .= ")";

    $increment_keys = $this->getIncrementalKeys();
    if (isset($increment_keys) && count($increment_keys))
    {

      foreach ((array)$this->getIncrementalKeys() as $key)
      {
        $difference = $this->getDBValue($key) - $this->getOriginalDBValue($key);

        $update_keys[$key] = "`$key`=`$key`+$difference";
      }
      if (count($update_keys))
      {
        $sql .= " ON DUPLICATE KEY UPDATE " . implode(",", $update_keys);
      }
    }

    return $sql;
  }

  protected function changes_as_sql_update($addslashes = true)
  {
    $changes = $this->changes();
    if (!count($changes))
      return null;

    $sql = "UPDATE `" . $this->db_name() . "`.`" . $this->table() . "` SET";


    $increment_keys = $this->getIncrementalKeys();

    foreach ($changes as $change)
    {
      if (!in_array($change["key"], $this->ignore_changes) && $change["key"] != "changes")
      {
        $value = $change["new"];
        if (isset($increment_keys) && count($increment_keys) &&
                in_array($change["key"], $increment_keys))
        {
          $difference = $value - $this->getOriginalDBValue($change["key"]);
          $sql .= " `" . $change["key"] . "` = " . $change["key"] . "+" . $difference . ",";
        }
        else if ($addslashes)
          $sql .= " `" . $change["key"] . "` = '" . addslashes($value) . "',";
        else
          $sql .= " `" . $change["key"] . "` = '" . $value . "',";
      }
    }
    //remove the extra comma
    $sql = substr($sql, 0, -1);


    $sql .= " WHERE " . $this->where_clause();

    return $sql;
  }

  //over-ride this if you require an alternative format.
  public function change_as_history($key, $new, $prev)
  {
    $threshold = 50;
    $change = ucwords("$key: ");
    if (strlen($new) > $threshold && strlen($prev) > $threshold)
      $change .= "\n'";
    else
      $change .= "'";
    $change .= stripslashes($new) . "'\n";
    $change .= "Previous " . ucwords($key) . ": ";
    if (strlen($prev) > $threshold && strlen($new) > $threshold)
      $change .= "\n' ";
    else
      $change .= "'";
    $change .= stripslashes($prev) . "'";
    $change .= "\n";
    return $change;
  }

  private $historical_order;

  function set_historical_order($order)
  {
    $this->historical_order = $order;
  }

  //over-ride this if you require an alternative way of recording historical changes.
  function changes_as_history()
  {

    if (!isset($this->historical_order))
    {
      $changes = $this->changes();
      if (!count($changes))
        return null;
      foreach ($changes as $change)
      {
        if (!isset($details))
          $details = "";
        $details .= $this->change_as_history($change['key'], $change['new'], $change['prev']);
      }
      return $details;
    }

    foreach ($this->historical_order as $key)
    {
      $previous = $this->previous[$key];
      if ($previous != null)
      {
        $new = $this->$key;
        if (!isset($details))
          $details = "";
        $details .= $this->change_as_history($key, $new, $previous);
      }
    }


    return $details;
  }

  private static function remove_last_comma($sql)
  {
    //remove the extra comma
    $sql = substr($sql, 0, -1);
    return $sql;
  }

  public function email_substitutions()
  {
    $class_name = strtoupper(get_class($this));
    $array = array();
    $this->load();
    foreach ($this->data() as $variable => $value)
    {
      $name = $class_name . "_" . strtoupper($variable);

      $array[$name] = $value;
    }

    return $array;
  }

  public function toArray()
  {
    $array = array();
    $this->load();
    foreach ($this->data() as $variable => $value)
    {
      $array[$variable] = $value;
    }

    return $array;
  }

  public function toJson()
  {
    return json_encode($this->toArray());
  }

  function addslashes(&$value, $key)
  {
    error_log("HOLY DEPRECATED BAT MAN!!!!");
    $value = addslashes($value);
  }

  static protected function addSlashesToArray($array)
  {
    error_log("HOLY DEPRECATED BAT MAN!!!!");
    if (!array_walk($array, array("DBObject", "addslashes")))
    {
      throw new Exception("Unable to escape slashes!");
    }
  }

  function getTransactionsForDelete()
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getTransactionsForDelete();
    }
    return null;
  }

  final function getMessagesForDelete()
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getMessagesForDelete();
    }
    return null;
  }

  final function getEmailsForDelete()
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getEmailsForDelete();
    }
    return null;
  }

  function delete()
  {
    if (!$this->can_load())
    {//isValid())
      error_log("object invalid. cannot be delete.");
      error_log(print_r(debug_backtrace(), 1));
      exit;
    }
    else
    {
      $sql = $this->delete_as_sql();

      if ($this->db()->execute($sql))
      {

        $this->apply_transactions($this->getTransactionsForDelete());
        $this->apply_messages($this->getMessagesForDelete());

        self::removeFromMemCache();

        return true;
      }
    }

    return false;
  }

  function getTransactionsForChanges($insert = false)
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getTransactionsForChanges($insert);
    }

    return null;
  }

  final function getMessagesForChanges($insert = false)
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getMessagesForChanges($insert);
    }
    return null;
  }

  final function getEmailsForChanges($insert = false)
  {
    if ($this->hasModelController())
    {
      return $this->getModelController()->getEmailsForChanges($insert);
    }
    return null;
  }

  private final function apply_transactions($transactions)
  {
    if (is_a($transactions, "Transaction"))
    {
      //error_log("record...");
      $transactions->record();
    }
    else if (is_array($transactions))
    {
      foreach ((array)$transactions as $transaction)
      {
        if (is_a($transaction, "Transaction"))
          $transaction->record();
        else
          error_log("$transaction given, expecting Transaction class.");
      }
    }
  }

  private final function apply_messages($messages)
  {
    if (is_a($messages, "Message"))
    {
      //error_log("record...");
      $messages->send();
    }
    else if (is_a($messages, "SendMessageTemplateController"))
    {
      $messages->execute();
    }
    else if (is_array($messages))
    {
      foreach ((array)$messages as $message)
      {
        if (is_a($message, "Message"))
          $message->send();
        else
          error_log("$message given, expecting Message class.");
      }
    }
  }

  private final function send_emails($emails)
  {
    if (is_a($emails, "Email"))
    {
      //error_log("record...");
      $emails->send();
    }
    else if (is_array($emails))
    {
      foreach ((array)$emails as $email)
      {
        if (is_a($email, "Email"))
          $email->send();
        else
          error_log("$email given, expecting Email class.");
      }
    }
  }

  /**
   * 
   * Save does not call on the object to load, for all current cases this is fine.
   * 
   * Assuming the class is created correctly, setting a database value should have resulted in a load,
   * but if you haven't set any database values, the row doesn't need to be updated. 
   * Again, assuming the class is constructed properly.
   * 
   * 
   */
  function save()
  {

    $insert = false;
    if (!$this->isValid())
    {
      $insert = true;
      $transactions = $this->getTransactionsForChanges(true);
      $messages = $this->getMessagesForChanges(true);
      $emails = $this->getEmailsForChanges(true);
      $sql = $this->changes_as_sql_insert();
    }
    else
    {
      $transactions = $this->getTransactionsForChanges(false);
      $messages = $this->getMessagesForChanges(false);
      $emails = $this->getEmailsForChanges(false);

      $sql = $this->changes_as_sql_update();
    }

    if (strlen($sql) && $this->db()->execute($sql))
    {//&& $this->db()->rows_affected() == 1)
      if ($insert)
      {
        $key = $this->primary_key();
        if (isset($key))
        {
          if (is_array($key) && count($key) == 1)
            $key = $key[0];

          if (!is_array($key))
          {
            $insert_id = $this->db()->getInsertID();
            if ($insert_id > 0)
            {
              $this->setDBValue($key, $insert_id);
            }
          }
        }
        $this->is_valid = true;
      }

      $this->apply_transactions($transactions);
      $this->apply_messages($messages);
      $this->send_emails($emails);

      $this->updateInMemCache();
      //$this->removeFromMemCache();
      $this->changes = array();
      $this->previous = array();
      return true;
    }
    else if (!strlen($sql))
    {
      //DBObject did not detect any changes...nothing to save.
      //error_log("nothing to save.");
      return true;
    }
    else
    {
      error_log($this->db()->getLastError());
    }

    return false;
  }

  function __toString()
  {
    return "DBObject subclass";
  }

  function getColumnForModel(Model $model)
  {
    
  }

  function getValueForColumnWithModel(Model $model)
  {
    
  }

  public static function collection($class, $consistency = null)
  {
    Loader::load("model", "DBObjectCollection");
    $reflection = new ReflectionClass($class);
    $object = $reflection->newInstance();

    $db = $object->db();
    if (isset($consistency) && is_string($consistency))
    {
      switch ($consistency)
      {
        case self::CONSISTENCY_ABSOLUTE:
          if (!$reflection->hasMethod("master_db"))
          {
            throw new Exception("No master database connection defined. Unable to provide consistency level required.");
          }
          $db = $object->master_db();
          break;
        case self::CONSISTENCY_REPORTING:
          if (!$reflection->hasMethod("reporting_db"))
          {
            throw new Exception("No reporting database connection defined. Unable to provide consistency level required.");
          }
          $db = $object->reporting_db();
          break;
      }
    }


    $collection = new DBObjectCollection($class, $db, $object->table(), $object->db_name());

    return $collection;
  }

  public static function mutableCollection($class)
  {
    Loader::load("model", "DBObjectMutableCollection");
    $reflection = new ReflectionClass($class);
    $object = $reflection->newInstance();
    $collection = new DBObjectMutableCollection($class, $object->db(), $object->table(), $object->db_name());

    return $collection;
  }

  public static function getStatistics($class)
  {

    Loader::load("model", "DBStatistics");
    $reflection = new ReflectionClass($class);
    $object = $reflection->newInstance();
    $statistics = new DBStatistics($class, $object->db(), $object->table(), $object->db_name());
    $statistics->setSumColumns($object->getIncrementalKeys());
    return $statistics;
  }

  public static function mutator($class, $type, $args=null)
  {
    $reflection = new ReflectionClass($class);

    ///determine path -- messy....		
    $path = $reflection->getFileName();
    $start_pos = "class-repository/model/";
    $start = strpos($path, "class-repository/model/");
    $model_include = substr($path, $start + strlen($start_pos));

    $end_pos = "{$class}.class.inc.php";
    $end = strpos($model_include, $end_pos);

    $path = substr($model_include, 0, $end);
    //$path .= "{$type}$class";
    //end of determine path

    Debugger::log("MUTATOR: <span style=\"color: #DAA2FF;\">$path{$type}{$class}</span>");

    Loader::load("mutator", "$path{$type}{$class}.class.inc.php");

    $creatorReflection = new ReflectionClass("{$type}{$class}");
    if (isset($args))
      $creator = $creatorReflection->newInstanceArgs($args);
    else
      $creator = $creatorReflection->newInstance();
    unset($path);
    unset($creatorReflection);
    unset($reflection);
    return $creator;
  }

  private static function get_class_path($class)
  {
    $reflection = new ReflectionClass($class);
    $path = $reflection->getFileName();
    $start_pos = "class-repository/model/";
    $start = strpos($path, "class-repository/model/");
    $model_include = substr($path, $start + strlen($start_pos));

    $end_pos = "{$class}.class.inc.php";
    $end = strpos($model_include, $end_pos);
    $path = substr($model_include, 0, $end);

    return $path;
  }

  public static function validator($class, $validator, $args)
  {
    if (!is_array($validator))
      $validators = array($validator);
    else
      $validators = $validator;

    foreach ($validators as $validator)
    {
      Loader::load("utility", "validator/" . self::get_class_path($class) . "{$validator}Validator.class.inc.php");

      $fields = (!is_array($args[0])) ? array($args[0]) : $args[0];
      $object = $args[1];
      $type = "issue";

      $reflection = new ReflectionClass("{$validator}Validator");
      $instance = $reflection->newInstanceArgs(array($class, $fields, $object, $type));
      $instance->activate();

      unset($instance, $reflection);
    }
  }

  public function collectionFilter(DBObjectCollection $collection)
  {
    $ids = $collection->getPrimaryKeys();
    return array("db" => $this->db_name(), "table" => $this->table(), "column" => $this->primary_key(), "value" => $ids, "comparison" => "IN");
  }

  protected function base_filter($column = null, $value=null, $comparison=null)
  {
    $filter = array("db" => $this->db_name(), "table" => $this->table());
    if (isset($column))
      $filter["column"] = $this->primary_key();
    if (isset($value))
    {
      $filter["value"] = $value;
      if (is_array($value))
      {
        if (count($value) && is_object($value[0]))
        {
          foreach ($value as $object)
            $filter["value"][] = $object->getID();
        }
        $filter["comparison"] = "IN";
      }
    }
    if (isset($comparison))
      $filter["comparison"] = "IN";

    return $filter;
  }

}

?>