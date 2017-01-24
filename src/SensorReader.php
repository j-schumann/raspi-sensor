<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

/**
 * Process for continuously checking the UART devices for new readings and
 * pushing them to the sensor-db.
 */
class SensorReader
{
    /**
     * @var SensorApi
     */
    protected $api = null;

    /**
     * @var UartReader
     */
    protected $reader = null;

    /**
     * Cache of all parsed sensor readings.
     *
     * @var array
     */
    protected $lines = [];

    /**
     * Class constructor - stores the given dependencies.
     *
     * @param \RpiSensor\SensorApi $api
     * @param \RpiSensor\UartReader $uart
     */
    public function __construct(SensorApi $api, UartReader $uart)
    {
        $this->api = $api;
        $this->reader = $uart;
    }

    /**
     * Enter the loop to continouusly check the UART for new sensor readings.
     */
    public function run()
    {
        while (true) {
            $input = $this->reader->read();
            $this->processInput($input);
            sleep(1);
        }
    }

    /**
     * Extracts the data from the given input string and pushes it to the API.
     *
     * @param string $input
     */
    protected function processInput(string $input)
    {
        $lines = explode("\n", $input);

        foreach ($lines as $line) {
            $line = trim($line, " \r\n");
            if (!$line) {
                continue;
            }

            echo date('Y-m-d H:i:s: ') . $line . "\n";

            $data = $this->filterLine($line);
            if ($data) {
                $this->lines[] = $data;
                $this->pushReadings();
            }
        }
    }

    /**
     * Extrahiert aus der empfangenen Zeile die Daten für die API.
     *
     * @return array|false
     */
    protected function filterLine($line)
    {
        $parts = explode(' ', $line);
        if (count($parts) != 2) {
            return false;
        }

        $sensor = $parts[0];
        if (!is_numeric($sensor)) {
            return false;
        }

        $items = [];
        $pairs = explode('&', $parts[1]);
        foreach ($pairs as $pair) {
            $kv = explode('=', $pair);
            if (count($kv) != 2) {
                continue;
            }

            $items[$kv[0]] = $kv[1];
        }

        return [
            // store the receive timestamp, when we cannot reach the server we
            // keep the readings in memory to retry later, but of cause we want
            // the original time to appear in the database
            'timestamp' => time(),

            'sensor'    => $sensor,
            'items'     => $items,
        ];
    }

    /**
     * Tries to push all accumulated readings to the server, removes pushed
     * lines and keeps failed to retry later.
     */
    public function pushReadings()
    {
        foreach ($this->lines as $k => $data) {
            $result = $this->api->pushReading($data);
            if ($result['status'] != 200) {
                echo 'API fail '.$result['status'].': '.$result['response']."\n";
                break;
            }

            unset($this->lines[$k]);
            //echo "posted: ".json_encode($data)."\n";
            echo 'API response: '.$result['response']."\n";
        }
    }
}
