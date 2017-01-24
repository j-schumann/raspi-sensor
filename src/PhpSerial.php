<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

use PhpSerial as BaseSerial;

/**
 * Overwrite base constructor to suppress error from stty when the script is
 * called via supervisord.
 */
class PhpSerial extends BaseSerial
{
    /**
     * Constructor. Perform some checks about the OS and setserial
     *
     * @return PhpSerial
     */
    public function __construct()
    {
        setlocale(LC_ALL, "en_US");

        $sysName = php_uname();

        if (substr($sysName, 0, 5) === "Linux") {
            $this->_os = "linux";

            // @jschumann: return code = 1 when run via supervisor
            $res = $this->_exec("stty");
            if ($res === 0 || $res === 1) {
                register_shutdown_function(array($this, "deviceClose"));
            } else {
                trigger_error(
                    "No stty availible, unable to run.",
                    E_USER_ERROR
                );
            }
        } elseif (substr($sysName, 0, 6) === "Darwin") {
            $this->_os = "osx";
            register_shutdown_function(array($this, "deviceClose"));
        } elseif (substr($sysName, 0, 7) === "Windows") {
            $this->_os = "windows";
            register_shutdown_function(array($this, "deviceClose"));
        } else {
            trigger_error("Host OS is neither osx, linux nor windows, unable " .
                          "to run.", E_USER_ERROR);
            exit();
        }
    }
}
