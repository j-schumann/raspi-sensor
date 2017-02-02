<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace RpiSensor;

/**
 * Prepare images for output to a framebuffer device using raw RGB565 format,
 * e.g. fbtft devices.
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
    public function rgb888ToRgb565(int $rgb888) : int
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
    public function rgbToRgb565(int $r, int $g, int $b) : int
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
    public function imageToFramebuffer($img) : string
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
     * @param string $file
     *
     * @return bool true if file/buffer was written, else false
     */
    public function writeFramebuffer($img, string $file = '/dev/fb1') : bool
    {
        $fbContent = $this->imageToFramebuffer($img);
        $bytes = file_put_contents($file, $fbContent);

        return $bytes == strlen($fbContent);
    }

    /**
     * Retrieve the display resolution for the given framebuffer device.
     *
     * @param string $fb
     * @return array    [width, height]
     * @throws \RuntimeException
     */
    public function getFramebufferResolution(string $fb = 'fb1') : array
    {
        $res = file_get_contents('/sys/class/graphics/'.$fb.'/virtual_size');
        if (!$res) {
            throw new \RuntimeException('Could not read the framebuffer resolution');
        }

        $wh = explode(',', $res);
        if (count($wh) !== 2) {
            throw new \RuntimeException('Could not parse the framebuffer resolution');
        }

        return [
            'width'  => (int)trim($wh[0]),
            'height' => (int)trim($wh[1])];
    }
}
