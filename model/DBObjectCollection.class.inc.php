<?php

Loader::load("model", "DBObjectManager");

class DBObjectCollection extends DBObjectManager {

    protected $managed_object;
    protected $db;
    private $table;
    private $database_name;

    function __construct($dbobject_class_name, Database $db, $table_name, $database_name=null) {
        $reflection = new ReflectionClass($dbobject_class_name);
        if ($reflection->isSubclassOf("DBObject")) {
            $this->managed_object = $dbobject_class_name;
            $this->db = $db;
            $this->table = $table_name;
            if (isset($database_name))
                $this->database_name = $database_name;
        }
        else
            Debugger::log("cannot create manager from $dbobject_class_name");
    }

    protected function db_name() {
        if (isset($this->database_name))
            return $this->database_name;
        else
            return parent::db_name();
    }

    public function groupByMappedFunction($function) {
        $grouped = array();
        foreach ($this->getManagedObjects() as $object) {
            $grouping = $object;
            foreach ((array) $function as $func) {
                $grouping = $grouping->$func();
            }
            $grouped[$grouping][] = $object;
        }

        return $grouped;
    }

    public function __call($name, $arguments) {
        if ($this->is_applying_sort($name) || $this->is_applying_filter($name))
            return parent::__call($name, $arguments);

        switch ($name) {
            case "get{$this->managed_object}s":
            case "get{$this->managed_object}es":
            case "get" . substr($this->managed_object, 0, -1) . "ies":
                return $this->getManagedObjects();
            case "getFirst{$this->managed_object}":
                if (reset($this->getManagedObjects()) === false)
                    return;
                else
                    return reset($this->getManagedObjects());
            case "getTotal":
                return $this->getManagedObjectCount(true);
            case "get{$this->managed_object}Count":
            case "getSize":
                return $this->getManagedObjectCount();
                break;
        }

        trigger_error("Call to undefined method DBObjectCollection->{$name}() on {$this->managed_object}");
        //Debugger::log("DBObjectCollection __call($name)");
    }

    protected function managed_object() {
        return $this->managed_object;
    }

    private $reflection_object;

    final protected function reflection_object() {
        if (!isset($this->reflection_object)) {
            $this->reflection_object = new ReflectionClass($this->managed_object());
        }

        return $this->reflection_object;
    }

    /**
     * The database connection to be used for the managed objects
     * 
     * @return Database
     */
    final protected function db() {
        return $this->db;
    }

    private $primary_key_method;

    private function get_primary_key() {
        //error_log("in get primary key");
        if (!isset($this->primary_key_method)) {
            $this->primary_key_method = new ReflectionMethod($this->managed_object(), 'primary_key');
            //error_log("create primary key method");
        }
        //else
        //error_log("reuse primary key method");
        if ($this->primary_key_method->isStatic()) {
            //error_log("invoke " . $this->primary_key_method);
            $key = $this->primary_key_method->invoke(null);
            //error_log("key: $key");
            return $key;
        }
        //else
        //error_log("primary key method is not static");
    }

    final protected function create_managed_object($obj) {

        $managed_object_reflection = new ReflectionClass($this->managed_object());

        if ($managed_object_reflection->hasMethod('__construct')) {
            $primary_key_method = $managed_object_reflection->getMethod("primary_key");
            $primary_key = $primary_key_method->invoke(null);

            if (isset($primary_key)) {
                $values = array();
                if (is_array($primary_key)) {
                    foreach ($primary_key as $key) {
                        $values[] = $obj->$key;
                    }
                }
                else
                    $values[] = $obj->$primary_key;

                if ($managed_object_reflection->hasMethod('primary_key_to_object')) {
                    $primary_key_to_object_method = new ReflectionMethod($this->managed_object(), 'primary_key_to_object');
                    if ($primary_key_to_object_method->isStatic()) {
                        $index = 0;
                        $args = array();
                        foreach ($values as $value) {
                            $args[] = $primary_key_to_object_method->invoke(null, $value, $index);
                            $index++;
                        }

                        $instance = $managed_object_reflection->newInstanceArgs($args);
                        $instance->load_from_sql_result($obj);
                    }
                    else
                        error_log("primary_key_to_object must be static");
                }
                else {

                    $instance = $managed_object_reflection->newInstanceArgs($values);
                    $instance->load_from_sql_result($obj);
                    //error_log("instance: $instance");
                    return $instance;
                }
            } else {
                //error_log($managed_object_reflection);
                throw new Exception("Managed object({$this->managed_object()}) must define 'public static function primary_key()' $primary_key");
            }
        } else {
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

    /**
     * Name of table where the managed objects are stored.
     * 
     * @return string
     */
    final protected function table() {
        return $this->table;
    }

    public function collectionFilter(DBObjectCollection $collection) {
        return array("column" => $this->get_primary_key(),
            "value" => $collection->get_select_sql($collection->get_primary_key()),
            "comparison" => "IN",
            "wrap" => "off",
            "slashes" => false);
    }

    public function toJSON() {
        $object_json = array();
        foreach ($this->getManagedObjects() as $object) {
            $object_json[] = $object->toJSON();
        }
        return '[' . implode(',', $object_json) . ']';
    }

}

?>