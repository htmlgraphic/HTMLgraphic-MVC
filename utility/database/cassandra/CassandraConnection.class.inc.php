<?php

Loader::load("vendor", "thrift/Thrift.php");
Loader::load("vendor", "thrift/transport/TSocket.php");
Loader::load("vendor", "thrift/transport/TBufferedTransport.php");
Loader::load("vendor", "thrift/transport/TFramedTransport.php");

Loader::load("vendor", "thrift/protocol/TBinaryProtocol.php");
Loader::load("vendor", "thrift/packages/cassandra/Cassandra.php");
Loader::load("vendor", "thrift/packages/cassandra/cassandra_types.php");

class CassandraConnection
{

  private $settings;

  function __construct($settings)
  {
    $this->settings = $settings;
  }

  protected function connection($keyspace)
  {
    if (!isset($this->connection[$keyspace]))
    {
      Loader::load("vendor", "phpcassa/connection.php");
      $this->connection[$keyspace] = new connection($keyspace); //,$this->settings);
    }

    return $this->connection[$keyspace];
  }

  protected function connect()
  {
    if (!isset($this->client))
    {
      $this->host = $this->settings["ip"][rand(0, count($this->settings["ip"]) - 1)];

      $this->socket = new TSocket($this->host, $this->settings["port"]);
      if (isset($this->settings["receive_timeout"]))
        $this->socket->setRecvTimeout($this->settings["receive_timeout"]);
      $this->socket->setDebug(true);
      $this->transport = new TFramedTransport($this->socket);

      $this->protocol = new TBinaryProtocol($this->transport);
      $this->client = new CassandraClient($this->protocol);

      $this->transport->open();
    }
  }

  protected function disconnect()
  {
    $this->transport->close();
    unset($this->client);
  }

  public function get_client()
  {
    $this->connect();
    return $this->client;
  }

  private function convert_name($name)
  {
    $converted = $name;
    switch ($name)
    {
      
    }

    return $converted;
  }

  public function __call($name, $arguments)
  {
    if (method_exists("CassandraClient", $this->convert_name($name)))
    {
      $this->connect();
      return call_user_func_array(array($this->client, $this->convert_name($name)), $arguments);
    }
  }

  /*
    login

    void login(keyspace, auth_request)
    Authenticates with the cluster for operations on the specified keyspace using the specified AuthenticationRequest credentials. Throws AuthenticationException if the credentials are invalid or AuthorizationException if the credentials are valid, but not for the specified keyspace.
   */

  function login()
  {
    
  }

  /*
    get

    ColumnOrSuperColumn get(key, column_path, consistency_level)
    Get the Column or SuperColumn at the given column_path. If no value is present, NotFoundException is thrown. (This is the only method that can throw an exception under non-failure conditions.)
   */

  function get()
  {
    
  }

  /*
    get_slice

    list<ColumnOrSuperColumn> get_slice(key, column_parent, predicate, consistency_level)
    Get the group of columns contained by column_parent (either a ColumnFamily name or a ColumnFamily/SuperColumn name pair) specified by the given SlicePredicate struct.
   */

  function get_slice()
  {
    
  }

  /*
    multiget_slice

    map<string,list<ColumnOrSuperColumn>> multiget_slice(keys, column_parent, predicate, consistency_level)
    Retrieves slices for column_parent and predicate on each of the given keys in parallel. Keys are a `list<string> of the keys to get slices for.

    This is similar to get_range_slice (Cassandra 0.5) except operating on a set of non-contiguous keys instead of a range of keys.
   */

  function multiget_slice()
  {
    
  }

  /*
    get_count

    i32 get_count(key, column_parent, consistency_level)
    Counts the columns present in column_parent.

    The method is not O(1). It takes all the columns from disk to calculate the answer. The only benefit of the method is that you do not need to pull all the columns over Thrift interface to count them.
   */

  function get_count()
  {
    
  }

  /*
    get_range_slices

    list<KeySlice> get_range_slices(column_parent, predicate, range, consistency_level)
    Replaces get_range_slice. Returns a list of slices for the keys within the specified KeyRange. Unlike get_key_range, this applies the given predicate to all keys in the range, not just those with undeleted matching data. This method is only allowed when using an order-preserving partitioner.
   */

  /*
    get_indexed_slices

    list<KeySlice> get_indexed_slices(column_parent, index_clause, predicate, consistency_level)
    Like get_range_slices, returns a list of slices, but uses IndexClause instead of KeyRange. To use this method, the underlying ColumnFamily of the ColumnParent must have been configured with a column_metadata attribute, specifying at least the name and index_type attributes. See CfDef and ColumnDef above for the list of attributes. Note: the IndexClause must contain one IndexExpression with an EQ operator on a configured index column. Other IndexExpression structs may be added to the IndexClause for non-indexed columns to further refine the results of the EQ expression.
   */

  /*
    insert

    insert(key, column_path, value, timestamp, consistency_level)
    Insert a Column consisting of (column_path.column, value, timestamp) at the given column_path.column_family and optional column_path.super_column. Note that column_path.column is here required, since a SuperColumn cannot directly contain binary values -- it can only contain sub-Columns.
   */

  private function set_keyspace($keyspace)
  {
    try
    {
      $start = microtime(true);
      $this->get_client()->set_keyspace($keyspace);
      $end = microtime(true);
      //error_log("Thrift::set_keyspace($keyspace)");
      Debugger::query("Thrift::set_keyspace($keyspace)", $end - $start, $keyspace);
    }
    catch (Exception $e)
    {
      exit;
      //Debugger::log($e);
      throw new Exception("Unable to set keyspace.");
    }
  }

  function filtered_read($keyspace, $filters, $column_family, $consistency)
  {

    $columnParent = new cassandra_ColumnParent(
                    array("column_family" => $column_family));

    $count = 500;

    $predicate = new cassandra_SlicePredicate();
    $predicate->slice_range = new cassandra_SliceRange(array("start" => "", "finish" => "", "count" => 100));

    $indexExpressions = array();
    foreach ($filters as $filter)
    {
      $indexExpressions[] = new cassandra_IndexExpression(array("column_name" => $filter["column"],
                  "op" => !isset($filter["comparison"]) ? cassandra_IndexOperator::EQ : $filter["comparison"],
                  "value" => $filter["value"]));
    }
    $indexClause = new cassandra_IndexClause(array("start_key" => "", "count" => $count, "expressions" => $indexExpressions));

    $this->set_keyspace($keyspace);

    $expected = $count;
    $data = array();
    $exceptions_allowed = 10;
    do
    {
      $more = false;
      try
      {
        error_log("Thrift::get_indexed_slices(" . json_encode(array($columnParent, $predicate, $indexClause)) . ") {$this->host}"); //,$end-$start,$this->keyspace());
        $start = microtime(true);
        $indexes = $this->get_client()->get_indexed_slices($columnParent, $indexClause, $predicate, $consistency);
        $end = microtime(true);
        Debugger::query("Thrift::get_indexed_slices(" . json_encode(array($columnParent, $predicate, $indexClause)) . ")", $end - $start, $this->keyspace());


        $added = 0;
        foreach ((array)$indexes as $index)
        {
          if (!isset($data[$index->key]))
          {
            $keys[] = $index->key;

            $uuid = $index->key;
            $row_data = new stdclass();
            foreach ($index->columns as $row)
            {
              $key = $row->column->name;
              $value = $row->column->value;
              $row_data->$key = $value;
            }
            $added++;
            $data[$index->key] = $row_data;
          }
        }
        if ($added == $expected)
        {
          $more = true;
          echo "added: $added\n";
          if (count($indexes))
          {
            $key = $indexes[count($indexes) - 1]->key;
            $indexClause->start_key = $key;
          }
        }
        else
        {
          if ($added != 0)
            $more = true;
          echo "only added $added\n";
          if (count($indexes))
          {
            $key = $indexes[count($indexes) - 1]->key;
            $indexClause->start_key = $key;
          }
        }
      }
      catch (Exception $e)
      {
        throw new Exception($e->why);
      }
      $expected = $count - 1;
    }
    while ($more);

    return $data;
  }

  function read_with_indexes($keyspace, $column_family, $indexes, $columns=null, $range = null, $consistency)
  {
    Loader::load("vendor", "phpcassa/columnfamily.php");

    $expressions = array();
    foreach ($indexes as $filter)
    {
      $expressions[] = CassandraUtil::create_index_expression($filter["column"], $filter["value"], !isset($filter["comparison"]) ? cassandra_IndexOperator::EQ : $filter["comparison"]);
    }

    $columnfamily = new columnfamily($this->connection($keyspace),
                    $column_family,
                    true,
                    true,
                    $consistency,
                    $consistency,
                    500);
    if (isset($range) && is_array($range))
    {
      if ($range["start"] != 0)
      {
        throw new Exception("Sorry, only ranges starting at 0 are currently supported");
      }

      $clause = CassandraUtil::create_index_clause($expressions, $range["start"], $range["length"]);
    }
    else
      $clause = CassandraUtil::create_index_clause($expressions, "", 1000000); //only the first million objects?

    $iterator = $columnfamily->get_indexed_slices($clause, $columns, "", "", false, 500, null, $consistency);

    $iterator->rewind();
    $data = array();
    while ($iterator->valid())
    {
      $key = $iterator->key();
      $value = $iterator->current();
      $data[$key] = $value;

      $iterator->next();
    }
    return $data;
  }

  function read($keyspace, $key, $column_family, $consistency)
  {

    $this->set_keyspace($this->keyspace());
    $start = microtime(true);
    try
    {
      $indexes = $this->client->multiget_slice(
                      array($key), new cassandra_ColumnParent(array("column_family" => $column_family)), new cassandra_SlicePredicate(array("slice_range" => new cassandra_SliceRange(array("start" => "", "finish" => "", "count" => 100)))), $consistency);
    }
    catch (Exception $e)
    {
      $info = isset($e->why) ? $e->why : get_class($e);
      throw new Exception("Database connection is not available: $info {$this->host}");
      return false;
    }
    $end = microtime(true);
    Debugger::query("Thrift::multiget_slice...", $end - $start, $this->keyspace());
    $data = new stdclass();
    foreach ($indexes as $uuid => $column)
    {
      foreach ($column as $row)
      {
        $key = $row->column->name;
        $value = $row->column->value;
        $data->$key = $value;
      }
    }
    return $data;
  }

  /*
    batch_mutate

    batch_mutate(mutation_map, consistency_level)
    Executes the specified mutations on the keyspace. mutation_map is a map<string, map<string, list<Mutation>>>; the outer map maps the key to the inner map, which maps the column family to the Mutation; can be read as: map<key : string, map<column_family : string, list<Mutation>>>. To be more specific, the outer map key is a row key, the inner map key is the column family name.

    A Mutation specifies either columns to insert or columns to delete. See Mutation and Deletion above for more details.
   */

  function write($keyspace, $key, $column_family, $data, $consistency)
  {
    try
    {
      Loader::load("vendor", "phpcassa/columnfamily.php");
      $columnFamily = new columnFamily($this->connection($keyspace), $column_family);

      //echo "use phpcassa!\n";
      //exit;

      if (Config::isLive() || Config::isStaging())
        return $columnFamily->insert($key, $data, null, null, $consistency);

      $time = $this->microsecond_timestamp();

      $cassandra_mutations = array();
      foreach ($data as $key => $value)
      {
        $cassandra_mutations[$key][$column_family][] = new cassandra_Mutation(array(
                    "column_or_supercolumn" => new cassandra_ColumnOrSuperColumn(array(
                        "column" => new cassandra_Column(array(
                            "name" => $key,
                            "value" => $value,
                            "timestamp" => $time,
                        ))))));
      }
      //Debugger::log("set keyspace $keyspace");
      $this->set_keyspace($keyspace);
      $start = microtime(true);
      $this->get_client()->batch_mutate($cassandra_mutations, $this->consistency());
      $end = microtime(true);
      if (json_encode($cassandra_mutations) == null)
      {
        print_r($cassandra_mutations);
        exit;
      }
      //error_log("Thrift::batch_mutate(".json_encode($cassandra_mutations).") " . "(".$this->host .":".$this->settings["port"].")");
      Debugger::query("Thrift::batch_mutate(" . json_encode($cassandra_mutations) . ")", $end - $start, $keyspace . "(" . $this->settings["ip"] . ":" . $this->settings["port"] . ")");
      return true;
    }
    catch (Exception $e)
    {
      $info = isset($e->why) ? $e->why : get_class($e);
      throw new Exception("Database connection is not available: $info {$this->host}");
      return false;
    }
  }

  private function microsecond_timestamp()
  {
    list($frag, $seconds) = explode(' ', microtime());
    return $seconds . substr($frag, 2, 6);
  }

  function getColumnFamilyDefinition($keyspace_name, $column_family)
  {
    echo "getColumnFamilyDefintion($keyspace_name,$column_family)\n";
    $keyspaces = $this->get_client()->describe_keyspaces();
    ///print_r($keyspaces);
    foreach ($keyspaces as $keyspace)
    {
      if ($keyspace->name == $keyspace_name)
      {
        foreach ($keyspace->cf_defs as $columnFamilyDefinition)
        {
          if ($columnFamilyDefinition->name == $column_family)
          {
            echo "<pre>" . print_r($columnFamilyDefinition, true) . "</pre>";
            return $columnFamilyDefinition;
          }
        }
      }
    }
    throw new Exception("$column_family not found in $keyspace_name! ({$this->host})");
  }

  /*
    remove

    remove(key, column_path, timestamp, consistency_level)
    Remove data from the row specified by key at the granularity specified by column_path, and the given timestamp. Note that all the values in column_path besides column_path.column_family are truly optional: you can remove the entire row by just specifying the ColumnFamily, or you can remove a SuperColumn or a single Column by specifying those levels too. Note that the timestamp is needed, so that if the commands are replayed in a different order on different nodes, the same result is produced.
   */

  /*
    describe_keyspaces

    set<string> describe_keyspaces()
    Gets a list of all the keyspaces configured for the cluster.
   */

  /*
    describe_cluster_name

    string describe_cluster_name()
    Gets the name of the cluster.
   */

  /*
    describe_version

    string describe_version()
    Gets the Thrift API version.

   */

  /*
    describe_ring

    list<TokenRange> describe_ring(keyspace)
    Gets the token ring; a map of ranges to host addresses. Represented as a set of TokenRange instead of a map from range to list of endpoints, because you can't use Thrift structs as map keys: https://issues.apache.org/jira/browse/THRIFT-162 for the same reason, we can't return a set here, even though order is neither important nor predictable.
   */

  /*
    describe_keyspace

    map<string, map<string, string>> describe_keyspace(keyspace)
    Gets information about the specified keyspace.
   */
}

?>