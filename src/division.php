<?php

/**
 * Finds the group that has not been updated for the longest time in the database. Updates all the data about that 
 * group, including locations and social accounts.
 *
 * This script is called by a cronjob many times a day in order to keep all groups updated. The cronjob pattern can be 
 * something like "* 0-9 * * *" to update 10h * 60min = 600 groups per night. When we update a group, we set the date to 
 * NOW in the 'updated_at' column. This way the processed group always lands at the end of the queue. 
*/

include './database.php';

function updateDivision($division, $connection) {
  // filtering deleted groups doesn't work yet -> needs deleted_at to be visible in hitobito api
  if(isset($division['deleted_at']) && strlen($division['deleted_at']) > 0) {
    $delstmt = $connection->prepare("DELETE FROM `divisions` WHERE pbs_id = ?;");
    $delstmt->bind_param("d", $division['id']);
    return $delstmt->execute();
  }

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
  $stmt = $connection->prepare("DELETE FROM `locations` WHERE `pbs_id` = ?");
  $stmt->bind_param("s", $division['id']);
  $stmt->execute();

  if(isset($division['locations'])) {
    $stmt = $connection->prepare("INSERT INTO `locations` (`pbs_id`, `code`, `latitude`, `longitude`) VALUES (?, ?, ?, ?)");
    foreach($division['locations'] as $location) {
      if(locationWithinSwitzerland($location)) {
        $stmt->bind_param("dsdd", $division['id'], $division['code'], $location['lat'], $location['long']);
        $stmt->execute();
        $stmt->reset();
      }
    }
  }
}

function insertSocialAccounts($division, $connection) {
  $stmt = $connection->prepare("DELETE FROM `social_accounts` WHERE `code` = ?");
  $stmt->bind_param("s", $division['id']);
  $stmt->execute();

  if(isset($division['social_accounts'])) {
    $stmt = $connection->prepare("INSERT INTO `social_accounts` (`code`, `url`, `type`) VALUES (?, ?, ?)");
    foreach($division['social_accounts'] as $social_account) {
      if($social_account['public']) {
        $stmt->bind_param("dss", $division['id'], $social_account['name'], $social_account['label']);
        $stmt->execute();
        $stmt->reset();
      }
    }
  }
}

function locationWithinSwitzerland($location) {
  return GEOLOCATION_SWITZERLAND_SOUTH_LIMIT < floatval($location['lat'])
    && GEOLOCATION_SWITZERLAND_NORTH_LIMIT > floatval($location['lat'])
    && GEOLOCATION_SWITZERLAND_WEST_LIMIT < floatval($location['long'])
    && GEOLOCATION_SWITZERLAND_EAST_LIMIT > floatval($location['long']);
}

function processDivision($id, $config, $connection) {
  if($id == null || $id == '') return false;

  $division = fetchDivision($id, $config);
  $division = transformDivisionData($division);
  insertLocations($division, $connection);
  insertSocialAccounts($division, $connection);
  
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
    'code' => $division['id'], 'name' => $division['name'], 'kv' => mapKV(substr($division['pbs_shortname'], 0, 2)),
    'genders' => mapGenders($division), 'pta' => !!$division['pta'], 'website' => $division['website'], 
    'email' => $division['email'], 'agegroups' => mapAgeGroups($data['linked']['groups']), 
    'locations' => $data['linked']['geolocations'],
    'social_accounts' => $data['linked']['social_accounts'],
    'id' => intval($division['id']),
    'deleted_at' => $division['deleted_at'],
    'description' => $division['description']
  ];
}

function mapKV($shortname_sub) {
  // For ticino there can be shortnames with the three digits "STI" in the data
  if(strcasecmp($shortname_sub, "ST") == 0) {
    return "TI";
  }
  return $shortname_sub;
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
