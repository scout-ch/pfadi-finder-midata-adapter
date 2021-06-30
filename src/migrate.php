<?php

include './database.php';

function addUpdatedAt($connection) {
  $connection->query("ALTER TABLE `divisions` ADD updated_at DATETIME NULL;");
}

function addPbsId($connection) {
  $connection->query("ALTER TABLE `divisions` ADD pbs_id INT(11) UNSIGNED;");
  $connection->query("ALTER TABLE `locations` ADD pbs_id INT(11) UNSIGNED;");
}

addUpdatedAt(connect($config));
addPbsId(connect($config));
