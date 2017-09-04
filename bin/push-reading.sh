#!/usr/bin/php
<?php
/**
 * Script to push a single sensor item reading to the sensor-db,
 * e.g. to push FHEM state changes to the API.
 */

namespace RpiSensor;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';
$api    = new SensorApi($config['api']['url'], $config['api']['key']);

if ($argc !== 4) {
    die("Usage: push-reading.sh sensorId itemId readingValue\n");
}

$data = [
    'timestamp' => time(),
    'sensor'    => $argv[1],
    'items'     => [
        $argv[2] => $argv[3],
    ],
];

$result = $api->pushReading($data);
//echo 'API response '.json_encode($result)."\n";
