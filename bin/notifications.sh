#!/usr/bin/php
<?php
/**
 * Script to check the API for new notifications and read them with voice
 * generation on the audio port, to be run via crontab.
 */

namespace RpiSensor;

ini_set('display_errors', true);
require_once __DIR__.'/../vendor/autoload.php';
$config = include __DIR__.'/../config.inc.php';
$api    = new SensorApi($config['api']['url'], $config['api']['key']);

$vn = new VoiceNotifications($api);
$vn->setPowerPin($config['notifications']['power_pin']);
$vn->setTimestampFile($config['notifications']['timestamp_file']);
$vn->read();
