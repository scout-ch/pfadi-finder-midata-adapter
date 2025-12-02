<?php

/**
 * Creates database tables if they do not exist yet
 */

include './database.php';

$sqlDivisions = "CREATE TABLE IF NOT EXISTS divisions (
    code VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255),
    cantonalassociation VARCHAR(255),
    mainpostalcode VARCHAR(255),
    allpostalcodes VARCHAR(255),
    gender INT(11),
    pta TINYINT(1),
    website VARCHAR(255),
    agegroups VARCHAR(255),
    email VARCHAR(255),
    updated_at DATETIME,
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// SQL for locations table
$sqlLocations = "CREATE TABLE IF NOT EXISTS locations (
    id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255),
    latitude DOUBLE,
    longitude DOUBLE,
    pbs_id INT(11)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// SQL for social_accounts table
$sqlSocialAccounts = "CREATE TABLE IF NOT EXISTS social_accounts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30),
    url VARCHAR(3000),
    type VARCHAR(3000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

function ensureTable($table_query, $table_name, $connection)
{
  if ($connection->query($table_query) == TRUE) {
    print "Table '" . $table_name . "' exists/was created.\n";
  } else {
    print "Error creating '" . $table_name .  "': " . $connection->error .  "\n";
  }
}

ensureTable($sqlDivisions, "divisions", connect($config));
ensureTable($sqlLocations, "locations", connect($config));
ensureTable($sqlSocialAccounts, "socialAccounts", connect($config));
