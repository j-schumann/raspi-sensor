#!/usr/bin/php
<?php
/**
 * Script to show status information on a small display.
 * If one or more limits currently fail display these, else display status.
 */

namespace RpiSensor;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';
$api    = new SensorApi($config['api']['url'], $config['api']['key']);

$statusDisplay = new StatusDisplay($api, $config['display']['device']);
$statusDisplay->update();
