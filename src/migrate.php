<?php

print("======== Check Database Tables =========\n");

include './createdatabase.php';

print("\n======== Run migrations =========\n");


function ensureColumnExists($connection, $table, $column, $column_definition) {
  $query = <<<CHECK
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = "$table"
    AND COLUMN_NAME = "$column";
  CHECK;

  $table_exists = boolval($connection->query($query)->fetch_row()[0]);
  if ($table_exists) {
    print("Column '" . $column . "' on table '" . $table . "' already exists.\n");
  } else {
    $query = 'ALTER TABLE `' . $table . '` ADD ' . $column . " " . $column_definition . ";";
    if ($connection->query($query) == TRUE) {
      print("Created column '" . $column . "' on table '" . $table . "'.\n");
    } else {
      print("Error creating column '" . $column . "' on table '" . $table . "': " . $connection->error . "\n");
    }
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

function changeType($connection, $table, $column, $newType) {
  $sqlCheck = "
        SELECT COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{$table}'
          AND COLUMN_NAME = '{$column}'
        LIMIT 1";
  $result = $connection->query($sqlCheck);
  if (!$result) {
    throw new Exception("Failed to check column type for {$table}[{$column}]" . ":" . $connection->error);
  }
  $row = $result->fetch_assoc();
  if (!$row) {
    throw new Exception("Column {$column} not found in table {$table}.");
  }
  if (stripos($row['COLUMN_TYPE'], $newType) === true) {
    print("{$table}[{$column}] is already of type {$newType}");
  } else {
    $oldType = $row['COLUMN_TYPE'];
    $sqlAlter = "ALTER TABLE {$table} MODIFY COLUMN {$column} {$newType}";
    if ($connection->query($sqlAlter)) {
      print("Modified table {$table}: {$column} type {$oldType} -> type {$newType}");
    } else {
      print("Table modification failed {$table}, {$column}, type: {$newType}");
    }
  }
}

addUpdatedAt(connect($config));
addPbsId(connect($config));
addDescription(connect($config));
changeType(connect($config), 'divisions', 'description', 'TEXT');

print("\n======== Done =========\n");
