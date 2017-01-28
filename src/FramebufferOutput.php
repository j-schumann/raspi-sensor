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
 * Create images from text and prepare images for output to a framebuffer device
 * using raw RGB565 format, e.g. fbtft devices.
 *
 * @see https://github.com/notro/fbtft
 */
class FramebufferOutput
{
    /**
     * Convert a 24bit RGB color (0x123456) to 16bit (0x1234).
     *
     * @param int $rgb888
     * @return int
     */
    public function rgb888ToRgb565(int $rgb888)
    {
        return ($rgb888 >> 8 & 0xf800)
             | ($rgb888 >> 5 & 0x07e0)
             | ($rgb888 >> 3 & 0x001f);
    }

    /**
     * Convert a color defined by its RGB values (0-255) to 16bit (0x1234).
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return int
     */
    public function rgbToRgb565(int $r, int $g, int $b)
    {
        return (($r >> 3) << 11) | (($g >> 2) << 5) | ($b >> 3);
    }

    /**
     * Convert the image given as resource into raw RGB565 for direct output
     * on a framebuffer device (compatible with fbtft @ Raspberry).
     * Replaces "fbi" (framebuffer imageviewer) which leaves a process open.
     *
     * @param resource $img
     * @return string
     */
    function imageToFramebuffer($img)
    {
        $h = imagesy($img);
        $w = imagesx($img);
        $fb = '';

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb888 = imagecolorat($img, $x, $y);
                $rgb565 = $this->rgb888ToRgb565($rgb888);
                $fb .= pack("v", $rgb565);
            }
        }

        return $fb;
    }

    /**
     * Directly write the given image to the given framebuffer device or file.
     *
     * @param resource $img
     * @param string $device
     */
    public function writeFramebuffer($img, string $device = '/dev/fb1')
    {
        $fbContent = $this->imageToFramebuffer($img);
        file_put_contents($device, $fbContent);
    }

    /**
     * @todo params: image size, font color, bg color
     *
     * @param type $text
     * @return type
     */
    public function textToImage($text)
    {
        $im = imagecreatetruecolor(160, 128);
        $backgroundColor = imagecolorallocate($im, 0, 18, 64);
        imagefill($im, 0, 0, $backgroundColor);

        $box = new Box($im);
        $box->setFontFace(__DIR__.'/../fonts/coolvetica.ttf');
        $box->setFontColor(new Color(255, 255, 255));
        $box->setFontSize(16);
        $box->setLineHeight(1.5);
        $box->setBox(10, 10, 118, 150);
        $box->setTextAlign('left', 'top');
        $box->draw($text);

        return imagerotate($im, 90, 0);
    }
}
