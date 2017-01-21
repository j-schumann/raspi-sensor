<?php

namespace RpiSensor;

/**
 * Class to interact with the sensor-db by using the JSON API.
 */
class SensorApi
{
    /**
     * Base URL of the sensor DB api
     *
     * @var string
     */
    protected $url = '';

    /**
     * API key of the account to access.
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * Class constructor - stores the given config.
     *
     * @param string $url
     * @param string $apiKey
     */
    public function __construct(string $url, string $apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    /**
     * Pushes a single sensor reading to the API and returns the result.
     *
     * @param array $data
     * @return array    [status, response]
     */
    public function pushReading(array $data)
    {
        // inject the API key
        $data['apiKey'] = $this->apiKey;

        // retrieve the CURL resource to the reading API endpoint
        $ch = $this->getCurl($this->url.'api/reading/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['status' => $status, 'response' => $response];

        // @todo den status hier schon auswerten und nur die messages/notices
        // oder FEhler zurückgeben
    }

    /**
     * Retrieve notifications from the API that are newer than the given
     * timestamp.
     *
     * @param int $timestamp
     * @return array
     */
    public function getNotifications(int $timestamp)
    {
        // retrieve the CURL resource to the notification API endpoint
        $url = $this->url.'api/notifications/?apiKey='.$this->apiKey;
        $url .= '&after='.$timestamp;
        $ch = $this->getCurl($url);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['status' => $status, 'response' => $response];

        // @todo den status hier schon auswerten und nur die notifications
        // oder Fehler zurückgeben
    }

    /**
     * Retrieve a CURL resource to read/write from/to the API.
     *
     * @param string $url
     * @return resource
     */
    protected function getCurl(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return $ch;
    }
}
