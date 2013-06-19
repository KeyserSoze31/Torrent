<?php
/**
 * @package Torrent
 * @author Keyser Söze
 * @copyright Copyright (c) 2013 Keyser Söze
 * Displays <a href="http://creativecommons.org/licenses/MIT/deed.fr">MIT</a>
 * @license http://creativecommons.org/licenses/MIT/deed.fr MIT
 */

/**
 * @namespace
 */
namespace Torrent;

use SplFileInfo;

class Decode
{
    /**
     * Decode torrent
     * 
     * @param  string|SplFileInfo $torrent
     * @return array
     */
    public static function decode($torrent)
    {
        if ($torrent instanceof SplFileInfo) {
            $torrent = file_get_contents($torrent->getRealPath());
        }

        $decoder = new self();
        $data = $decoder->decodeEntry($torrent);

        return $data[0];
    }

    /**
     * Decode torrent
     * 
     * @param  string  $wholefile
     * @param  integer $offset
     * @return array
     */
    public function decodeEntry($wholefile, $offset = 0)
    {
        if ($wholefile[$offset] == 'd') {
            return $this->decodeDict($wholefile, $offset);
        }
        
        if ($wholefile[$offset] == 'l') {
            return $this->decodeList($wholefile, $offset);
        }

        if ($wholefile[$offset] == "i") {
            $offset++;
            return $this->decodeNumber($wholefile, $offset);
        }

        // String value: decode number, then grab substring
        $info = $this->decodeNumber($wholefile, $offset);
        
        if ($info[0] === false) {
            return array(false);
        }

        $ret[0] = substr($wholefile, $info[1], $info[0]);
        $ret[1] = $info[1] + strlen($ret[0]);

        return $ret;
    }

    /**
     * Decode number
     * 
     * @param  string $wholefile
     * @param  integer $start
     * @return array
     */
    public function decodeNumber($wholefile, $start)
    {
        $ret[0] = 0;
        $offset = $start;

        // Funky handling of negative numbers and zero
        $negative = false;

        if ($wholefile[$offset] == '-') {
            $negative = true;
            $offset++;
        }

        if ($wholefile[$offset] == '0') {
            
            $offset++;
            
            if ($negative) {
                return array(false);
            }

            if ($wholefile[$offset] == ':' || $wholefile[$offset] == 'e') {
                $offset++;
                $ret[0] = 0;
                $ret[1] = $offset;

                return $ret;
            }

            return array(false);
        }

        while (true) {

            if ($wholefile[$offset] >= '0' && $wholefile[$offset] <= '9') {
                
                $ret[0] *= 10;
                $ret[0] += ord($wholefile[$offset]) - ord("0");
                $offset++;
            } elseif ($wholefile[$offset] == 'e' || $wholefile[$offset] == ':') {
                // Tolerate : or e because this is a multiuse function
                $ret[1] = $offset+1;
                
                if ($negative) {
                    if ($ret[0] == 0) {
                        return array(false);
                    }
                    $ret[0] = - $ret[0];
                }
                return $ret;
            } else {
                return array(false);
            }
        }
    }

    /**
     * Decode list
     * 
     * @param  string $wholefile
     * @param  integer $start
     * @return array
     */
    public function decodeList($wholefile, $start)
    {
        $offset = $start+1;
        $i = 0;

        if ($wholefile[$start] != 'l') {
            return array(false);
        }
        
        $ret = array();

        while (true) {

            if ($wholefile[$offset] == 'e') {
                break;
            }

            $value = $this->decodeEntry($wholefile, $offset);
            
            if ($value[0] === false) {
                return array(false);
            }
            
            $ret[$i] = $value[0];
            $offset = $value[1];
            $i ++;
        }

        // The empy list is an empty array. Seems fine.
        $final[0] = $ret;
        $final[1] = $offset+1;

        return $final;
    }


    /**
     * Tries to construct an array
     * 
     * @param  string  $wholefile
     * @param  integer $start
     * @return array
     */
    public function decodeDict($wholefile, $start = 0)
    {
        $offset = $start;

        if ($wholefile[$offset] == 'l') {
            return $this->decodeList($wholefile, $start);
        }

        if ($wholefile[$offset] != 'd') {
            return false;
        }

        $ret = array();
        $offset++;

        while (true) {

            if ($wholefile[$offset] == 'e') {
                $offset++;
                break;
            }

            $left = $this->decodeEntry($wholefile, $offset);

            if (!$left[0]) {
                return false;
            }

            $offset = $left[1];

            if ($wholefile[$offset] == 'd') {
                // Recurse
                $value = $this->decodeDict($wholefile, $offset);

                if (!$value[0]) {
                    return false;
                }

                $ret[addslashes($left[0])] = $value[0];
                $offset= $value[1];

                continue;

            } elseif ($wholefile[$offset] == 'l') {

                $value = $this->decodeList($wholefile, $offset);

                if (!$value[0] && is_bool($value[0])) {
                    return false;
                }

                $ret[addslashes($left[0])] = $value[0];
                $offset = $value[1];

            } else {

                $value = $this->decodeEntry($wholefile, $offset);

                if ($value[0] === false) {
                    return false;
                }

                $ret[addslashes($left[0])] = $value[0];
                $offset = $value[1];
            }
        }

        if (empty($ret)) {
            $final[0] = true;
        } else {
            $final[0] = $ret;
        }

        $final[1] = $offset;

        return $final;
    }
}