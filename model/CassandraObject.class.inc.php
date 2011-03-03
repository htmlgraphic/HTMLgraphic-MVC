<?php
/*
  Loader::load("vendor","thrift/Thrift.php");
  Loader::load("vendor","thrift/transport/TSocket.php");
  Loader::load("vendor","thrift/transport/TBufferedTransport.php");
  Loader::load("vendor","thrift/transport/TFramedTransport.php");

  Loader::load("vendor","thrift/protocol/TBinaryProtocol.php");
  Loader::load("vendor","thrift/packages/cassandra/Cassandra.php");
  Loader::load("vendor","thrift/packages/cassandra/cassandra_types.php");
 */

Loader::load("model", "Model");

abstract class CassandraObject extends Model
{
	function __construct($uuid = null)
	{
		if (!isset($uuid))
		{

			Loader::load("vendor", "phpcassa/columnfamily.php");
			$this->uuid = CassandraUtil::uuid1();

			//Loader::load("vendor","uuid/uuid");
			//		$this->uuid = UUID::TimeUUIDType();
			//		$this->uuid_binary = UUID::generate(UUID::UUID_TIME,UUID::FMT_BINARY);
			$this->data = new stdclass();
		}
		else
		{
			$this->uuid = $uuid;
		}
	}

	/*
	  public function __call($name,$args)
	  {
	  if(strtolower(substr($name, -6)) == 'filter')
	  {
	  //check if column family has a filter for this.
	  }
	  }
	 */
	function getUUID()
	{
		return $this->uuid;
	}

	function isValid()
	{
		return $this->is_valid;
	}

	private $cassandra_definition;

	final protected function cassandra_definition()
	{
		//if(!isset($this->cassandra_definition))
		//	$this->cassandra_definition = $this->thrift_connection()->getColumnFamilyDefinition($this->keyspace(),$this->column_family());
		return $this->cassandra_definition;
	}

	abstract protected function keyspace();
	abstract protected function column_family();
	abstract protected function thrift_connection();

	private $data;

	final protected function load_data()
	{
		if (!isset($this->data))
		{
			try
			{
				$this->data = $this->thrift_connection()->read(
							 $this->keyspace(),
							 $this->uuid,
							 $this->column_family(),
							 $this->consistency()
				);
			} catch (Exception $e)
			{
				error_log("Failed Cassandra Read");
			}

			$this->loaded = true;
			$this->transport->close();
		}

		return $this->data;
	}

	final function load_from_data($data)
	{
		$this->data = $data;
		$this->loaded = true;
	}

	final function load_from_data_columns($data_columns)
	{
		$this->data = new stdclass();
		foreach ($data_columns as $data_column)
		{
			$column = $data_column->column;
			$key = $column->name;
			$value = $column->value;
			$this->data->$key = $value;
		}
		$this->loaded = true;
	}

	final protected function get_data_value($name)
	{
		$this->load_data();

		if (isset($this->data[$name]))
			return $this->data[$name];
	}

	final protected function set_data_value($name, $new_value)
	{
		$this->load_data();

		//$this->changed_field($name,$this->getDataValue($name),$new_value);

		$this->data->$name = $new_value;
	}

	function consistency()
	{
		return cassandra_ConsistencyLevel::ONE;
	}

	function save()
	{
		try
		{
			return $this->thrift_connection()->write(
				   $this->keyspace(),
				   $this->uuid,
				   $this->column_family(),
				   $this->data,
				   $this->consistency()
			);
		} catch (Exception $e)
		{
			error_log("failed Cassandra save Method");
		}
	}

	function delete()
	{
		
	}

}
?>