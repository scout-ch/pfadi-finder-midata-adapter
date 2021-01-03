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

  function loadEntriesFromDB(){
    $stmt = $this->connection->prepare("SELECT * FROM divisions;");
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()){
      $data[] = $row;
    }

    return $data;
  }

  function loadLocationsFromDB($data){
    $stmt = $this->connection->prepare("SELECT * FROM locations WHERE code = ?;");
    $stmt->bind_param("s", $data['code']);
    $stmt->execute();
    $result = $stmt->get_result();

    $locations = [];
    while($row = $result->fetch_assoc()){
      $locations[] = $row;
    }

    return $locations;
  }

  function transform($data, $locations) {
    $formatLocations = [];

    foreach($locations as $location){
      $formatLocations[] = [
        'id' => $location['code'],
        'lat' => $location['latitude'],
        'long' => $location['longitude'],
      ];
    }

    $divArray = [
      'id' => $data['code'],
      'name' => $data['name'],
      'kv' => $data['cantonalassociation'],
      'genders' => $data['gender'],
      'pta' => !!$data['pta'],
      'website' => $data['website'],
      'email' => $data['email'],
      'agegroups' => $data['agegroups'],
      'locations' => $formatLocations,
    ];

    return $divArray;
  }
}

$pfadiFinderAdapter = new PfadiFinderAdapter($config);
$entries = $pfadiFinderAdapter->loadEntriesFromDB();

$container = [];
foreach($entries as $entry){
  $locations = $pfadiFinderAdapter->loadLocationsFromDB($entry);
  $item = $pfadiFinderAdapter->transform($entry, $locations);

  $container[] = [
    'id' => $entry['code'],
    'data' => $item,
    'ok' => true
  ];
}

header('Content-Type: application/json; charset=UTF-8');
print(json_encode($container));
