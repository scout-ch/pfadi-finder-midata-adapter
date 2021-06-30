<?php
/*
+---------------------+--------------+------+-----+
| Field               | Type         | Null | Key |
+---------------------+--------------+------+-----+
| code                | varchar(255) | NO   | PRI |
| name                | varchar(255) | NO   |     |
| cantonalassociation | varchar(255) | NO   |     |
| mainpostalcode      | varchar(255) | NO   |     |
| allpostalcodes      | varchar(255) | NO   |     |
| gender              | int(11)      | NO   |     |
| pta                 | tinyint(1)   | NO   |     |
| website             | varchar(255) | YES  |     |
| agegroups           | varchar(255) | YES  |     |
| email               | varchar(255) | YES  |     |
+---------------------+--------------+------+-----+
*/

include './config.php';

function connect($config) {
  $connection = mysqli_connect($config['DATABASE_HOST'], 
                               $config['DATABASE_USER'], 
                               $config['DATABASE_PASSWORD'], 
                               $config['DATABASE_DB']);
  $connection->set_charset('utf8mb4');
  return $connection;
}
