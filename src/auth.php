<?php

/**
 * Basic auth, checks if the user is authenticated
 */


if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="pfadi-finder-midata-adapter"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Authorization required';
  exit;
}
