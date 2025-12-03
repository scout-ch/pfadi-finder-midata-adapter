<?php

/**
 * Basic auth, checks if the user is authenticated
 */


if (!isset($_SERVER['PHP_AUTH_USER']) or !isset($_SERVER['PHP_AUTH_PW'])) {
  header('WWW-Authenticate: Basic realm="pfadi-finder-midata-adapter"');
  header('HTTP/1.0 401 Unauthorized');
  print("Authentication required.");
  exit;
}

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

if ($user !== $config['AUTH_USER'] || $pass !== $config['AUTH_PASSWORD']) {
  header('HTTP/1.0 401 Unauthorized');
  die("Not authorized");
}
