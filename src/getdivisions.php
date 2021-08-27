<?php
include './database.php';

function getLocations($connection, $divisionCode) {
  $sql_loc = "SELECT * FROM locations WHERE code = '" . $divisionCode . "'";
  $result_loc = $connection->query($sql_loc);
  $locations = $result_loc->fetch_all(MYSQLI_ASSOC);
  
  foreach($locations as &$loc){   
    $loc["id"] = intval($loc["id"]);
    $loc["latitude"] = number_format($loc["latitude"],6,".","");
    $loc["longitude"] = number_format($loc["longitude"],6,".","");
    unset($loc["pbs_id"]);
  }
  
  return $locations;
}

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
