<?php

Loader::load("model", "DBObjectManager");

class DBStatistics extends DBObjectManager
{

  private $managed_object;
  private $db;
  private $table;
  private $database_name;

  function __construct($dbobject_class_name, Database $db, $table_name, $database_name=null)
  {
    $reflection = new ReflectionClass($dbobject_class_name);
    if ($reflection->isSubclassOf("DBObject"))
    {
      $this->managed_object = $dbobject_class_name;
      $this->db = $db;
      $this->table = $table_name;
      if (isset($database_name))
        $this->database_name = $database_name;
    }
  }

  protected function db_name()
  {
    if (isset($this->database_name))
      return $this->database_name;
    else
      return parent::db_name();
  }

  private $function_columns;

  public function addFunctionForColumns($function, $columns)
  {
    $function = strtoupper($function);
    foreach ((array)$columns as $key => $column)
    {
      $this->function_columns[$function][$key] = $column;
      //	$this->selected_array = array("column" => $column,"wrapper" => $function);
    }
    return $this;
  }

  public function removeFunction($function)
  {
    $this->function_columns[$function] = array();
  }

  private $sum_columns;

  public function setSumColumns($array)
  {
    $this->addFunctionForColumns("SUM", (array)$array);
    return $this;
    $this->sum_columns = $array;
    foreach ((array)$array as $columns)
    {
      foreach ($columns as $column)
      {
        $this->select_array[] = array("column" => $column, "wrapper" => "SUM");
      }
    }
    //error_log("select array: " . print_r($this->select_array,true));
    return $this;
  }

  public function clearSumColumns()
  {
    $array = array();
    foreach ($this->selected_array as $selected)
    {
      if ($selected["wrapper"] != "SUM")
        $array[] = $selected;
    }
    $this->selected_array = $array;
  }

  private $count_columns;

  public function setCountColumns($array)
  {
    $this->addFunctionForColumns("COUNT", (array)$array);
    return;
    $this->count_columns = $array;
    foreach ($array as $column)
    {
      $this->select_array[] = array("column" => $column, "wrapper" => "COUNT");
    }
    //error_log("select array: " . print_r($this->select_array,true));
  }

  private $selecting_columns = array();

  private function build_select_from_functions()
  {
    $select_clause = "";
    foreach ((array)$this->function_columns as $function => $columns)
    {
      foreach ($columns as $key => $column)
      {
        if (strlen($select_clause))
          $select_clause .= ",";
        if (is_array($column))
        {
          foreach ($column as $column_function => $column_value)
          {
            $column_name = $column_function . "_" . $column_value . "_" . strtolower($function);
            $dbname = (!is_numeric($key)) ? "`" . $key . "`." : "";
            $select_clause .= " $function(" . strtoupper($column_function) . "($dbname`$column_value`)) as `$column_name`";
          }
        }
        else
        {
          $column_name = $column . "_" . strtolower($function);
          $dbname = (!is_numeric($key)) ? "`" . $key . "`." : "";
          $select_clause .= " $function($dbname`$column`) as `$column_name`";
        }
        $this->selecting_columns[] = $column_name;
      }
    }
    return $select_clause;
  }

  private function build_select_from_array($array)
  {
    //error_log("<pre>Where: " . print_r($array,1) . "</pre>");

    $select_clause = "";
    foreach ((array)$array as $info)
    {
      if (isset($info["column"]))
      {
        if (is_array($info["column"]))
          $column = "`" . implode("`,`", $info["column"]) . "`";
        else
          $column = "`" . $info["column"] . "`";
        /*
          if(isset($info["table"]))
          {
          $column = "";
          if(isset($info["db"]))
          $column = "`" . $info["db"] . "`.";
          $column .= "`" . $info["table"] . "`.`" . $info["column"] . "`";
          }
         */
        if (isset($info["wrapper"]))
          $wrap = $info["wrapper"];
        else
          $wrap = "";
        if (strlen($select_clause))
          $select_clause .= ",";
        $column_name = $info["column"];
        if (is_array($info["column"]))
          $column_name = $this->table();
        if ($wrap == "COUNT" && $info["column"] != "*")
          $select_clause .= "$column,";
        if (is_array($info["column"]))
          $column = "*";
        if (strlen($wrap))
          $select_clause .= " $wrap($column) as " . $column_name . "_" . strtolower($wrap);
        else
          $select_clause .= " $column_name ";
      }
      //else
      //error_log("column not set..." . print_r($info,1));
    }
    //Debugger::log("where clause: ".$where_clause);
    return $select_clause;
  }

  final protected function get_select_stats_sql()
  {

    $select = "";

    if (strlen($select))
      $select .= ",";
    $select .= $this->build_select_from_functions();

    if (isset($this->grouping))
    {
      foreach ($this->grouping as $grouping)
      {
        if (!in_array($grouping, $this->selecting_columns))
        {
          if (strlen($select))
            $select .= ",";
          $select .= " `$grouping`";
        }
      }
    }

    return $select;
  }

  //"SELECT SUM(publisher) FROM ARTICLES WHERE author = 'author'";

  function setGroupFunctions($functions)
  {
    foreach ($functions as $function => $args)
    {
      $this->group_by = strtoupper($function) . "(" . implode(",", $args) . ")";
      $this->select_array[] = array("columns" => strtoupper($function) . "(" . implode(",", $args) . ")");
    }

    return $this;
  }

  private $group_by;
  private $grouping;

  function setGroupColumns($columns)
  {
    $this->group_by = "`" . implode("`,`", $columns) . "`";
    $this->grouping = (array)$columns;
    foreach ($columns as $column)
    {
      $this->select_array[] = array("column" => $column);
    }
    return $this;
  }

  protected function group_by_clause()
  {
    return $this->group_by;
  }

  final private function get_object_stats_sql($values = null)
  {

    $sql = "SELECT {$this->get_select_stats_sql()}";
    $sql .= " FROM `{$this->db_name()}`.`" . $this->table() . "` ";

    if (strlen($this->join_clause()))
      $sql .= $this->join_clause();
    if ($this->index_hints())
      $sql .= $this->index_hints();

    if (strlen($this->where_clause()))
      $sql .= " WHERE " . $this->where_clause();
    if (isset($this->group_by))
      $sql .= " GROUP BY {$this->group_by}";
    if (strlen($this->order_by_clause()))
      $sql .= " ORDER BY " . $this->order_by_clause();
    if (strlen($this->get_limit()))
      $sql .= " LIMIT " . $this->get_limit();
    return $sql;
  }

  private $managed_object_sums;
  private $managed_object_counts;
  private $managed_object_stats;

  private function loadStatsFromDatabase()
  {
    $sql = $this->get_object_stats_sql();
    $this->managed_object_sums = array();
    $this->managed_object_counts = array();

    //	error_log("loadSumsFromDatabase: $sql");
    //	exit;

    foreach ((array)$this->sum_columns as $column)
    {
      $this->managed_object_sums[$column] = 0;
    }

    foreach ((array)$this->count_columns as $column)
    {
      if (is_array($column))
        $column = implode(":", $column);
      $this->managed_object_count[$column] = 0;
    }
    //if(isset($this->grouping))
    //{
    //	$this->managed_object_data = array();
    //	$key = implode(":",$this->grouping);
    //}
    if ($result = $this->db()->query($sql))
    {
      while ($row = $result->fetch_object())
      {

        foreach ((array)$this->function_columns as $function => $columns)
        {
          //echo "$function <br>";
          $func = strtolower($function);
          foreach ($columns as $column)
          {

            if (is_array($column))
            {
              $column_path = ""; // = $function;
              foreach ($column as $column_key => $column_value)
                $column_path .= $column_key . "_" . $column_value;
              //$column_path .= "{$function}_" . $column;
              //	print_r($column);
              $column = $column_path;

              //	$column = implode("_",$column);
            }
            if (isset($this->grouping))
            {
              $group_array = array();
              foreach ($this->grouping as $group_key)
                $group_array[] = $row->$group_key;
              $key = implode(":", $group_array);
              $this->managed_object_data[$func][$column][$key] = $row->{$column . "_" . $func};
            }
            else
            {
              $this->managed_object_data[$func][$column][] = $row->{$column . "_" . $func};
            }
            if ($function == "SUM")
              $this->managed_object_sums[$column] = $row->{$column . "_sum"};
            else if ($function == "COUNT")
              $this->managed_object_counts[$column] = $row->{$column . "_count"};
          }
        }
      }
    }
    // account for no results, probably a better way of doing this
    if (!isset($this->managed_object_data))
    {
      $this->managed_object_data = array();
      foreach ((array)$this->function_columns as $function => $columns)
      {
        $func = strtolower($function);
        $this->managed_object_data[$func] = array();
        foreach ($columns as $column)
        {
          if (is_array($column))
          {
            $column_path = "";
            foreach ($column as $column_key => $column_value)
              $column_path .= $column_key . "_" . $column_value;
            $column = $column_path;
          }
          $this->managed_object_data[$func][$column] = array();
        }
      }
    }

    //error_log(print_r($this->managed_object_data,true));
  }

  private $loaded_sums = false;

  final protected function loadStats()
  {
    if (!$this->loaded_sums)
    {
      $this->loaded_sums = true;
      $this->loadStatsFromDatabase();
    }
  }

  final private function getManagedObjectData()
  {
    $this->loadStats();
    return $this->managed_object_data;
  }

  final public function getManagedObjectSums()
  {
    //todo: implement this?
    //if(isset($this->managed_objects))
    //	return count($this->managed_objects);
    //else
    {
      $this->loadStats();
      return $this->managed_object_sums;
    }
  }

  final public function getManagedObjectCounts()
  {
    $this->loadStats();
    return $this->managed_object_counts;
  }

  protected function managed_object()
  {
    return $this->managed_object;
  }

  final protected function create_managed_object($obj)
  {
    
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

  public function __call($name, $arguments)
  {
    if ($this->is_applying_sort($name) || $this->is_applying_filter($name))
      return parent::__call($name, $arguments);

    $this->getManagedObjectData();
    $lower_name = strtolower(str_replace('_', '', $name));
    //Debugger::log($this->managed_object_data);
    foreach ($this->managed_object_data as $function => $function_data)
    {
      foreach ($function_data as $column => $column_data)
      {
        if ($lower_name == strtolower(str_replace('_', '', "get{$function}For{$column}")))
        {

          return $column_data;
        }
        else
        {
          //error_log($lower_name . " != " . strtolower(str_replace('_', '', "get{$function}For{$column}")));
          Debugger::log($lower_name . " != " . strtolower(str_replace('_', '', "get{$function}For{$column}")));
        }
      }
    }
    //exit;
    switch ($name)
    {
      case "get{$this->managed_object}s":
        return $this->getManagedObjects();
      case "getTotal":
        return $this->getManagedObjectCount(true);
      case "get{$this->managed_object}Count":
      case "getSize":
        return $this->getManagedObjectCount();
        break;
    }
    trigger_error("Call to undefined method DBStatistics->{$name}()");
    //error_log(print_r($this->managed_object_data,true));
    //Debugger::log("DBObjectCollection __call($name)");
  }

}

?>