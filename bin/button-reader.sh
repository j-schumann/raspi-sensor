#!/usr/bin/php
<?php
/**
 * Script for watching GPIO pins for changes and executing actions.
 */

namespace RpiSensor;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';

$api    = new SensorApi($config['api']['url'], $config['api']['key']);
$statusDisplay = new StatusDisplay($api, $config['display']['device']);
$gpioWatch = new GpioWatch($config['gpio_watch'], $statusDisplay);

echo "Starting GPIO watch\n";
$gpioWatch->watch();