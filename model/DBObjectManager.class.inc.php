<?php
abstract class DBObjectManager
{

	private $added_objects = array();
	private $removed_objects = array();
	private $managed_objects;
	protected $loaded = false;
	protected $loaded_count = false;
	protected $loaded_no_limit_count = false;

	/*
	 * Experimental.
	 * 
	 * 
	 */
	protected function getWhereClauseForObjectRelationships($relations)
	{
		$reflection = new ReflectionClass($this->managed_object());
		$reflection->hasMethod("getModelRelationships");
		$method = new ReflectionMethod($this->managed_object(), "getModelRelationships");
		$where_clause = array();
		if ($method->isStatic())
		{
			$relationships = $method->invoke(null);

			foreach ($relations as $key => $relation)
			{
				if (!is_numeric($key))
				{
					$where_clause[] = array("column" => $key, "value" => $relation);
				}
				else
				{
					if (is_array($relation) && count($relation))
					{
						$multiple_relation = $relation;
						$relation = $multiple_relation[0];
					}

					foreach ($relationships as $relationship)
					{
						if ($relationship["class"] == get_class($relation))
						{
							//error_log("match: $relation == " . $relationship["class"]);
							$relationMethod = new ReflectionMethod($relationship["class"], $relationship["value"]);
							$comparison = "=";
							$column = $relationship["column"];
							if (isset($multiple_relation))
							{
								$comparison = "IN";
								foreach ($multiple_relation as $multiple)
								{
									$value[] = $relationMethod->invoke($relation);
								}
							}
							else
							{
								$value = $relationMethod->invoke($relation);
							}
							$where_clause[] = array("column" => $column, "value" => $value, "comparison" => $comparison);
						}
					}
				}
			}
			return $where_clause;
		}
		else
		{
			//error_log("must be static");
		}
	}

	private $random_managed_objects = array();

	final protected function getRandomManagedObjects($size, $values = null)
	{
		if (!isset($this->random_managed_objects[$size]))
		{
			if (!isset($this->managed_objects))
			{
				$this->loadRandom($size, $values);
				return $this->managed_objects;
			}
			else
			{
				$this->random_managed_objects[$size] = array_rand($this->managed_objects, $size);
			}
		}
		//print_r($this->random_managed_objects);
		return $this->random_managed_objects[$size];
	}

	/**
	 * The managed objects.
	 * 
	 * @return array of managed objects.
	 */
	final protected function getManagedObjects($values = null)
	{
		if (!isset($this->managed_objects))
			$this->load($values);

		return $this->managed_objects;
	}

	final protected function getManagedObjectCount($no_limit = false)
	{
		//error_log("get managed object count");
		if (isset($this->managed_objects) && !$no_limit)
			return count($this->managed_objects);
		else if ($no_limit && isset($this->managed_object_no_limit_count))
			return $this->managed_object_no_limit_count;
		else
		{

			if ($no_limit && isset($this->limit))
			{
				$limit = $this->get_limit();
				$this->set_limit(null);
				$this->loadCount($no_limit);
				$count = $this->managed_object_no_limit_count;
				$this->set_limit($limit);
			}
			else
			{
				$this->loadCount();
				$count = $this->managed_object_count;
			}
			return $count;
		}
	}

	final protected function getUpdatedManagedObjects()
	{
		$update = array();
		foreach ($this->managed_objects as $object)
		{
			if (!in_array($object, $this->added_objects) && $object->isModified())
			{
				$update[] = $object;
			}
		}
		return $update;
	}

	final protected function getAddedManagedObjects()
	{
		return $this->added_objects;
	}

	/**
	 * Called by subclass when the manager adds an object.
	 *
	 *  
	 */
	final protected function addManagedObject(DBObject $object)
	{
		if (is_a($object, $this->managed_object()))
		{
			if (isset($this->removed_objects) && in_array($object, $this->removed_objects))
			{
				$key = array_search($object, $this->removed_objects);
				unset($this->removed_objects[$key]);
				$this->removed_objects = array_values($this->removed_objects);
			}

			if (!in_array($object, $this->managed_objects))
			{
				$this->added_objects[] = $object;
				$this->managed_objects[] = $object;
			}
			//else
			//error_log("$object is already managed!");
		}
		//else
		//error_log("$object is not of expected type: {$this->managed_object()}");
	}

	final protected function getRemovedManagedObjects()
	{
		return $this->removed_objects;
	}

	/**
	 * Called by the subclass when the manager removes an object.
	 * 
	 * 
	 */
	final protected function removeManagedObject(DBObject $object)
	{

		if (is_a($object, $this->managed_object()))
		{
			if (isset($this->added_objects) && in_array($object, $this->added_objects))
			{
				//error_log("it's in added...");
				$key = array_search($object, $this->added_objects);
				unset($this->added_objects[$key]);
				$this->added_objects = array_values($this->added_objects);
			}

			if (in_array($object, $this->managed_objects))
			{
				$this->removed_objects[] = $object;
				$key = array_search($object, $this->managed_objects);
				unset($this->managed_objects[$key]);
				$this->managed_objects = array_values($this->managed_objects);
			}
			//else
			//error_log("$object is not managed!");
		}
		//else
		//error_log("$object is not of expected type: {$this->managed_object()}");
	}

	/**
	 * The type of object to be managed
	 * 
	 * @return string
	 * 
	 */
	abstract protected function managed_object();
	/**
	 * The database connection to be used for the managed objects
	 * 
	 * @return Database
	 */
	abstract protected function db();
	/**
	 * Name of table where the managed objects are stored.
	 * 
	 * @return string
	 */
	abstract protected function table();
	protected function db_name()
	{
		return $this->db()->getName();
	}

	private $lookup_is_memcachable = false;

	public function useMemcache($bool = true)
	{
		$this->lookup_is_memcachable = $bool;
	}

	protected function canUseMemcache()
	{
		return $this->lookup_is_memcachable;
	}

	public function __call($name, $arguments)
	{
		if ($this->is_applying_sort($name))
		{
			$order = strtolower(substr($name, 5, 9)) == "ascending" ? "ASC" : "DESC";
			//Debugger::log($order);
			$function = array($this->managed_object(), substr($name, $order == "ASC" ? 14 : 15));
			//Debugger::log($function);
			if (method_exists($function[0], $function[1]))
			{
				//Debugger::log($function[0] . "::" . $function[1] ." does exist.");
				$this->applySort(call_user_func($function), $order);
				return $this;
			}
			else
			{
				Debugger::log($function[0] . "::" . $function[1] . " does not exist.");
				error_log($function[0] . "::" . $function[1] . " does not exist.");
			}
		}
		else if ($this->is_applying_filter($name))
		{
			$function = array($this->managed_object(), substr($name, 5));

			if (method_exists($function[0], $function[1]))
			{
				$this->applyFilter(call_user_func_array($function, $arguments));
				return $this;
			}
			else
			{
				error_log($function[0] . "::" . $function[1] . " does not exist.");
			}
		}
		else
			error_log("not filter: $name");


		trigger_error("Call to undefined method DBObjectManager->{$name}() on {$this->managed_object()}");
		//Debugger::log("DBObjectCollection __call($name)");
	}

	private $filterset;

	public function applyFilter($filter)
	{
		//TODO:
		//if the data is already loaded, 
		//filter the data.

		$current = $this->get_where_clause_array();
		if (isset($filter["column"]))
			$current[] = $filter;
		else
			foreach ($filter as $inner)
				$current[] = $inner;

		$this->set_where_clause_array($current);

		return $this;
	}

	/**
	 * Resets any previously set filters. Any data loaded will be invalidated.
	 * 
	 */
	public function setFilter($filter)
	{
		$this->set_where_clause_array($filter);

		return $this;
	}

	public function applyJoin($type, DBObjectManager $manager, $filter)
	{
		$this->joins[$type][] = array("manager" => $manager, "filter" => $filter);

		return $this;
	}

	public function setRandomOrder()
	{
		$this->set_order_by_function("rand");

		return $this;
	}

	public function setSortFunction($function, $arguments = null)
	{
		$this->set_order_by_function($function, $arguments);

		return $this;
	}

	public function setSortColumns($columns)
	{
		//TODO:
		//if the data is already loaded, resort by these columns if possible, otherwise reload.
		$this->set_order_by($columns);

		return $this;
	}

	public function setRange($start, $length)
	{
		//TODO:
		//if the data is already loaded,
		//and the range is a subset of the current range apply range, otherwise reload.
		$this->set_limit("$start,$length");

		return $this;
	}

	private $where_clause_array;

	protected function set_where_clause_array($array)
	{
		$this->where_clause_array = $array;
	}

	protected function get_where_clause_array()
	{
		return $this->where_clause_array;
	}

	public function build_cassandra_objects_from_filters($array)
	{
		$objects = array();
		foreach ((array) $array as $info)
		{
			$filters = (object) $info;
			if (isset($info->cassandra))
			{
				$objects = array_merge($info->cassandra, $objects);
			}
			//convert the mysql version
			else if (isset($info->column))
			{

				$columnParent = new cassandra_columnParent();
				$columnParent;

				$columnPath = new cassandra_columnPath();
				$columnPath->column_family = $this->managed_object() . "SUPER"; //"_INDEX";
				$columnPath->super_column = $info->column;
				$columnPath->column = $info->value;


				$sliceRange = new cassandra_SliceRange();
				$sliceRange->start = "";
				$sliceRange->finish = "";
				//$slice->count = ?? mysql limit?

				$slicePredicate = new cassandra_SlicePredicate();
				list() = $predicate->column_names;
				$slicePredicate->sliceRange = $sliceRange;
			}
			else if (isset($info["apply or"]))
			{
				Debugger::log("apply or not supported yet for cassandra look ups");
				/*
				  $inner_clause = $this->build_where_clause_from_array($info["apply or"]);
				  $type = "";
				  if(strlen($where_clause))
				  {
				  if(isset($info["type"]))
				  $type = $info["type"];
				  else
				  $type = "&&";
				  }
				  $where_clause .= "$type ($inner_clause) ";
				 */
			}
		}
	}

	protected function build_mysql_where_clause_from_filters($array)
	{
		//error_log("<pre>Where: " . print_r($array,1) . "</pre>");
		$where_clause = "";
		foreach ((array) $array as $info)
		{
			if (isset($info["column"]))
			{
				$column = "`" . $this->db_name() . "`" . $this->table() . "`" . "`" . $info["column"] . "`";
				//error_log($column);
				$comp = "=";
				if (isset($info["comparison"]))
					$comp = $info["comparison"];
				if (isset($info["wrap"]) && $info["wrap"] == "off")
					$wrap = "";
				else
					$wrap = "'";
				$type = "";
				if (strlen($where_clause))
				{
					if (isset($info["type"]))
						$type = $info["type"];
					else
						$type = "&&";
				}
				if (isset($info["value"]))
				{

					if (is_array($info["value"]))
					{
						$values = array();

						foreach ($info["value"] as $value)
						{
							if (isset($info['addslashes']) && $info['addslashes'] == "off")
								$value = $info["value"];
							else
								$values[] = $this->db()->escape_string($value);
						}
						$where_clause .= "$type $column $comp ('" . implode("','", $values) . "') ";
					}
					else if (strtolower($comp) == "in")
					{
						$where_clause .= "$type $column $comp ({$wrap}" . $info["value"] . "{$wrap})";
					}
					else
					{
						if (isset($info['addslashes']) && $info['addslashes'] == "off")
							$value = $info["value"];
						else
							$value = $this->db()->escape_string($info["value"]);

						if (strtolower($type) == "in")
							$where_clause .= "$type $column $comp ($value) ";
						else
							$where_clause .= "$type $column $comp {$wrap}$value{$wrap} ";
					}
				}
				//else
				//error_log("value not set...");
			}
			else if (isset($info["apply or"]))
			{

				$inner_clause = $this->build_where_clause_from_array($info["apply or"]);
				$type = "";
				if (strlen($where_clause))
				{
					if (isset($info["type"]))
						$type = $info["type"];
					else
						$type = "&&";
				}
				$where_clause .= "$type ($inner_clause) ";
			}
			//else
			//error_log("column not set..." . print_r($info,1));
		}
		//Debugger::log("where clause: ".$where_clause);
		return $where_clause;
	}

	private function build_where_clause_from_array($array)
	{
		//Debugger::log("<pre>Where: " . print_r($array,1) . "</pre>");
		$where_clause = "";
		foreach ((array) $array as $info)
		{
			if (isset($info["column"]))
			{
				$column = "`" . $this->db_name() . "`.`" . $this->table() . "`.`" . $info["column"] . "`";
				if (isset($info["table"]))
				{
					$column = "";
					if (isset($info["db"]))
						$column = "`" . $info["db"] . "`.";
					$column .= "`" . $info["table"] . "`.`" . $info["column"] . "`";
				}

				$comp = "=";
				if (isset($info["comparison"]))
					$comp = $info["comparison"];
				if (isset($info["wrap"]) && $info["wrap"] == "off")
					$wrap = "";
				else
					$wrap = "'";
				$type = "";
				if (strlen($where_clause))
				{
					if (isset($info["type"]))
						$type = $info["type"];
					else
						$type = "&&";
				}
				if (isset($info["value"]))
				{

					if (is_array($info["value"]))
					{
						$values = array();

						foreach ($info["value"] as $value)
						{
							if (isset($info['addslashes']) && $info['addslashes'] == "off")
								$value = $info["value"];
							else
							{
								//	$values[] = $this->db()->escape_string($value);
								$values[] = addslashes($value);
							}
						}
						$where_clause .= "$type $column $comp ('" . implode("','", $values) . "') ";
					}
					else if (strtolower($comp) == "in")
					{
						$where_clause .= "$type $column $comp ({$wrap}" . $info["value"] . "{$wrap})";
					}
					else
					{
						if (isset($info['addslashes']) && $info['addslashes'] == "off")
							$value = $info["value"];
						else
							$value = $this->db()->escape_string($info["value"]);

						if (strtolower($type) == "in")
							$where_clause .= "$type $column $comp ($value) ";
						else
							$where_clause .= "$type $column $comp {$wrap}$value{$wrap} ";
					}
				}
				//else
				//error_log("value not set...");
			}
			else if (isset($info["apply or"]))
			{

				$inner_clause = $this->build_where_clause_from_array($info["apply or"]);
				$type = "";
				if (strlen($where_clause))
				{
					if (isset($info["type"]))
						$type = $info["type"];
					else
						$type = "&&";
				}
				$where_clause .= "$type ($inner_clause) ";
			}
			//else
			//error_log("column not set..." . print_r($info,1));
		}
		//Debugger::log("where clause: ".$where_clause);
		return $where_clause;
	}

	public function indexHint($index)
	{
		if (is_array($index))
		{
			foreach ($index as $key => $value)
			{
				if ($key == "force")
					$this->force_index[] = $value;
				if ($key == "use")
					$this->use_index[] = $value;
				if ($key == "ignore")
					$this->ignore_index[] = $value;
			}
		}

		return $this;
	}

	private $force_index, $use_index, $ignore_index;

	protected function index_hints()
	{
		$string = "";
		if (isset($this->force_index))
			foreach ($this->force_index as $value)
				$string = " FORCE INDEX (`$value`) ";

		if (isset($this->use_index))
			foreach ($this->use_index as $value)
				$string = " USE INDEX (`$value`) ";

		if (isset($this->ignore_index))
			foreach ($this->ignore_index as $value)
				$string = " IGNORE INDEX (`$value`) ";

		return $string;
	}

	/**
	 * The WHERE clause for the SQL statement used to pull the managed objects from the table.
	 * 
	 * Default value is no WHERE clause.
	 *  
	 * @return string
	 */
	protected function where_clause()
	{
		$clause = array();
		if (isset($this->where_clause_array))
			$clause[] = $this->build_where_clause_from_array($this->where_clause_array);
		if (isset($this->joins))
		{
			foreach ($this->joins as $type => $managers)
			{
				foreach ($managers as $manager)
				{
					$manager = $manager["manager"];
					if (isset($manager->where_clause_array))
					{
						$manager_clause = $manager->build_where_clause_from_array($manager->where_clause_array);
						if (strlen($manager_clause))
						{

							$clause[] = $manager_clause;
						}
					}
				}
			}
		}
		if (count($clause))
			return implode(" && ", $clause);
	}

	protected function join_clause()
	{
		$clause = "";
		if (isset($this->joins))
		{
			foreach ($this->joins as $type => $information)
			{
				foreach ($information as $info)
				{
					$manager = $info["manager"];
					$filter = $info["filter"];

					$clause .= $type . " JOIN `" . $manager->db_name() . "`.`" . $manager->table() . "` ON `" .
						   $this->db_name() . "`.`" . $this->table() . "`.`" . $filter[0] . "` = `" .
						   $manager->db_name() . "`.`" . $manager->table() . "`.`" . $filter[1] . "` ";
				}
			}
		}
		if (strlen($clause))
			return $clause;
	}

	protected function group_by_clause()
	{
		
	}

	private $order_by_clause_order;

	/**
	 * Set the array that will be used to build the order by clause format:
	 * 	array("column" => "desc","column2","asc") => `column` DESC `column2` ASC
	 */
	protected function set_order_by($order)
	{
		//error_log("set order by: " . print_r($order,1));
		if (!is_array($order))
			$order = array($order => "ASC");
		$this->order_by_clause_order = $order;
	}

	public function applySort($column, $order = "ASC")
	{
		$this->order_by_clause_order = array($column => $order);
	}

	private $order_by_function;

	protected function set_order_by_function($function, $arguments=null)
	{
		if (!isset($arguments))
			$arguments = array();
		$this->order_by_function = array($function => $arguments);
	}

	protected function order_by_clause()
	{
		$order_by = "";
		if (isset($this->order_by_function))
		{
			foreach ($this->order_by_function as $function => $arguments)
			{
				$order_by .= strtoupper($function) . "(" . implode(",", $arguments) . ") DESC";
			}
		}
		if (isset($this->order_by_clause_order))
		{

			foreach ($this->order_by_clause_order as $column => $direction)
			{
				if (strlen($order_by))
					$order_by .= ",";
				$order_by .= "`{$this->db_name()}`.`{$this->table()}`.`$column` $direction";
			}
		}

		return $order_by;
	}

	private $limit;

	protected function set_limit($limit)
	{
		$this->limit = $limit;
	}

	protected function get_limit()
	{
		return $this->limit;
	}

	/**
	 * Not used.
	 * 
	 */
	protected function maximum_storage_requests_before_storage()
	{
		return 1;
	}

	/**
	 *
	 * Called for each row to create the managed object.
	 *
	 * Example:
	  function create_managed_object($database_row)
	  {
	  $blacklist = new Blacklist($database_row->ip);
	  $blacklist->load_from_sql_result($database_row);
	  return $blacklist;
	  }
	 *
	 * return @DBObject subclass
	 */
	abstract protected function create_managed_object($obj);

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
	}

	private function callStaticOnManagedObject($function)
	{
		return call_user_func(array($this->managed_object(), $function));
	}

	/**
	 *
	  SELECT * FROM
	  (
	  SELECT `id` FROM `member_spotlight_pregen`
	  ORDER BY RAND()
	  LIMIT ". (self::$AUTHOR_SPOTLIGHT_MAX * 3)."
	  ) as tbl
	  LEFT OUTER JOIN `member_spotlight_pregen` AS msp
	  ON `tbl`.`id` = msp.`id`
	 */
	protected function select_random($number)
	{
		return "SELECT `id` FROM `member_spotlight_pregen` ORDER BY RAND() LIMIT $limit";
	}

	protected final function select()
	{
		return $this->get_select_sql(null);
	}

	protected final function select_for_memcache()
	{
		$primary_key = $this->get_primary_key();
		if (isset($primary_key))
		{
			return self::get_select_sql($primary_key);
		}
	}

	/*
	 * 
	 * 
	 */

	private $primary_keys;

	final public function setPrimaryKeys($primary_keys)
	{
		$this->primary_keys = $primary_keys;
		$this->applyFilter(array("column" => $this->get_primary_key(), "value" => $primary_keys, "comparison" => "IN"));
		return $this;
	}

	final public function getPrimaryKeys()
	{
		if (!isset($this->primary_keys))
		{
			$primary_key = $this->get_primary_key();
			$sql = self::get_select_sql($primary_key);

			if ($this->canUseMemcache() && !isset($random))
			{
				$primary_keys = $this->getMemcached($sql);
				if (!is_array($primary_keys))
					unset($primary_keys);
				else
					$this->loaded = true;
			}
			if (!isset($primary_keys))
			{
				if ($results = $this->db()->query($sql))
				{
					$primary_keys = array();
					//error_log("loading....");
					$this->loaded = true;
					while ($obj = $results->fetch_object())
					{
						$primary_keys[] = $obj->$primary_key;
					}
				}
				if ($this->canUseMemcache() && !isset($random))
				{
					$this->setMemcached($sql, $primary_keys);
				}
			}
			$this->primary_keys = $primary_keys;
		}
		else
			$this->loaded = true;
		return $this->primary_keys;
	}

	final private function loadFromPrimaryKeys($random = null)
	{
		$primary_key = $this->get_primary_key();
		if (isset($primary_key))
		{
			$primary_keys = $this->getPrimaryKeys();

			$managed_objects = array();
			foreach ((array) $primary_keys as $key)
			{
				//error_log("create with key: $key");
				$object = $this->create_managed_object_with_primary_value($key);
				if ($this->managedObjectAllowsMemcache())
					$managed_objects[$object->getMemcacheKey()] = $object;
				else
					$managed_objects[] = $object;
			}
			if ($this->managedObjectAllowsMemcache() && count($managed_objects))
			{
				//error_log(print_r($memcache_keys,true));
				$memcache_data = $this->getMemcached(array_keys($managed_objects));
				//error_log(print_r(array_keys($memcache_data),true));
				//error_log("managed object keys: " . print_r(array_keys($managed_objects),true));
				foreach ($managed_objects as $key => $managed_object)
				{
					if (isset($memcache_data[$key]) && is_object($memcache_data[$key]))
					{
						//	error_log("load from sql result...");
						$managed_object->load_from_sql_result($memcache_data[$key]);
					}
					//else
					//	error_log("$key not found");
				}
			}

			$this->managed_objects = array_values($managed_objects);
		}
		//else
		//error_log("primary key is not defined!");
	}

	final protected function get_memcache_key_for_primary_value($value)
	{
		
	}

	final protected function create_managed_object_with_primary_value($value)
	{
		$managed_object_reflection = new ReflectionClass($this->managed_object());

		if ($managed_object_reflection->hasMethod('__construct'))
		{
			if ($managed_object_reflection->hasMethod('primary_key_to_object'))
			{
				$primary_key_to_object_method = new ReflectionMethod($this->managed_object(), 'primary_key_to_object');
				if ($primary_key_to_object_method->isStatic())
				{
					$arg_obj = $primary_key_to_object_method->invoke(null, $value);

					$args = array($arg_obj);
					$instance = $managed_object_reflection->newInstanceArgs($args);
				}
				//else
				//error_log("primary_key_to_object must be static");
			}
			else
			{
				$instance = $managed_object_reflection->newInstanceArgs((array) $value);
			}
		}
		else
		{
			$instance = $managed_object_reflection->newInstance();
		}
		if (isset($instance))
			return $instance;
		//else
		//error_log("unable to create instance for key $value");
	}

	/**
	 * Override to perform specific functionality when a managed object is modified.
	 * 
	 * 
	 */
	protected function did_change_field(DBObject $object, $field)
	{
		
	}

	public final function managedObjectAllowsMemcache()
	{
		$managed_object_reflection = new ReflectionClass($this->managed_object());
		if ($managed_object_reflection->hasMethod("allowMemcache"))
		{
			$allow_memcache_method = new ReflectionMethod($this->managed_object(), 'allowMemcache');
			if ($allow_memcache_method->isStatic())
				return $allow_memcache_method->invoke(null);
		}
		return false;
	}

	/**
	 * Loaded the objects from memcache.
	 * 
	 * Note: The same memcache key is used here as is used in DBObject 
	 */
	private function loadFromMemCache()
	{
		if ($this->managedObjectAllowsMemcache() && $this->isMemcachedAvailable())
		{
			$this->loadFromPrimaryKeys();
		}
		else if ($this->managedObjectAllowsMemcache() && !$this->isMemcachedAvailable())
		{
			//error_log("mem cache server unavailable");
		}
	}

	function isMemcachedAvailable()
	{
		return call_user_func(array($this->managed_object(), "isMemcachedAvailable"));
	}

	function getMemcached($key)
	{
		return call_user_func_array(array($this->managed_object(), "getMemcached"), array($key));
	}

	function setMemcached($key, $val, $ttl=0)
	{
		return call_user_func_array(array($this->managed_object(), "setMemcached"), array($key, $val, $ttl));
	}

	/**
	 * The key to store the managed objects in memcache.
	 * 
	 */
	protected function getMemCacheKey()
	{
		return $this->get_select_sql();
	}

	final protected function loadCount($no_limit = false)
	{
		if ($no_limit)
		{
			$this->loaded_no_limit_count = true;
			$this->loadCountFromDatabase($no_limit);
			return;
		}
		if (!$this->loaded_count)
		{
			$this->loaded_count = true;
			$this->loadCountFromDatabase();
		}
	}

	/**
	 * Load the values from the table, if no values are given. All columns are loaded.
	 */
	final protected function load($values = null)
	{


		if (!$this->loaded && $this->managedObjectAllowsMemcache() && $this->isMemcachedAvailable())
		{
			$this->loadFromMemCache();
		}

		if (!$this->loaded)
		{
			$this->loaded = true;
			$this->loadFromDatabase($values);
		}
	}

	final protected function loadRandom($number, $values = null)
	{
		if (!$this->loaded && $this->managedObjectAllowsMemcache() && $this->isMemcachedAvailable())
		{
			$this->loadRandomFromMemCache($number);
		}

		if (!$this->loaded)
		{
			$this->loaded = true;
			$this->loadRandomFromDatabase($number, $values);
		}
	}

	protected function custom_sql_select()
	{
		
	}

	final private function get_select_random_sql($number, $values = null)
	{

		if ($values == null)
		{
			$sql = "SELECT *";
		}
		else
		{
			$sql = "SELECT `" . implode("`,`", (array) $values) . "`";
		}

		$primary_key = $this->get_primary_key();
		$sql .= " FROM ( SELECT `$primary_key` FROM `{$this->table()}`";

		if (strlen($this->where_clause()))
			$sql .= " WHERE " . $this->where_clause();
		else
		{
			if ($this->callStaticOnManagedObject("dummyMode"))
			{

				if ($this->managed_object() != "Employee" && $this->managed_object() != 'VideoCategory' && $this->managed_object() != 'Blacklist' && $this->managed_object() != 'Whitelist' && $this->managed_object() != 'Graylist')
				{
					Debugger::log("SELECTING ENTIRE TABLE! $sql\n");

					throw new Exception("bad programmer, no cookie! {$this->managed_object()}");
				}
				else
				{
					Debugger::log("{$this->managed_object()} using DEPRECATED dummyMode()");
				}
			}
		}

		$sql .= "  ORDER BY RAND()";
		$sql .= " LIMIT $number ) as random_{$this->table()}";
		$sql .= " LEFT OUTER JOIN `{$this->table()}`	ON `{$this->table()}`.`$primary_key` = `random_{$this->table()}`.`$primary_key`";


		if (strlen($this->order_by_clause()))
			$sql .= " ORDER BY " . $this->order_by_clause();


		//error_log("get select random sql: $sql");

		return $sql;
	}

	protected function get_select_sql($values = null)
	{
		$custom = $this->custom_sql_select();

		if (isset($custom) && strlen($custom))
		{
			$sql = "SELECT $custom";
		}
		else if ($values == null)
		{

			$sql = "SELECT `{$this->table()}`.*";
		}
		else
		{
//			$sql = "SELECT `" . implode("`,`",(array)$values) . "`";
			$sql = "SELECT `{$this->table()}`.`" . implode("`,`{$this->table()}`.`", (array) $values) . "`";
		}

		$sql .= " FROM `" . $this->db_name() . "`.`" . $this->table() . "` ";

		if (strlen($this->join_clause()))
			$sql .= $this->join_clause();

		if ($this->index_hints())
			$sql .= " {$this->index_hints()} ";

		if (strlen($this->where_clause()))
			$sql .= " WHERE " . $this->where_clause();
		else if (!strlen($this->get_limit()))
		{
			if ($this->callStaticOnManagedObject("dummyMode"))
			{

				if ($this->managed_object() != "Employee" && $this->managed_object() != 'VideoCategory' && $this->managed_object() != 'Blacklist' && $this->managed_object() != 'Whitelist' && $this->managed_object() != 'Graylist')
				{
					Debugger::log("SELECTING ENTIRE TABLE! $sql\n");

					throw new Exception("bad programmer, no cookie! {$this->managed_object()}");
				}
				else
				{
					Debugger::log("{$this->managed_object()} using DEPRECATED dummyMode()");
				}
			}
		}
		if (strlen($this->group_by_clause()))
			$sql .= " GROUP BY " . $this->group_by_clause();
		if (strlen($this->order_by_clause()))
			$sql .= " ORDER BY " . $this->order_by_clause();
		if (strlen($this->get_limit()))
			$sql .= " LIMIT " . $this->get_limit();

		//error_log("get select sql: $sql");

		return $sql;
	}

	final private function get_object_count_sql($values = null)
	{


		$sql = "SELECT COUNT(1) as count";
		$sql .= " FROM `" . $this->db_name() . "`.`" . $this->table() . "` ";

		if (strlen($this->join_clause()))
			$sql .= $this->join_clause();

		if ($this->index_hints())
			$sql .= " {$this->index_hints()} ";

		if (strlen($this->where_clause()))
			$sql .= " WHERE " . $this->where_clause();
		//if(strlen($this->order_by_clause()))
		//	$sql .= " ORDER BY " . $this->order_by_clause();
		if (strlen($this->group_by_clause()))
			$sql .= " GROUP BY " . $this->group_by_clause();
		if (strlen($this->get_limit()))
			$sql .= " LIMIT " . $this->limit;
		return $sql;
	}

	private function loadCountFromDatabase($no_limit = false)
	{
		$sql = $this->get_object_count_sql();
		//error_log("$sql");
		if ($result = $this->db()->query($sql))
		{
			if ($row = $result->fetch_object())
			{
				if ($no_limit)
					$this->managed_object_no_limit_count = $row->count;
				else
					$this->managed_object_count = $row->count;
			}
			else
			{
				if ($no_limit)
					$this->managed_object_no_limit_count = $row->count;
				else
					$this->managed_object_count = 0;
			}
		}
		else
			error_log("no results? " . $this->db()->getLastError());
	}

	/**
	 * Create a new object from the database and table, with the clause. 
	 * The clause should not specify a limit as this is already included.
	 * 
	 * If no values are specified, all columns will be loaded.
	 */
	private function loadRandomFromDatabase($number, $values = null)
	{

		//error_log(print_r(array_slice(debug_backtrace(false),0,3),true));
		$sql = $this->get_select_random_sql($number, $values);
		//error_log("loadFromDatabase: $sql");

		if ($result = $this->db()->query($sql))
		{

			$managed_objects = array();

			while ($obj = $result->fetch_object())//fetch_next())
			{
				$managed_objects[] = $this->create_managed_object($obj);
			}

			if ($this->managedObjectAllowsMemcache() && $this->isAvailable())
			{
				$this->setMemcached($this->getMemCacheKey(), $managed_objects);
			}
			$this->managed_objects = $managed_objects;
		}
		//else
		//error_log($this->db()->getLastError());
	}

	/**
	 * Create a new object from the database and table, with the clause. 
	 * The clause should not specify a limit as this is already included.
	 * 
	 * If no values are specified, all columns will be loaded.
	 */
	private function loadFromDatabase($random = null, $values = null)
	{

		//error_log(print_r(array_slice(debug_backtrace(false),0,3),true));
		if (isset($random))
			$sql = $this->get_select_random_sql($random, $values);
		else
			$sql = $this->get_select_sql($values);
		//error_log($sql);	
		if ($result = $this->db()->query($sql))
		{

			$managed_objects = array();

			while ($obj = $result->fetch_object())//fetch_next())
			{
				$managed_objects[] = $this->create_managed_object($obj);
			}

			if ($this->managedObjectAllowsMemcache() && $this->isMemcachedAvailable())
			{
				$this->setMemcached($this->getMemCacheKey(), $managed_objects);
			}
			$this->managed_objects = $managed_objects;
		}
		//else
		//error_log($this->db()->getLastError());
	}

	final private function saveAddedManagedObjects()
	{

		$added_objects = $this->getAddedManagedObjects();
		if (!isset($added_objects) || count($added_objects) == 0)
		{
			return true;
		}
		$columns = array();

		foreach ($added_objects as $object)
		{
			$columns = array_unique(array_merge($columns, $object->modified_fields()));
		}

		$add_values = array();
		foreach ($added_objects as $object)
			$add_values[] = implode(",", $object->columnsToValues($columns));

		$add_sql = "INSERT INTO {$this->table()} (`" . implode("`,`", $columns) . "`) VALUES";
		$add_sql .= "(" . implode("),(", $add_values) . ")";

		return $this->db()->execute($add_sql);
	}

	private function saveRemovedManagedObjects()
	{

		$removed_object = $this->getRemovedManagedObjects();
		if (!isset($removed_object) || count($removed_object) == 0)
			return;
		$removed_where = array();
		foreach ($removed_object as $object)
			$remove_where[] = $object->where_clause();

		$remove_sql = "DELETE FROM {$this->table()} WHERE ";
		$remove_sql .= " (" . implode(") || (", $remove_where) . ")";

		return $this->db()->execute($remove_sql);
	}

	private function saveUpdatedManagedObjects()
	{
		$updated_object = $this->getUpdatedManagedObjects();
		if (!isset($updated_objects) || count($updated_objects) == 0)
			return true;
		foreach ($updated_object as $object)
		{
			$object->save();
		}
	}

	final protected function saveManagedObjects()
	{
		$this->saveAddedManagedObjects();

		$this->saveRemovedManagedObjects();

		$this->saveUpdatedManagedObjects();
	}

	public function save()
	{
		foreach ($this->getManagedObjects() as $obj)
		{
			if (!$obj->save())
			{
				//error_log("Unable to save managed object: $obj");
			}
		}

		foreach ((array) $this->removed_objects as $obj)
		{
			if (!$obj->delete())
			{
				//error_log("Unable to remove managed object; $obj");
			}
		}
		//reset removed objects so we don't attempt to remove them again.
		$this->removed_objects = array();

		return true;
	}

	public function iterator()
	{
		Loader::load("model", "DBObjectIterator");
		$iterator = new DBObjectIterator($this->managed_object(), $this->db(), $this->table(), $this->db_name());

		$iterator->set_where_clause_array($this->where_clause_array);
		$iterator->set_limit($this->get_limit());
		return $iterator;
	}

	public function collection()
	{
		Loader::load("model", "DBObjectCollection");
		$collection = new DBObjectCollection($this->managed_object(), $this->db(), $this->table(), $this->db_name());

		$collection->setFilter($this->get_where_clause_array());
		return $collection;
	}

	public function getStatistics()
	{

		Loader::load("model", "DBStatistics");

		$reflection = new ReflectionClass($this->managed_object());
		$object = $reflection->newInstance();
		$statistics = new DBStatistics($this->managed_object(), $this->db(), $this->table(), $this->db_name());
		//$statistics->setSumColumns($object->getIncrementalKeys());
		$statistics->setFilter($this->get_where_clause_array());
		//$statistics->setGroupColumns($this->group_by);

		return $statistics;
	}

	final protected function is_applying_filter($fn)
	{
		return (strtolower(substr($fn, 0, 5)) == 'apply' && strtolower(substr($fn, -6)) == 'filter');
	}

	final protected function is_applying_sort($fn)
	{
		return (strtolower(substr($fn, 0, 5)) == 'apply' &&
		(strtolower(substr($fn, 5, 9)) == 'ascending' ||
		strtolower(substr($fn, 5, 10)) == 'descending') &&
		strtolower(substr($fn, -4)) == 'sort');
	}

}
?>