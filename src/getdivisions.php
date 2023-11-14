<?php

/**
 * Returns the list of all groups in JSON format.
 * Doesn't include any social accounts.
 */

include './database.php';
include './divisionhelper.php';

function getDivisions($connection) {
  $sql = "SELECT * FROM divisions";
  $result = $connection->query($sql);

  if ($result->num_rows > 0) {
    $divisions = $result->fetch_all(MYSQLI_ASSOC);
    
    foreach($divisions as &$div){     
      $div["gender"] = intval($div["gender"]);
      $div["pta"] = boolval($div["pta"]);
      unset($div["pbs_id"]);
      unset($div["updated_at"]);
      $div["locations"] = getLocations($connection, $div["code"]);
    }
    
    return json_encode($divisions);
  } else {
    return "no results found";
  }
}

$connection = connect($config);
header('Content-Type: application/json; charset=UTF-8');
echo getDivisions($connection);
?>
