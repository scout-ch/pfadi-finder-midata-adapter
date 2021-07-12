<?php
include './database.php';

function updateDivision($division, $connection) {
  $stmt = $connection->prepare("UPDATE `divisions` SET 
                                `name` = ?, `cantonalassociation` = ?, `gender` = ?, `pta` = ?, 
                                `website` = ?, `agegroups` = ?, `email` = ?, `code` = ?, `updated_at` = NOW()
                                WHERE pbs_id = ?;");

  $stmt->bind_param("ssiissssd", $division['name'], $division['kv'], $division['genders'], $division['pta'], 
                               $division['website'], $division['agegroups'], $division['email'], $division['code'],
                               $division['id'], );
                    

  return $stmt->execute();
}

function insertLocations($division, $connection) {
  $stmt = $connection->prepare("DELETE FROM `divisions` WHERE `pbs_id` = ?");
  $stmt->bind_param("s", $division['id']); 
  $stmt->execute();

  $stmt = $connection->prepare("INSERT INTO `locations` (`pbs_id`, `code`, `latitude`, `longitude`) VALUES (?, ?, ?)");

  foreach($division['locations'] as $location) {
    $stmt->bind_param("dsdd", $division['id'], $division['code'], $location['lat'], $location['long']);
    $stmt->execute();
    $stmt->reset();
  }
}

function processDivision($id, $config, $connection) {
  if($id == null || $id == '') return false;

  $division = fetchDivision($id, $config);
  $division = transformDivisionData($division);

  return [
    'id' => $id,
    'data' => $division,
    'ok' => updateDivision($division, $connection) == true,
    'error' => $connection->error
  ];
}

function fetchDivision($id, $config) {
  $query = $config['TOKEN'] ? "?token=" . $config['TOKEN'] : "";
  $url = $config['BASE_URL'] . "/de/groups/$id.json" . $query;
  error_log("Requesting division $id at $url", 0);
  $data = file_get_contents($url);

  if($data) return json_decode($data, true);
}

function transformDivisionData($data) {
  $division = $data['groups'][0];

  return [
    'code' => $division['id'], 'name' => $division['name'], 'kv' => substr($division['pbs_shortname'], 0, 2), 
    'genders' => mapGenders($division), 'pta' => !!$division['pta'], 'website' => $division['website'], 
    'email' => $division['email'], 'agegroups' => mapAgeGroups($data['linked']['groups']), 
    'locations' => $data['linked']['geolocations'], 'id' => intval($division['id'])
  ];
}


function mapAgeGroups($groups) {
  if(!$groups) return '';

  $ageGroups = array_map(function ($group) { return ['Biber' => 0, 'Wölfe' => 1, 'Pfadi' => 2, 'Pio' => 3, 'Rover' => 4][$group['group_type']]; }, $groups);
  $ageGroups = array_filter($ageGroups, function ($ageGroup) { return $ageGroup !== null; });
  $ageGroups = array_unique($ageGroups);
  return join(', ', $ageGroups);
}

function mapGenders($data) {
  if ($data['gender'] == 'w') return 1;
  if ($data['gender'] == 'm') return 2;
  return 3;
}

function selectDivision($connection, $minage) {
  return intval($connection->query("SELECT `pbs_id` FROM `divisions` 
                                    WHERE `updated_at` IS NULL
                                    OR `updated_at` < DATE_SUB(NOW(), INTERVAL $minage HOUR) 
                                    ORDER BY RAND() LIMIT 1;")->fetch_row()[0]) ;
}

header('Content-Type: application/json; charset=UTF-8');

$connection = connect($config);
$id = selectDivision($connection, $config['MINAGE'] ?? 24);

print(json_encode(processDivision($id, $config, $connection)));
