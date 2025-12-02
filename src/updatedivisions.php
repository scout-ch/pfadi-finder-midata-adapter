<?php

/**
 * Updates all data for all groups found in the table divisions.
 *
 * The script pauses between calls to the midata database to not DOS the server.
 *
 */
include './division.php';

header('Content-Type: application/json; charset=UTF-8');

$connection = connect($config);
$timeout = 0.5; # Seconds between fetch

print(json_encode(processDivisions($config, $connection, $timeout)));
