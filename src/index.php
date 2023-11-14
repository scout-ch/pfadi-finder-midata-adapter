<?php

/**
 * Index groups: Checks MiData for new groups to add. Creates empty entries for new groups. 
 * Other data is collected in division.php. This file can be called by a cronjob once a day in
 * order to have a clean index before division.php updates all the groups over the night.
 *
 * Removing groups from the index only happens for hard deleted groups. Soft deleted groups are
 * removed by the division.php file
 */

include './database.php';

function removeDivisions($ids, $connection) {
  $stmt_loc = $connection->prepare("DELETE FROM `locations` WHERE pbs_id = ?");
  $stmt_div = $connection->prepare("DELETE FROM `divisions` WHERE pbs_id = ?");

  foreach($ids as $id) {
    $stmt_loc->bind_param("s", $id); 
    $stmt_loc->execute();
    $stmt_loc->reset();
    $stmt_div->bind_param("s", $id); 
    $stmt_div->execute();
    $stmt_div->reset();
  }
}

function insertDivisions($ids, $connection) {
  $stmt = $connection->prepare("INSERT INTO `divisions` (`pbs_id`, `code`, `name`, `cantonalassociation`, `gender`, `pta`, `mainpostalcode`, `allpostalcodes`) 
                                VALUES (?, ?, '', '', 0, 0, '', '')");

  foreach($ids as $id) {
    $stmt->bind_param("ds", $id, $id);
    
    if(!$stmt->execute()) error_log($connection->error);
    $stmt->reset();
  }
}

function fetchIndex($config) {
  $query = $config['TOKEN'] ? "?token=" . $config['TOKEN'] : "";
  $url = $config['BASE_URL'] . "/de/list_groups.json" . $query;
  error_log("Requesting division index at $url", 0);
  $data = file_get_contents($url);

  if($data) return json_decode($data, true);
}

function existingDivisionsCodes($connection) {
  return array_flatten($connection->query("SELECT `pbs_id` FROM `divisions`;")->fetch_all(MYSQLI_NUM));
}

function array_flatten($items) {
    if (! is_array($items)) {
        return [$items];
    }

    return array_reduce($items, function ($carry, $item) {
        return array_merge($carry, array_flatten($item));
    }, []);
}

function processIndex($config, $connection) {
  $existingDivisionCodes = existingDivisionsCodes($connection);
  $divisionIndex = fetchIndex($config);
  $divisionCodes = [];

  foreach($divisionIndex['groups'] as $divisionListing) {
    if ($divisionListing['type'] == 'Group::Abteilung') $divisionCodes[] = $divisionListing['id'];
  }

  $divisionsToRemove = array_diff($existingDivisionCodes, $divisionCodes);
  $divisionsToAdd = array_diff($divisionCodes, $existingDivisionCodes);
  $divisionsToAdd = array_diff($divisionsToAdd, $config['EXCLUDED_GROUPS']);
  removeDivisions($divisionsToRemove, $connection);
  insertDivisions($divisionsToAdd, $connection);

  return ['removed' => $divisionsToRemove, 'added' => $divisionsToAdd];
}


header('Content-Type: application/json; charset=UTF-8');
print(json_encode(processIndex($config, connect($config))));
