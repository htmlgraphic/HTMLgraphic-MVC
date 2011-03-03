<?php
//thift, you suck
Loader::load("vendor","thrift/Thrift");

$socket = new TSocket('127.0.0.1',9160);
$transport = new TBufferedTransport($socket, 1024, 1024);
$protocol = new TBinaryProtocol($transport);
$client = new CassandraClient($protocol);
$transport->open();

$keyspace = "Articles";

$keyUserId = "1";


$timestamp = time();
$columnPath = new cassandra_ColumnPath();
$columnPath->column_family = 'Standard1';
$columnPath->super_column = null;

// We want the consistency level to be ZERO which means async operations on 1 node
$consistency_level = cassandra_ConsistencyLevel::ZERO;

$columnPath->column = 'ID';
$client->insert($keyspace, $keyUserId, $columnPath, 1, $timestamp, $consistency_level);

$columnPath->column = 'Title';
$client->insert($keyspace, $keyUserId, $columnPath, "My Super Awesome Article", $timestamp, $consistency_level);

$columnPath->column = 'Body';
$client->insert($keyspace, $keyUserId, $columnPath, "This<br />is<br />the<br />greatest<br />article<br />EVAR!", $timestamp, $consistency_level);


$columnParent = new cassandra_ColumnParent();
$columnParent->column_family = "Standard1";
$columnParent->super_column = NULL;

$sliceRange = new cassandra_SliceRange();
$sliceRange->start = "";
$sliceRange->finish = "";
$predicate = new cassandra_SlicePredicate();
list() = $predicate->column_names;
$predicate->slice_range = $sliceRange;

$consistency_level = cassandra_ConsistencyLevel::ONE;

$keyUserId = 1;
$result = $client->get_slice($keyspace, $keyUserId, $columnParent, $predicate, $consistency_level);

print_r($result);
$transport->close();
?>
