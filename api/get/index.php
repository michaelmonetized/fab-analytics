<?php

/**
 * /api/get/index.php
 *
 * @name fab-analytics.js api endpoint
 * @descrtipion Reads json logs into json arrays based on GET params
 * @package fab-analytics
 * @author Hustle Launch <success@hustlelaunch.com>
 * @license GPL
 * @version 0.1.3-rc
 * @url https://www.hustlelaunch.com
 * @maintainer @michaelmonetized
 */

$plugin_path = str_replace("/api/get", '', __DIR__);

if (isset($_GET)) {
  if (isset($_GET['domain'])) {
    data_for_client($_GET['domain']);
  } else {
    list_all_clients();
  }
}

/**
 * List all domains in the logs directory
 * @returns {array} of domains with children files grouped by nested [Year][Month][Day][event][token] keys
 */
function list_all_clients()
{
  global $plugin_path;

  $domains = [];

  foreach (glob("{$plugin_path}/logs/*") as $domain) {
    $domain = basename($domain);

    $domains[$domain] = [];

    foreach (glob("{$plugin_path}/logs/{$domain}/*.json") as $file) {
      $filename = basename($file); // eg: 4nued5vSLHLcc3Wx-0.61882100 1720604394.json

      $parts = explode("-", $filename);

      $token = $parts[0];
      $microtime = $parts[1]; // need to convert to int timestamp example input: 0.61882100 1720604394 expected output: 1720604394

      $microtimeParts = explode(" ", $microtime);

      $milliseconds = floor(intval($microtimeParts[1] . str_replace("0.", ".", $microtimeParts[0])) * 1000);
      $seconds = intval($microtimeParts[1]);

      $contents = file_get_contents($file);

      $data = json_decode($contents);

      $year = intval(date("Y", $seconds)); // fatal error expects int string given
      $month = intval(date("m", $seconds)); // fatal error expects int string given
      $day = intval(date("d", $seconds)); // fatal error expects int string given

      $event = $data->event;

      $domains[$domain][$year][$month][$day][$event][$token] = $data;
    }
  }

  output_client_data($domains);
}

/**
 * Returns client data
 * @param {string} $domain
 * @returns {array} of domains with children files grouped by nested [Year][Month][Day][event][token] keys
 */
function data_for_client($domain)
{
  global $plugin_path;

  $logs = [];

  foreach (glob("{$plugin_path}/logs/{$domain}/*.json") as $file) {
    $filename = basename($file);

    $parts = explode("-", $filename);

    $token = $parts[0];
    $microtime = $parts[1]; // need to convert to int timestamp example input: 0.61882100 1720604394 expected output: 1720604394

    $microtimeParts = explode(" ", $microtime);

    $milliseconds = floor(intval($microtimeParts[1] . str_replace("0.", ".", $microtimeParts[0])) * 1000);
    $seconds = intval($microtimeParts[1]);

    $contents = file_get_contents($file);

    $data = json_decode($contents);

    $year = intval(date("Y", $seconds));
    $month = intval(date("m", $seconds));
    $day = intval(date("d", $seconds));

    $event = $data->event;

    $logs[$year][$month][$day][$event][$token] = $data;
  }

  output_client_data($logs);
}

/**
 * Outputs client data
 * @param {array} $data
 * @param {int} $status
 */
function output_client_data($data, $status = 200)
{
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');

  http_response_code($status);

  echo json_encode($data);
}
