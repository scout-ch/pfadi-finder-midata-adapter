<?php

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

?>