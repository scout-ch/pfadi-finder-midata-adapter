<?php

include './database.php';


function ensureColumnExists($connection,$table, $column, $column_definition) {
  $query = <<<CHECK
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = "$table"
    AND COLUMN_NAME = "$column";
  CHECK;

  $table_exists = boolval($connection->query($query)->fetch_row()[0]);
  if(!$table_exists) {
    $query = 'ALTER TABLE `' . $table . '` ADD ' . $column . " " . $column_definition . ";";
    $connection->query($query);
  }
}

function addUpdatedAt($connection) {
  ensureColumnExists($connection, "divisions", "updated_at", "DATETIME NULL");
}

function addPbsId($connection) {
  ensureColumnExists($connection, "divisions", "pbs_id", "INT(11) UNSIGNED");
  ensureColumnExists($connection, "locations", "pbs_id", "INT(11) UNSIGNED");
}

function addDescription($connection) {
  ensureColumnExists($connection, "divisions", "description", "VARCHAR(255) NULL");
}

function addDescription($connection) {
  $connection->query("ALTER TABLE `divisions` ADD description VARCHAR(255) NULL;");
}

addUpdatedAt(connect($config));
addPbsId(connect($config));
addDescription(connect($config));
