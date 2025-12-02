<?php
/*
 * Finds the group that has not been updated for the longest time in the database. Updates all the data about that 
 * group, including locations and social accounts.
*/

include './division.php';

header('Content-Type: application/json; charset=UTF-8');

$connection = connect($config);
$id = selectDivision($connection, $config['MINAGE'] ?? 24);

print(json_encode(processDivision($id, $config, $connection)));
