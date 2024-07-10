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

// get json from phpinput
$json = file_get_contents('php://input');

// decode json
$data = json_decode($json);

// get domain
$domain = $data->domain;

// get identifier
$identifier = base64_encode($data->session_token . '-' . microtime());

// get json file
$path = str_replace('/api/post/visit/', '', __DIR__) . '/logs/' . $domain;
$json_file = "{$path}/{$identifier}.json";

if (!file_exists($path)) {
  mkdir($path, 0777, true);
}

// write json to file
file_put_contents($json_file, $json);

// if the domain contains oxstu as a substring then include ../send/index.php
if (strpos($domain, 'oxstu') !== false && $data->category === 'form') {
  include '../send/index.php';
}

// return json
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode($data);
