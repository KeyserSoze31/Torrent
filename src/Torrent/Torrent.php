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

use DateTime;

class Torrent
{
    /**
     * @var string
     */
    protected $announce;

    /**
     * @var array
     */
    protected $announceList = array();

    /**
     * @var string
     */
    protected $infoHash;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var integer
     */
    protected $length = 0;

    /**
     * @var array
     */
    protected $files = array();

    /**
     * @var integer
     */
    protected $fileCounts = 0;

    /**
     * @var string
     */
    protected $comment;

    /**
     * 
     * @param string $torrent
     */
    public function __construct($torrent)
    {
        $torrent = Decode::decode($torrent);

        $this->announce = $torrent['announce'];

        if (isset($torrent['announce-list'])) {
            $this->announceList = $torrent['announce-list'];
        }

        $this->infoHash = hash('sha1', new Encode($torrent['info']));

        $this->date = new DateTime('@' . $torrent['creation date']);

        $this->name = $torrent['info']['name'];

        if (isset($torrent['info']['files'])) {
            $this->files = $torrent['info']['files'];

            foreach ($torrent['info']['files'] as $file) {
                $this->fileCounts++;
                $this->length += $file['length'];
            }
        } else {
            $this->length = $torrent['length'];
            $this->fileCounts++;
        }

        if (isset($torrent['comment'])) {
            $this->comment = $torrent['comment'];
        }
    }

    /**
     * 
     * @return string
     */
    public function getAnnounce()
    {
        return $this->announce;
    }

    /**
     * 
     * @return array
     */
    public function getAnnounceList()
    {
        return $this->announceList;
    }

    /**
     * Get info hash
     * 
     * @return string
     */
    public function getInfoHash()
    {
        return $this->infoHash;
    }

    /**
     * Get date
     * 
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get length
     * 
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get files
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get the number of files
     * 
     * @return integer
     */
    public function getFileCount()
    {
        return $this->fileCounts;
    }

    /**
     * Get comment
     * 
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}