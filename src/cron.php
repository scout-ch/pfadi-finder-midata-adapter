<?php

include './config.php';

class MidataAdapter {
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

  function fetchFromMidata($id) {
    $token = $this->config['TOKEN'] ? "?token=" . $this->config['TOKEN'] : "";
    $url = $this->config['BASE_URL'] . "/de/groups/$id.json" . $token;
    $data = file_get_contents($url);

    if($data) return json_decode($data, true);
  }

  function fetchIndexFromMidata() {
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

  function fetchAllFromMidata() {
    $divisions = [];
    $divisionIndex = $this->fetchIndexFromMidata();
    foreach($divisionIndex['groups'] as $divisionListing) {
      if ($divisionListing['type'] != 'Group::Abteilung') continue;

      $divisions[] = $this->transform($this->fetchFromMidata($divisionListing['id']));
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

  function insertDivisionIntoDB($division) {
    $stmt = $this->connection->prepare("INSERT INTO `divisions` (code, name, cantonalassociation, gender, pta, website, agegroups, email, mainpostalcode, allpostalcodes) 
                                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, '');");
    $stmt->bind_param("sssiisss", $division['id'], $division['name'], $division['kv'], $division['genders'], $division['pta'], $division['website'], $division['agegroups'], $division['email']);
    $stmt->execute();
    $stmt->close();
  }

  function insertLocationsIntoDB($division) {
    $stmt = $this->connection->prepare("INSERT INTO `locations` (code, latitude, longitude) VALUES (?, ?, ?);");

    foreach($division['locations'] as $location) {
      $stmt->bind_param("sdd", $division['id'], $location['lat'], $location['long']);
      $stmt->execute();
      $stmt->reset();
    }
  }

  function checkForDevision($division){
    $stmt = $this->connection->prepare("SELECT COUNT(*) AS exist FROM divisions WHERE code = ?;");
    $stmt->bind_param("s", $division['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $exist = $result->fetch_row();

    if($exist[0] > 0){
      return true;
    }else{
      return false;
    }
  }

  function clearDivisionFromDB($division) {
    if($stmt = $this->connection->prepare("DELETE FROM `locations` WHERE code = ?;")){
      $stmt->bind_param("s", $division['id']);
      $stmt->execute();
    }else{
      print_r($this->connection->error);
    }

    if($stmt = $this->connection->prepare("DELETE FROM `divisions` WHERE code = ?;")){
      $stmt->bind_param("s", $division['id']);
      $stmt->execute();
    }else{
      print_r($this->connection->error);
    }
  }

  function processDivision($division) {
    if ($division == null) return;

    if($this->checkForDevision($division) == true){
      $this->clearDivisionFromDB($division);
    }

    $this->insertDivisionIntoDB($division);
    $this->insertLocationsIntoDB($division);

    return [
      'id' => $division['id'],
    ];
  }
}

$midataAdapter = new MidataAdapter($config);

$divisions = $midataAdapter->fetchAllFromMidata();
$result = [];
foreach($divisions as $division) {
    $result[] = $midataAdapter->processDivision($division);
}

$count = count($result);
$status = ["status" => "Processed $count entries"];

header('Content-Type: application/json; charset=UTF-8');
print(json_encode($status));