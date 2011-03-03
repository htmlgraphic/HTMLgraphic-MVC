<?php
Loader::load("model", "CassandraObjectManager");

class CassandraObjectCollection extends CassandraObjectManager
{
	function __construct($class, $keyspace, $name, $connection, $consistency=null)
	{
		$reflection = new ReflectionClass($class);
		if ($reflection->isSubclassOf("CassandraObject"))
		{
			$this->managed_object = $class;
			$this->keyspace = $keyspace;
			$this->column_family = $name;
			$this->connection = $connection;
			if (isset($consistency))
				$this->consistency = $consistency;
		}
		else
		{
			throw new Exception("$class is not a CassandraObject");
		}
	}

	function managed_object()
	{
		return $this->managed_object;
	}

	function keyspace()
	{
		return $this->keyspace;
	}

	function column_family()
	{
		return $this->column_family;
	}

	function connection()
	{
		return $this->connection;
	}

	function consistency()
	{
		if (isset($this->consistency))
			return $this->consistency;
		else
			return parent::consistency();
	}

	function create_managed_object($object_data)
	{
		$managed_object_reflection = new ReflectionClass($this->managed_object());

		return $managed_object_reflection->newInstanceArgs($object_data->uuid);
	}

	public function __call($name, $arguments)
	{
		if ($this->is_applying_sort($name) || $this->is_applying_filter($name))
			return parent::__call($name, $arguments);

		switch ($name)
		{
			case "get{$this->managed_object}s":
			case "get{$this->managed_object}es":
			case "get" . substr($this->managed_object, 0, -1) . "ies":
				return $this->getManagedObjects();
			case "getFirst{$this->managed_object}":
				if (!$this->getManagedObjects() || reset($this->getManagedObjects()) === false)
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
	}

}
?>