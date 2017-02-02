<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

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
     * @return array    [error|message+notices]
     */
    public function pushReading(array $data) : array
    {
        // inject the API key
        $data['apiKey'] = $this->apiKey;

        $url = $this->url.'api/reading';
        $result = $this->sendRequest($url, [], $data);

        if (isset($result['error'])) {
            return $result;
        }

        // server always responds with a message
        // (either error message or "Success")
        if (!isset($result['message'])) {
            return [
                'error' => 'failed to push reading the API',
            ];
        }

        return $result;
    }

    /**
     * Retrieve limit status information from the API.
     * If an error occured the returned array has the key "error" with the
     * message.
     *
     * @return array
     */
    public function getLimitStatus() : array
    {
        $url = $this->url.'api/limit-status';
        $result = $this->sendRequest($url, [
            'apiKey' => $this->apiKey,
        ]);

        if (isset($result['error'])) {
            return $result;
        }

        if (!isset($result['status'])) {
            return [
                'error' => 'failed to retrieve limit status from the API',
            ];
        }

        return $result['status'];
    }

    /**
     * Retrieve sensor status information from the API.
     *
     * @return array
     */
    public function getSensorStatus(string $sensorIdentifier)
    {
        $url = $this->url.'api/sensor-status';
        $result = $this->sendRequest($url, [
            'apiKey' => $this->apiKey,
            'sensor' => $sensorIdentifier,
        ]);

        if (isset($result['error'])) {
            return $result;
        }

        if (!isset($result['sensor'])) {
            return [
                'error' => 'failed to retrieve sensor status from the API',
            ];
        }

        return $result['sensor'];
    }

    /**
     * Retrieve notifications from the API that are newer than the given
     * timestamp.
     * If an error occured the returned array has the key "error" with the
     * message.
     *
     * @param int $timestamp
     * @return array
     */
    public function getNotifications(int $timestamp) : array
    {
        $url = $this->url.'api/notifications';
        $result = $this->sendRequest($url, [
            'apiKey' => $this->apiKey,
            'after'  => $timestamp,
        ]);

        if (isset($result['error'])) {
            return $result;
        }

        if (!isset($result['notifications'])) {
            return [
                'error' => 'failed to retrieve notifications from the API',
            ];
        }

        return $result['notifications'];
    }

    /**
     * Sends the given request to the API server and returns the response.
     * If an error occured an array with the error message is returned.
     *
     * @param string $url   URL to the API endpoint, including action
     * @param array $get    GET parameters to append to the URL
     * @param mixed $post   data to encode as json and send as POST
     * @return mixed        decoded JSON response (most time will be an array)
     */
    protected function sendRequest(string $url, array $get = [], $post = null)
    {
        if (count($get)) {
            $url .= '?'.http_build_query($get);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        }

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status != 200) {
            return [
                'error' => 'API fail '.$status.': '.$response,
            ];
        }

        $json = json_decode($response, true);
        if ($json === null) {
            return [
                'error' => 'API fail: invalid or empty JSON response',
            ];
        }

        return $json;
    }
}
