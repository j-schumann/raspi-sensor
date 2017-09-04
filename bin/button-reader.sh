#!/usr/bin/php
<?php
/**
 * Script to watch the UART device for sensor readings, enters a loop, to be
 * run on system startup or via supervisor.
 */

namespace RpiSensor;

use PhpGpio\Gpio;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';

$api    = new SensorApi($config['api']['url'], $config['api']['key']);
$statusDisplay = new StatusDisplay($api, $config['display']['device']);
$gpioWatch = new GpioWatch($config['gpio_watch'], $statusDisplay);

echo "Starting GPIO watch\n";
$gpioWatch->watch();