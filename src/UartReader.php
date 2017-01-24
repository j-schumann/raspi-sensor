<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

/**
 * Wrapper for PhpSerial to sinplify usage.
 */
class UartReader
{
    /**
     * UART device name/path
     *
     * @var string
     */
    protected $device = '';

    /**
     * @var PhpSerial
     */
    protected $port = null;

    /**
     * Class constructor - stores the given config.
     *
     * @param string $device
     */
    public function __construct(string $device)
    {
        $this->device = $device;
    }

    /**
     * Destructor - closes the device/port.
     */
    public function __destruct()
    {
        $this->port->deviceClose();
    }

    /**
     * Opens the configured device.
     */
    protected function init()
    {
        $this->port = new PhpSerial();
        $this->port->deviceSet($this->device);

        $this->port->confBaudRate(9600);
        $this->port->confParity("none");
        $this->port->confCharacterLength(8);
        $this->port->confStopBits(1);
        $this->port->confFlowControl("none");

        $success = $this->port->deviceOpen();
        if (!$success) {
            $this->port = null;
            return false;
        }

        return true;
    }

    /**
     * Read input from the device.
     *
     * @return string
     */
    public function read()
    {
        if (!$this->port) {
            $success = $this->init();
            if (!$success) {
                // @todo Exception instead?
                return '';
            }
        }

        return $this->port->readPort();
    }
}
