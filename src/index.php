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
      $stmt->bind_param("sdd", $division['id'], $location['latitude'], $location['longitude']);
      $stmt->execute();
      $stmt->reset();
    }
  }

  function clearDivision($division) {
    $stmt = $this->connection->prepare("DELETE FROM `locations` WHERE code = ?");
    $stmt->bind_param("s", $division['id']); 
    $stmt->execute();

    $stmt = $this->connection->prepare("DELETE FROM `divisions` WHERE code = ?");
    $stmt->bind_param("s", $division['id']); 
    $stmt->execute();
  }

  function processDivision($division) {
    if ($division == null) return;

    $this->clearDivision($division);
    $this->insertDivision($division);
    $this->insertLocations($division);
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

    if($data) return json_decode($data, true)['groups'][0];
  }

  function fetchIndex() {
    $token = $this->config['TOKEN'] ? "?token=" . $this->config['TOKEN'] : "";
    $url = $this->config['BASE_URL'] . "/de/list_groups.json" . $token;
    $data = file_get_contents($url);

    if($data) return json_decode($data, true)['groups'];
  }

  function transform($data) {
    return [
      'id' => $data['pbs_shortname'], 'name' => $data['name'], 'kv' => substr($data['pbs_shortname'], 0, 2), 'genders' => $this->mapGenders($data), 
      'pta' => !!$data['pta'], 'website' => $data['website'], 'email' => $data['email'], 'agegroups' => $this->mapAgeGroups($data), 'locations' => $this->mapLocations($data)
    ];
  }

  function fetchAll() {
    $divisions = [];
    $divisionIndex = $this->fetchIndex();
    foreach($divisionIndex as $divisionListing) {
      if ($divisionListing['type'] != 'Group::Abteilung') continue;

      $divisions[] = $this->transform($this->fetch($divisionListing['id']));
    }

    return $divisions;
  }

  function mapAgeGroups($data) {
    return "";
  }

  function mapLocations($data) {
    return [];
  }

  function mapGenders($data) {
    if ($data['gender'] == 'w') return 1;
    if ($data['gender'] == 'm') return 2;
    return 3;
  }
}


$midataAdapter = new MidataAdapter($config);
$pfadiFinderAdapter = new PfadiFinderAdapter($config);

$divisions = $midataAdapter->fetchAll();
foreach($divisions as $division) {
  $pfadiFinderAdapter->processDivision($division);
}
