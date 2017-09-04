<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

/**
 * Reads notifications from the sensor DB API and uses PicoTTS to create sound
 * files from them and play them on the audio device.
 */
class VoiceNotifications
{
    /**
     * @var SensorApi
     */
    protected $api = null;

    /**
     * Number of the raspberry gpio pin on which the power for the speaker
     * can be turned on and off.
     *
     * @var int
     */
    protected $powerPin = -1;

    /**
     * Filename for the unix timestamp of the last received notification.
     *
     * @var string
     */
    protected $timestampFile = 'last_notification.txt';

    /**
     * Timestamp which is send to the API to retrieve only notifications newer
     * than this datetime.
     *
     * @var int
     */
    protected $timestamp = 0;

    /**
     * List of received messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Class constructor - stores the given dependency.
     *
     * @param \RpiSensor\SensorApi $api
     */
    public function __construct(SensorApi $api)
    {
        $this->api = $api;
    }

    /**
     * Sets the pin to power on/off the speaker
     *
     * @param int $powerPin
     */
    public function setPowerPin(int $powerPin)
    {
        $this->powerPin = $powerPin;
    }

    /**
     * Sets the file where the last notification's timestamp is stored.
     *
     * @param string $filename
     */
    public function setTimestampFile(string $filename)
    {
        $this->timestampFile = $filename;
    }

    /**
     * Query the API for new notifications and read them aloud.
     */
    public function read()
    {
        if (file_exists($this->timestampFile)) {
            $latest = file_get_contents($this->timestampFile);
            $this->timestamp = (int)$latest;
        }

        if (!$this->timestamp) {
            $this->timestamp = time();
        }

        $notifications = $this->getNotifications();
        echo "retrieved ".count($notifications)
            ." notifications newer than "
            .date('Y-m-d H:i:s', $this->timestamp)."\n";

        // activate speaker
        if (count($notifications)) {
            $this->powerOn();
        }

        foreach($notifications as $notification) {
            $this->readNotification($notification);
        }

        // deactivate speaker
        if (count($notifications)) {
            $this->powerOff();
        }
    }

    /**
     * Retrieve the notifications from the API.
     *
     * @return array
     */
    protected function getNotifications()
    {
        $result = $this->api->getNotifications($this->timestamp);
        if (isset($result['error'])) {
            echo $result['error']."\n";
            return [];
        }

        return $result;
    }

    /**
     * Generate the wave file using picoTTS and output it to the speaker.
     *
     * @param array $notification
     */
    protected function readNotification(array $notification)
    {
        $fn = '/tmp/'.$notification['timestamp'].'.wav';
        echo $notification['textShort']."\n";

        system('pico2wave -l=de-DE -w='.$fn.' "'
                .addslashes($notification['textShort']).'"');
        system('aplay -q '.$fn);
        unlink($fn);

        // update the timestamp and store the value so we don't request this
        // message again
        if ($notification['timestamp'] > $this->timestamp) {
            $this->timestamp = $notification['timestamp'];
            file_put_contents($this->timestampFile, $this->timestamp);
        }
    }

    /**
     * Activate the speaker (if power is controlled via GPIO).
     */
    protected function powerOn()
    {
        if ($this->powerPin > -1) {
            system('amixer -q set PCM 90%');
            system('raspi-gpio set '.$this->powerPin.' op');
            system('raspi-gpio set '.$this->powerPin.' dl');
        }
    }

    /**
     * Deactivate the speaker (if power is controlled via GPIO).
     */
    protected function powerOff()
    {
        if ($this->powerPin > -1) {
            system('raspi-gpio set '.$this->powerPin.' ip pn');
        }
    }
}
