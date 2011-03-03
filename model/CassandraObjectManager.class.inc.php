<?php
abstract class CassandraObjectManager
{
	/**
	 * The managed objects.
	 *
	 * @return array of managed objects.
	 */
	final protected function getManagedObjects()
	{
		if (!isset($this->managed_objects))
		{
			$this->load();
		}
		return $this->managed_objects;
	}

	/**
	 * The type of object to be managed
	 *
	 * @return string
	 *
	 */
	abstract protected function managed_object();
	/**
	 * The keyspace for the managed objects
	 *
	 * @return Database
	 */
	abstract protected function keyspace();
	/**
	 * Name of column_family where the managed objects are stored.
	 *
	 * @return string
	 */
	abstract protected function column_family();
	abstract protected function connection();
	protected function consistency()
	{
		return cassandra_ConsistencyLevel::ONE;
	}

	public function __call($name, $arguments)
	{
		/*
		  if($this->is_applying_sort($name))
		  {
		  $order = strtolower(substr($name,5,9)) == "ascending" ? "ASC" : "DESC";
		  Debugger::log($order);
		  $function = array($this->managed_object(), substr($name, $order == "ASC" ? 14 : 15));
		  Debugger::log($function);
		  if(method_exists($function[0],$function[1]))
		  {
		  Debugger::log($function[0] . "::" . $function[1] ." does exist.");
		  $this->applySort(call_user_func_array($function,null),$order);
		  return $this;
		  }
		  else
		  {
		  Debugger::log($function[0] . "::" . $function[1] ." does not exist.");
		  error_log($function[0] . "::" . $function[1] ." does not exist.");
		  }

		  }
		  else
		 */
		if ($this->is_applying_filter($name))
		{
			//Debugger::log("filter: $name");
			$function = array($this->managed_object(), substr($name, 5));

			if (method_exists($function[0], $function[1]))
			{
				$this->applyFilter(call_user_func_array($function, $arguments));
				return $this;
			}
			else
			{
				Debugger::log($function[0] . "::" . $function[1] . " does not exist.");
				error_log($function[0] . "::" . $function[1] . " does not exist.");
			}
		}


		trigger_error("Call to undefined method CassandraObjectManager->{$name}() on {$this->managed_object()}");
	}

	private $columns;

	public function retrieveColumn($column)
	{
		$this->columns[] = $column;
		return $this;
	}

	private $filters;

	public function applyFilter($filter)
	{
		if (!isset($this->filters))
			$this->filters = array();

		$this->filters[] = $filter;

		//$this->filters[] = new cassandra_IndexExpression(array("column_name" => $filter["column"],
		//				"op" => !isset($filter["comparison"]) ? cassandra_IndexOperator::EQ : $filter["comparison"],
		//				"value" => $filter["value"]));


		return $this;
	}

	public function setSortColumns($columns)
	{
		//TODO:
		//if the data is already loaded, resort by these columns if possible, otherwise reload.
		$this->set_order_by($columns);

		return $this;
	}

	private $range;

	public function setRange($start, $length)
	{
		$this->range = array("start" => $start, "length" => $length);

		return $this;
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
				$primary_keys = EAMemcache::get($sql);
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
					EAMemcache::set($sql, $primary_keys);
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
				$memcache_data = EAMemCache::get(array_keys($managed_objects));
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

	/**
	 * Load the values from the table, if no values are given. All columns are loaded.
	 */
	private $loaded;

	final protected function load()
	{
		if (!$this->loaded)
		{
			$this->loaded = true;
			$this->loadFromStorage();
			//if filters are provided, use filter selection
			//otherwise, return everything.
			//limits should also be respected, but shouldn't be necessary immediately.
		}
	}

	/**
	 * Create a new object from the database and table, with the clause.
	 * The clause should not specify a limit as this is already included.
	 *
	 * If no values are specified, all columns will be loaded.
	 */
	private function loadFromStorage()
	{
		if (isset($this->filters))
		{
			#print_r($this->filters);
			$rows = $this->connection()
						 ->read_with_indexes($this->keyspace(),
							    $this->column_family(),
							    $this->filters,
							    $this->columns,
							    $this->range,
							    $this->consistency()
			);
			$objects = array();
			$managed_object_reflection = new ReflectionClass($this->managed_object());
			foreach ($rows as $key => $value)
			{
				$object = $managed_object_reflection->newInstanceArgs(array($key));
				$object->load_from_data($value);
				$objects[$key] = $object;
			}
			$this->managed_objects = $objects;
			return;
		}
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