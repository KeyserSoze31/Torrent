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

class Encode
{
    /**
     * @var string
     */
    protected $content;

    /**
     * 
     * @param array $info
     */
    public function __construct(array $info)
    {
        $this->decideEncode($info);
    }

    /**
     * Passes lists and dictionaries accordingly, 
     * and has encodeEntry handle the strings and integers.
     * 
     * @param  mixed $unknown
     * @return string|void
     */
    public function decideEncode($unknown)
    {
        if (is_array($unknown)) {
            if (isset($unknown[0]) || empty($unknown)) {
                return $this->encodeList($unknown);
            } else {
                return $this->encodeDict($unknown);
            }
        }

        $this->encodeEntry($unknown);
    }

    /**
     * Encodes strings, integers and empty dictionaries.
     * 
     * @param  mixed   $entry
     * @param  boolean $unstrip is set to true when decoding dictionary keys
     * @return void
     */
    public function encodeEntry($entry, $unstrip = false)
    {
        if (is_bool($entry)) {
            $this->content .= "de";
            return;
        }

        if (is_int($entry) || is_float($entry)) {
            $this->content .= "i" . $entry . "e";
            return;
        }

        if ($unstrip) {
            $myentry = stripslashes($entry);
        } else {
            $myentry = $entry;
        }

        $length = strlen($myentry);

        $this->content .= $length . ":" . $myentry;
        return;
    }

    /**
     * Encodes lists
     * 
     * @param  array $array
     * @return void
     */
    public function encodeList($array)
    {
        $this->content .= "l";

        // The empty list is defined as array();
        if (empty($array)) {
            $this->content .= "e";
            return;
        }

        for ($i = 0; isset($array[$i]); $i++) {
            $this->decideEncode($array[$i]);
        }
        $this->content .= "e";
    }

    /**
     * Encodes dictionaries
     * 
     * @param  mixed $array
     * @return void
     */
    public function encodeDict($array)
    {
        $this->content .= "d";

        if (is_bool($array)) {
            $this->content .= "e";
            return;
        }

        // NEED TO SORT!
        $newarray = $this->makeSorted($array);

        foreach ($newarray as $left => $right) {
            $this->encodeEntry($left, true);
            $this->decideEncode($right);
        }
        $this->content .= "e";
        return;
    }

    /**
     * Dictionary keys must be sorted. foreach tends to iterate over the 
     * order the array was made, so we make a new one in sorted order.
     * 
     * @param  array $array
     * @return array
     */
    public function makeSorted($array)
    {
        $i = 0;

        // Shouldn't happen!
        if (empty($array)) {
            return $array;
        }

        foreach ($array as $key => $value) {
            $keys[$i++] = stripslashes($key);
        }

        sort($keys);

        for ($i = 0 ; isset($keys[$i]); $i++) {
            $return[addslashes($keys[$i])] = $array[addslashes($keys[$i])];
        }

        return $return;
    }

    /**
     * Get torrent
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }
}