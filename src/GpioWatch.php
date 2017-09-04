<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

use PhpGpio\Gpio;

/**
 * Used to watch GPIO pins for changes to trigger actions.
 */
class GpioWatch
{
    /**
     * @var array
     */
    public $pins = [];

    /**
     * @var Gpio
     */
    private $gpio = null;

    /**
     * @var StatusDisplay
     */
    public $statusDisplay = null;

    /**
     * Store the given dependencies.
     *
     * @param array $pins
     * @param tatusDisplay $statusDisplay
     */
    public function __construct(array $pins, StatusDisplay $statusDisplay)
    {
        $this->gpio = new GPIO();
        $this->pins = $pins;
        $this->statusDisplay = $statusDisplay;
    }

    public function __destruct()
    {
        $this->gpio->unexportAll();
    }

    /**
     * Initialize the given GPIO pin for input and optional pull-up.
     *
     * @param int $pin
     * @param array $config
     */
    public function setupPin(int $pin, array $config = [])
    {
        if (isset($config['pull_up']) && $config['pull_up']) {
            system("raspi-gpio set $pin pu");
        }

        $this->gpio->setup($pin, "in");
    }

    /**
     * Initializes all configured GPIO pins and enters an infinite loop,
     * checking the pins every 500ms.
     */
    public function watch()
    {
        foreach($this->pins as $pin => $config) {
            $this->setupPin($pin, $config);
        }

        while(true) {
            foreach($this->pins as $pin => $config) {
                $this->readPin($pin, $config);
            }

            usleep(500000);
        }
    }

    /**
     * Reads the given GPIO pin, compares the value and if it matches calls
     * the given callback function with the instance itself (e.g. to check other
     * pins or display something else).
     *
     * @param int $pin
     * @param array $config
     * @return boolean
     */
    protected function readPin(int $pin, array $config = [])
    {
        $value = $this->gpio->input($pin);
        if ($value != $config['watch_for']) {
            return false;
        }
        if (isset($config['callback']) && is_callable($config['callback'])) {
            $config['callback']($this);
        }

        return true;
    }
}