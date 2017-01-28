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

// @todo getLimitStatus
// if limits fail -> display those
// else getLastValue(tempIn) + getLastValue(tempOut) -> display those

$fo = new FramebufferOutput();
$i = $fo->textToImage("Lorem\nipsum dolor sit amet");
$fo->writeToFramebuffer($i);
imagepng($i, __DIR__.'/../test.png');
