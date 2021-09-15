<?php
include './database.php';
include './divisionhelper.php';

function getDivisionByCode($connection, $code) {
  $sql = "SELECT * FROM divisions WHERE code LIKE '$code' LIMIT 1";
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
    return false;
  }
}

function getCodeFromURL() {
  if (isset($_GET['code'])) {
	// Check if input is secure
    $match = preg_match('/[A-Z]{1,3}\d{1,3}/', $_GET['code']);
    if($match === 1) {
      return $_GET['code'];
	}
  }	
}

$connection = connect($config);
$division = getDivisionByCode($connection, getCodeFromURL());
if($division !== false) {
	header('Content-Type: application/json; charset=UTF-8');
	echo $division;
}
else {
	echo "no results found";
}
?>
