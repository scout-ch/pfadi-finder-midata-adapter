<?php

function getLocations($connection, $code) {
  $stmt = $connection->prepare("SELECT * FROM locations WHERE code = ?");
  $stmt->bind_param('s', $code);
  $stmt->execute();
  $result = $stmt->get_result();
  $locations = $result->fetch_all(MYSQLI_ASSOC);
  
  foreach($locations as &$loc){   
    $loc["id"] = intval($loc["id"]);
    $loc["latitude"] = number_format($loc["latitude"],6,".","");
    $loc["longitude"] = number_format($loc["longitude"],6,".","");
    unset($loc["pbs_id"]);
  }
  
  return $locations;
}
