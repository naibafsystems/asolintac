<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.filesystem.file');

class JoomDOCFile extends JFile
{
    /**
     * Absolute file path in file system.
     * 
     * @var string
     */
    private $absolutePath;
    /**
     * Relative file path from Joomla root.
     * 
     * @var string
     */
    private $relativePath;
    /**
     * File name without path.
     * 
     * @var string
     */
    private $name;
    /**
     * File extension.
     * 
     * @var string
     */
    private $extension;
    /**
     * File size in human readable format.
     * 
     * @var string
     */
    private $size;
    /**
     * Full file URL (domain/path).
     * 
     * @var string
     */
    private $url;
    /**
     * Document data.
     * 
     * @var stdClass
     */
    public $document;
    /**
     * File hits (download).
     * 
     * @var int
     */
    public $hits;
    /**
     * Date of file uploading.
     * 
     * @var string
     */
    private $uploaded;
    /**
     * File mimetype.
     * 
     * @var string
     */
    private $mimetype;
	/**
     * File is symbolic link.
     * 
     * @var boolean
     */
    public $isSymLink;
    
    /**
     * Create and init object. Set absolute path.
     * 
     * @param string $absolutePath file absolute path
     * @return void
     */
    public function __construct($absolutePath, $isSymLink = false)
    {
        $this->absolutePath = JPath::clean($absolutePath);
        $this->relativePath = JoomDOCFileSystem::getRelativePath($this->absolutePath);
        $this->isSymLink = $isSymLink;
        $this->name = parent::getName($this->absolutePath);
        $this->extension = parent::getExt($this->absolutePath);
        $this->size = JoomDOCFileSystem::getFileSize($this->absolutePath);
        $this->url = JoomDOCFileSystem::getURL($this->relativePath);
        $this->document = null;
        $this->hits = 0;
        $this->uploaded = filemtime($this->absolutePath);
        $this->mimetype = function_exists('mime_content_type') && is_readable($this->absolutePath) ? mime_content_type($this->absolutePath) : '';
    }

    /**
     * Get absolute file path in file system.
     * 
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * Get relative file path from Joomla root.
     * 
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Get file name without path.
     * 
     * @return string
     */
    public function getFileName()
    {
        return $this->name;
    }

    /**
     * Get file extension.
     * 
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get file size in human readable format.
     * 
     * @return string
     */
    public function getFileSize()
    {
        return $this->size;
    }

    /**
     * Get full file URL (domain/path).
     * 
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Get file date uploaded.
     * 
     * @return string
     */
    public function getUploaded()
    {
        return $this->uploaded;
    }

    /**
     * Get file mime type.
     * 
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimetype;
    }

    /**
     * Ordinary method to acces object properties.
     * 
     * @param string $param name of property
     * @return string property value, if property no exists function return null
     */
    public function get($param)
    {
        if (isset($this->$param))
            return $this->$param;
        return null;
    }

}

?>