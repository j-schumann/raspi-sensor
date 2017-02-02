<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

use GDText\Box;
use GDText\Color;

/**
 * Reads limit status from the sensor DB API and uses the framebuffer display
 * to show information on the TFT.
 */
class StatusDisplay
{
    /**
     * @var SensorApi
     */
    protected $api = null;

    /**
     * List of status information for each limit
     *
     * @var array
     */
    protected $status = [];

    /**
     * Name of the framebuffer device to use.
     *
     * @var string
     */
    protected $device = 'fb1';

    /**
     * Class constructor - stores the given dependencies.
     *
     * @param \RpiSensor\SensorApi $api
     * @param string $device
     */
    public function __construct(SensorApi $api, string $device = 'fb1')
    {
        $this->api = $api;
        $this->device = $device;
    }

    /**
     * Query the API for the current limit status and error messages or
     * general status on a framebuffer display.
     */
    public function update()
    {
        $this->status = $this->getLimitStatus();
        $matched = [];
        foreach($this->status as $limitId => $info) {
            if ($info['status'] !== 'ok') {
                $matched[] = $limitId;
            }
        }

        echo "retrieved status for ".count($this->status)
            .' limits ('.count($matched).' matching) at '
            .date('Y-m-d H:i:s', time())."\n";


        if (count($matched)) {
            $this->displayMatches($matched);
        } else {
            $this->displayOk();
        }
    }

    /**
     * Called when all limits are currently ok.
     * Displays general status information.
     */
    protected function displayOk()
    {
        $fb = new FramebufferOutput();
        $res = $fb->getFramebufferResolution($this->device);

        $wz = $this->getSensorStatus(20);
        $fenster = $this->getSensorStatus(1);
        if (!isset($wz['items']) || !isset($fenster['items'])) {
            $text = 'Konnte Sensor-Status nicht lesen!';
        } else {
            $text = 'Innen: '.round($wz['items']['t']['lastValue'], 1)
                    .$wz['items']['t']['unit']."\n"
                    .'Aussen: '.round($fenster['items']['t2']['lastValue'], 1)
                    .$fenster['items']['t2']['unit'];
        }


        // flip w/h here and rotate the image later as the display is attached
        // in portrait mode (setting rotate in fbtft config causes lines with
        // random pixel as the actuation seems of) but we want to view it in
        // wide screen
        $im = imagecreatetruecolor($res['height'], $res['width']);

        $backgroundColor = imagecolorallocate($im, 0, 150, 0);
        imagefill($im, 0, 0, $backgroundColor);

        $box = new Box($im);
        $box->setFontFace(__DIR__.'/../fonts/coolvetica.ttf');
        $box->setFontColor(new Color(255, 255, 255));
        $box->setFontSize(16);
        $box->setLineHeight(1.5);
        $box->setBox(10/*x*/, 0/*y*/, $res['height'] - 10, $res['width']);
        $box->setTextAlign('left', 'top');
        $box->draw($text);

        $rotated = imagerotate($im, 90, 0);
        $fb->writeFramebuffer($rotated, '/dev/'.$this->device);
    }

    /**
     * Called when at least on limit currently matches.
     * Display the error message.
     *
     * @param array $matches
     */
    protected function displayMatches(array $matches)
    {
        $text = '';
        for ($i = 0; $i < min(4, count($matches)); $i++) {
            $status = $this->status[$matches[$i]];
            var_dump($status);
            if ($status['customMessage']) {
                $text .= $status['customMessage']."\n";
            } else {
                $text .= 'Grenzwert '.$status['limitName']
                    .' ('.$status['itemName'].' - '.$status['sensorName']
                    .') erreicht seit '.date('d.m.Y H:i:s', $status['since'])
                    ."\n";
            }
        }

        $fb = new FramebufferOutput();
        $res = $fb->getFramebufferResolution($this->device);

        // flip w/h here and rotate the image later as the display is attached
        // in portrait mode (setting rotate in fbtft config causes lines with
        // random pixel as the actuation seems of) but we want to view it in
        // wide screen
        $im = imagecreatetruecolor($res['height'], $res['width']);

        $backgroundColor = imagecolorallocate($im, 200, 0, 0);
        imagefill($im, 0, 0, $backgroundColor);

        $box = new Box($im);
        $box->setFontFace(__DIR__.'/../fonts/coolvetica.ttf');
        $box->setFontColor(new Color(255, 255, 255));
        $box->setFontSize(16);
        $box->setLineHeight(1.5);
        $box->setBox(10/*x*/, 0/*y*/, $res['height'] - 10, $res['width']);
        $box->setTextAlign('left', 'top');
        $box->draw($text);

        $rotated = imagerotate($im, 90, 0);
        $fb->writeFramebuffer($rotated, '/dev/'.$this->device);
    }

    /**
     * Retrieve the limit status from the API.
     *
     * @return array
     */
    protected function getLimitStatus()
    {
        $result = $this->api->getLimitStatus();
        if (isset($result['error'])) {
            echo $result['error']."\n";
            return [];
        }

        return $result;
    }

    /**
     * Retrieve the status status from the API.
     *
     * @return array
     */
    protected function getSensorStatus($identifier)
    {
        $result = $this->api->getSensorStatus($identifier);
        if (isset($result['error'])) {
            echo $result['error']."\n";
            return [];
        }

        return $result;
    }
}
