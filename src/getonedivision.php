<?php

/**
 * Returns the group in JSON format
 * This can be used by the pfadifinder to fetch all data about a specific group. Use this with 
 * a GET-Parameter for the group id, e.g. https://url.ch/src/getonedivision.php?code=375
 */

include './database.php';
include './divisionhelper.php';

function getDivisionByCode($connection, $code) {
  if(!$code) return false;

  $stmt = $connection->prepare("SELECT * FROM divisions WHERE code LIKE ? LIMIT 1");
  $stmt->bind_param('s', $code);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows <= 0) return [];
  $divisions = $result->fetch_all(MYSQLI_ASSOC);
  
  foreach($divisions as &$div){     
    $div["gender"] = intval($div["gender"]);
    $div["pta"] = boolval($div["pta"]);
    unset($div["pbs_id"]);
    unset($div["updated_at"]);
    $div["locations"] = getLocations($connection, $div["code"]);
    $div["social_accounts"] = getSocialAccounts($connection, $div["code"]);
  }
  
  return $divisions;
}

function getCodeFromURL() {
  if (isset($_GET['code'])) {

	  // Check if input is secure
    $match = preg_match('/^[a-zA-Z]{0,3}\d{1,5}$/', $_GET['code']);
    if($match === 1) return $_GET['code'];
  }	
}

$connection = connect($config);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(getDivisionByCode($connection, getCodeFromURL()));
?>
