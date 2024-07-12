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
 * @returns {array} of domain objectss with children file contents as data event counts in respective keys
 */
function list_all_clients()
{
  global $plugin_path;

  $domains = [];

  foreach (glob("{$plugin_path}/logs/*") as $domain) {
    $domain = basename($domain);

    $stats = [
      'pageviews' => 0,
      'unique_sessions' => 0,
      'unique_visitors' => 0,
      'conversions' => 0,
      'calls' => 0,
      'emails' => 0,
      'submissions' => 0,
      'form_abandonments' => 0,
      'data' => [],
    ];

    $pageviews = [];
    $visitors = [];

    foreach (glob("{$plugin_path}/logs/{$domain}/*.json") as $file) {
      $contents = file_get_contents($file);

      $data = json_decode($contents);

      if (!$data) {
        continue;
      }

      $event = $data->event;

      // fill counts
      switch ($event) {
        case 'pageview':
          $stats['pageviews']++;

          $pageviews[] = $data->session_start;

          $visitors[] = $data->session_token;

          break;
        case 'conversion':
          $stats['conversions']++;

          switch ($data->category) {
            case 'Email':
              $stats['emails']++;

              break;
            case 'Call':
              $stats['calls']++;

              break;
            case 'form':
              $stats['submissions']++;

              break;
          }

          break;
        case 'abandonment':
          $stats['form_abandonments']++;

          break;
      }

      $stats['data'] = $data;

      if ($data->category === 'form') {
        $filename = basename($file);
        $filename_parts = explode("-", $filename);
        $event_microtime = $filename_parts[1];
        $event_microtime_parts = explode(" ", $event_microtime);
        $event_microtime_seconds = intval($event_microtime_parts[1]);
        $event_microtime_milliseconds = floatVal($event_microtime_parts[0]);

        $form_data = [
          'time' => $event_microtime_seconds,
          'status' => $event,
          'page' => $data->title,
          'fields' => $data->data,
        ];

        $stats['forms'][$event_microtime_milliseconds] = $form_data;
      }
    }

    if (isset($stats['forms']) && is_array($stats['forms'])) {
      rsort($stats['forms']);
    }

    $stats['unique_sessions'] = count(array_unique($pageviews));
    $stats['unique_visitors'] = count(array_unique($visitors));

    // convert stats to object
    $domains[$domain] = (object) $stats;
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
