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

function getSocialAccounts($connection, $code) {
  $stmt = $connection->prepare("SELECT * FROM social_accounts WHERE code = ?");
  $stmt->bind_param('s', $code);
  $stmt->execute();
  $result = $stmt->get_result();
  $social_accounts = $result->fetch_all(MYSQLI_ASSOC);

  foreach($social_accounts as &$social_account){
    unset($social_account["id"]);
    unset($social_account["code"]);
  }

  return $social_accounts;
}
