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
      $filename = basename($file);

      $parts = explode("-", $filename);

      $token = $parts[0];
      $microtime = $parts[1];

      $contents = file_get_contents($file);

      $data = json_decode($contents);

      $year = intval(date("Y", $microtime));
      $month = intval(date("m", $microtime));
      $day = intval(date("d", $microtime));

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
    $microtime = $parts[1];

    $contents = file_get_contents($file);

    $data = json_decode($contents);

    $year = intval(date("Y", $microtime));
    $month = intval(date("m", $microtime));
    $day = intval(date("d", $microtime));

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
