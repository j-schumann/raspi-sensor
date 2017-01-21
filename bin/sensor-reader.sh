#!/usr/bin/php
<?php
/**
 * Script to watch the UART device for sensor readings, enters a loop, to be
 * run on system startup or via supervisor.
 */
namespace RpiSensor;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';

$reader = new UartReader($config['uart']['device']);
$api    = new SensorApi($config['api']['url'], $config['api']['key']);

$process = new SensorReader($api, $reader);
$process->run();
