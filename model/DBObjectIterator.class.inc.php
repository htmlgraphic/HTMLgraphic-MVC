<?php

Loader::load("model", "DBObjectManager");

class DBObjectIterator extends DBObjectManager
{

  private $managed_object;
  private $db;
  private $table;

  function __construct($dbobject_class_name, Database $db, $table_name)
  {
    $reflection = new ReflectionClass($dbobject_class_name);
    if ($reflection->isSubclassOf("DBObject"))
    {
      $this->managed_object = $dbobject_class_name;
      $this->db = $db;
      $this->table = $table_name;
    }
    else
    {
      error_log("cannot create manager from $dbobject_class_name");
    }
  }

  private static $iterator_type;
  private static $array_iterator_location;
  private static $iterator;

  public function nextObject()
  {
    //error_log("get next object...");
    if (!isset($this->iterator))
    {
      if (isset($this->managed_objects))
      {

        $this->array_iterator_location = 0;
        $this->managed_object;
      }
      else
      {

        if (!$this->loaded && $this->canUseMemcache() && EAMemCache::isAvailable())
        {

          $primary_key = $this->get_primary_key();
          $sql = $this->select_for_memcache();
          if (isset($primary_key) && isset($sql))
          {
            if ($results = $this->db()->query($sql))
            {
              $this->iterator_type = "memcache";
              $this->iterator = $results;
            }
          }
        }
      }

      if (!isset($this->iterator))
      {
        $sql = $this->select();
        //	error_log($sql);
        if (isset($sql))
        {
          if ($result = $this->db()->query($sql))
          {

            $this->iterator_type = "normal";
            $this->iterator = $result;
          }
        }
      }
      //error_log("type: {$this->iterator_type}");
    }

    if (is_array($this->iterator))
    {

      if (!isset($this->iterator[$this->array_iterator_location]))
      {
        return $this->iterator[$this->array_iterator_location];
      }
    }
    else
    {

      $obj = $this->iterator->fetch_object();
      if (isset($obj))
      {
        if ($this->iterator_type == "memcache")
        {
          $primary_key = $this->get_primary_key();
          if (isset($primary_key))
          {
            //error_log("create with primary value {$obj->$primary_key}");
            return $this->create_managed_object_with_primary_value($obj->$primary_key);
          }
        }
        else
        {
          //error_log("create managed object...");	
          return $this->create_managed_object($obj);
        }
      }
    }
  }

  final public function applyBooleanFunction($function, $parameters = array(), $stop_condition)
  {

    while ($managed_object = $this->nextObject())
    {
      if ($stop_condition == call_user_func_array($function, array_merge(array($managed_object), (array)$parameters)))
      {
        unset($managed_object);
        return true;
      }
      else
        unset($managed_object);
    }
    return false;
  }

  final public function applyMutator($name, $parameters = array(), $toString = "__toString")
  {
    $results = array();
    while ($managed_object = $this->nextObject())
    {
      $value = DBObject::mutator($this->managed_object(), $name, array_merge(array($managed_object), (array)$parameters))->activate();

      $key = call_user_func(array($managed_object, $toString));

      $results[$key] = $value;

      unset($managed_object);
    }

    return $results;
  }

  final public function applyFunction($function, $parameters = array(), $toString = "__toString")
  {
    $results = array();
    while ($managed_object = $this->nextObject())
    {
      $key = call_user_func(array($managed_object, $toString));
      $value = call_user_func_array($function, array_merge(array($managed_object), (array)$parameters));

      if (isset($results[$key]))
      {
        //error_log("over writing existing value! $key");
      }
      $results[$key] = $value;


      unset($managed_object);
    }


    return $results;
  }

  final protected function create_managed_object($obj)
  {
    $managed_object_reflection = new ReflectionClass($this->managed_object());

    if ($managed_object_reflection->hasMethod('__construct'))
    {
      $primary_key_method = $managed_object_reflection->getMethod("primary_key");
      $primary_key = $primary_key_method->invoke(null);

      if (isset($primary_key))
      {
        $values = array();
        if (is_array($primary_key))
        {
          foreach ($primary_key as $key)
            $values[] = $obj->$key;
        }
        else
          $values[] = $obj->$primary_key;

        if ($managed_object_reflection->hasMethod('primary_key_to_object'))
        {
          $primary_key_to_object_method = new ReflectionMethod($this->managed_object(), 'primary_key_to_object');
          if ($primary_key_to_object_method->isStatic())
          {
            $index = 0;
            $args = array();
            foreach ($values as $value)
            {
              $args[] = $primary_key_to_object_method->invoke(null, $value, $index);
              $index++;
            }

            $instance = $managed_object_reflection->newInstanceArgs($args);
            $instance->load_from_sql_result($obj);
          }
          //else
          //error_log("primary_key_to_object must be static");
        }
        else
        {

          $instance = $managed_object_reflection->newInstanceArgs($values);
          $instance->load_from_sql_result($obj);
          //error_log("instance: $instance");
          return $instance;
        }
      }
      else
      {
        //error_log($managed_object_reflection);
        throw new Exception("Managed object({$this->managed_object()}) must define 'public static function primary_key()' $primary_key");
      }
    }
    else
    {
      //error_log("create with no args");
      $instance = $managed_object_reflection->newInstance();
      $instance->load_from_sql_result($obj);
    }
    if (isset($instance))
      $instance->load_from_sql_result($obj);
    return $instance;
    //else
    //	error_log("no instance created");
  }

  private $primary_key_method;

  private function get_primary_key()
  {
    if (!isset($this->primary_key_method))
    {
      $this->primary_key_method = new ReflectionMethod($this->managed_object(), 'primary_key');
    }
    if ($this->primary_key_method->isStatic())
    {
      return $this->primary_key_method->invoke(null);
    }
    //else
    //error_log("primary key method is not static");
  }

  protected function managed_object()
  {
    return $this->managed_object;
  }

  private $reflection_object;

  final protected function reflection_object()
  {
    if (!isset($this->reflection_object))
    {
      $this->reflection_object = new ReflectionClass($this->managed_object());
    }

    return $this->reflection_object;
  }

  /**
   * The database connection to be used for the managed objects
   * 
   * @return Database
   */
  final protected function db()
  {
    return $this->db;
  }

  /**
   * Name of table where the managed objects are stored.
   * 
   * @return string
   */
  final protected function table()
  {
    return $this->table;
  }

}

?>