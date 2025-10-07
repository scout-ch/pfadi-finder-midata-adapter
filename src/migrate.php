<?php

include './database.php';

function addUpdatedAt($connection) {
  $connection->query("ALTER TABLE `divisions` ADD updated_at DATETIME NULL;");
}

function addPbsId($connection) {
  $connection->query("ALTER TABLE `divisions` ADD pbs_id INT(11) UNSIGNED;");
  $connection->query("ALTER TABLE `locations` ADD pbs_id INT(11) UNSIGNED;");
}

function addDescription($connection) {
  $connection->query("ALTER TABLE `divisions` ADD description VARCHAR(255) NULL;");
}

addUpdatedAt(connect($config));
addPbsId(connect($config));
addDescription(connect($config));
