<?php

/**
 * /api/post/index.php
 *
 * @name fab-analytics.js api endpoint
 * @descrtipion Injests data from analytics and writes to /logs/{domain}/{identifier}.json
 * @package fab-analytics
 * @author Hustle Launch <success@hustlelaunch.com>
 * @license GPL
 * @version 0.1.3-rc
 * @url https://www.hustlelaunch.com
 * @maintainer @michaelmonetized
 */
$now = trim(microtime());
$plugin_path = str_replace("/api/post/visit/", '', __DIR__);

// get json from phpinput
$json = file_get_contents('php://input');

// decode json
$data = json_decode($json);

if (json_last_error() !== JSON_ERROR_NONE) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  echo json_encode(['error' => 'Invalid JSON']);
  die();
}

if (!isValidData($data)) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  echo json_encode(['error' => 'Invalid data']);
  die();
}

// get domain
$domain = $data->domain;

// get token
$token = $data->session_token;

// get identifier
$identifier = "{$token}-{$now}";

// get json file
$path = "{$plugin_path}/logs/{$domain}";
$file = "{$path}/{$identifier}.json";

if (!is_dir($path)) {
  mkdir($path, 0755, true);
}

// write json to file
if (!(file_put_contents($file, $json))) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  echo json_encode(['error' => 'Unable to write to file']);
  die();
}

// if the domain contains oxstu as a substring then include ../send/index.php
if (strpos($domain, 'oxstu') !== false && $data->category === 'form') {
  include '../send/index.php';
}

function isValidData($data)
{
  return isset($data->domain) && isset($data->session_token);
}

$contents = file_get_contents($file);

if ($contents === false) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  echo json_encode([
    "error" => "Unable to read file $file"
  ]);

  die();
}

// return json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
  "file" => $file,
  "contents" => file_get_contents($file),
  "data" => $data,
  "plugin" => $plugin_path,
]);
