<?php
class DatabaseTable
{
	function __construct(Database $connection, $table_name, $database)
	{
		$this->connection = $connection;
		$this->table = $table_name;
		$this->database = $database;
	}

	private function get_column_info()
	{
		if (!isset($this->columns))
		{
			$columns = array();

			$sql = "SHOW COLUMNS FROM `{$this->database}`.`{$this->table}`";
			$results = $this->connection->query($sql);

			$fields = array();
			while ($column_info = $results->fetch_object())
			{

				$column = new stdclass();
				$column->name = $column_info->Field;
				$column->type = $column_info->Type;
				$column->nullable = $column_info->Null;
				if (strlen($column_info->Key))
					$column->key = $column_info->Key;
				$column->default_value = $column_info->Default;
				$column->extra = $column_info->Extra;

				$columns[$column->name] = $column;
			}

			$this->columns = $columns;
		}

		return $this->columns;
	}

	private function get_index_info()
	{
		if (!isset($this->indexes))
		{
			$indexes = array();

			$sql = "SHOW INDEXES FROM `{$this->database}`.`{$this->table}`";
			$results = $this->connection->query($sql);

			while ($index_info = $results->fetch_object())
			{
				$indexes[] = $index_info;
			}
			$this->indexes = $indexes;
		}

		return $this->indexes;
	}

	function getColumns()
	{
		return array_keys($this->get_column_info());
	}

	function getPrimaryColumns()
	{
		$primaries = array();
		foreach ($this->get_column_info() as $name => $column_info)
		{
			if (isset($column_info->key) && $column_info->key == "PRI")
				$primaries[] = $name;
		}

		return $primaries;
	}

	function getIndexedColumns()
	{
		$indexes = $this->get_index_info();
		$columns = array();

		foreach ($this->get_index_info() as $index_info)
		{
			if ($index_info->Table == $this->table && $index_info->Seq_in_index == 1)
				$columns[] = $index_info->Column_name;
		}

		return $columns;
	}

}
?>