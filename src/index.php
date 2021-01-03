<?php
/*
+---------------------+--------------+------+-----+
| Field               | Type         | Null | Key |
+---------------------+--------------+------+-----+
| code                | varchar(255) | NO   | PRI |
| name                | varchar(255) | NO   |     |
| cantonalassociation | varchar(255) | NO   |     |
| mainpostalcode      | varchar(255) | NO   |     |
| allpostalcodes      | varchar(255) | NO   |     |
| gender              | int(11)      | NO   |     |
| pta                 | tinyint(1)   | NO   |     |
| website             | varchar(255) | YES  |     |
| agegroups           | varchar(255) | YES  |     |
| email               | varchar(255) | YES  |     |
+---------------------+--------------+------+-----+
*/

include './config.php';

class PfadiFinderAdapter {
  private $config;
  private $connection;

  function __construct($config) {
    $this->config = $config;
    $this->connection = $this->connect();
  }

  function connect() {
    return mysqli_connect($this->config['DATABASE_HOST'],
                          $this->config['DATABASE_USER'],
                          $this->config['DATABASE_PASSWORD'],
                          $this->config['DATABASE_DB']);
  }

  function insertDivision($division) {
    $stmt = $this->connection->prepare("INSERT INTO `divisions` (code, name, cantonalassociation, gender, pta, website, agegroups, email, mainpostalcode, allpostalcodes) 
                                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, '')");
    $stmt->bind_param("sssiisss", $division['id'], $division['name'], $division['kv'], $division['genders'], $division['pta'], $division['website'], $division['agegroups'], $division['email']);
    $stmt->execute();
  }

  function insertLocations($division) {
    $stmt = $this->connection->prepare("INSERT INTO `locations` (code, latitude, longitude) VALUES (?, ?, ?)");

    foreach($division['locations'] as $location) {
      $stmt->bind_param("sdd", $division['id'], $location['lat'], $location['long']);
      $stmt->execute();
      $stmt->reset();
    }
  }

  function clearDivision($division) {
    if($stmt = $this->connection->prepare("DELETE FROM `locations` WHERE code = ?")){
      $stmt->bind_param("s", $division['id']);
      $stmt->execute();
    }else{
      print_r($this->connection->error);
    }

    if($stmt = $this->connection->prepare("DELETE FROM `divisions` WHERE code = ?")){
      $stmt->bind_param("s", $division['id']);
      $stmt->execute();
    }else{
      print_r($this->connection->error);
    }
  }

  function processDivision($division) {
    if ($division == null) return;

    if ($this->clearDivision($division) && 
      $this->insertDivision($division) &&
      $this->insertLocations($division)) {
        return [
          'id' => $division['id'],
          'data' => $division,
          'ok' => true
        ];
    }

    return [
      'id' => $division['id'],
      'data' => $division,
      'ok' => false
    ];
  }
}

class MidataAdapter {
  private $config;

  function __construct($config) {
    $this->config = $config;
  }

  function fetch($id) {
    $token = $this->config['TOKEN'] ? "?token=" . $this->config['TOKEN'] : "";
    $url = $this->config['BASE_URL'] . "/de/groups/$id.json" . $token;
    $data = file_get_contents($url);

    if($data) return json_decode($data, true);
  }

  function fetchIndex() {
    $token = $this->config['TOKEN'] ? "?token=" . $this->config['TOKEN'] : "";
    $url = $this->config['BASE_URL'] . "/de/list_groups.json" . $token;
    $data = file_get_contents($url);

    if($data) return json_decode($data, true);
  }

  function transform($data) {
    $div = $data['groups'][0];

    $divArray = [
      'id' => $div['pbs_shortname'],
      'name' => $div['name'],
      'kv' => substr($div['pbs_shortname'], 0, 2),
      'genders' => $this->mapGenders($div),
      'pta' => !!$div['pta'],
      'website' => $div['website'],
      'email' => $div['email'],
      'agegroups' => $this->mapAgeGroups($div),
      'locations' => $data['linked']['geolocations']
    ];

    return $divArray;
  }

  function fetchAll() {
    $divisions = [];
    $divisionIndex = $this->fetchIndex();
    foreach($divisionIndex['groups'] as $divisionListing) {
      if ($divisionListing['type'] != 'Group::Abteilung') continue;

      $divisions[] = $this->transform($this->fetch($divisionListing['id']));
    }

    return $divisions;
  }

  function mapAgeGroups($data) {
    return "";
  }

  function mapGenders($data) {
    if ($data['gender'] == 'w') return 1;
    if ($data['gender'] == 'm') return 2;
    return 3;
  }
}

$midataAdapter = new MidataAdapter($config);
$pfadiFinderAdapter = new PfadiFinderAdapter($config);
$result = [];

$divisions = $midataAdapter->fetchAll();
foreach($divisions as $division) {
  $result[] = $pfadiFinderAdapter->processDivision($division);
}

header('Content-Type: application/json; charset=UTF-8');
print(json_encode($result));
