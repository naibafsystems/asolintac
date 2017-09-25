<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JoomDOCAccessFileSystem extends JoomDOCAccess {

    /**
     * Access creating new folder.
     *
     * @param int $docid document ID
     * @param string $path file relative path
     * @return boolean
     */
    public static function newFolder ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_NEWFOLDER, $docid, $path);
    }

    /**
     * Access deleting folders/files.
     *
     * @param int $docid document ID
     * @param string $path file relative path
     * @return boolean
     */
    public static function deleteFile ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_DELETEFILE, $docid, $path);
    }

    /**
     * Access upload files.
     *
     * @param int $docid document ID
     * @param string $path file relative path
     * @return void
     */
    public static function uploadFile ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_UPLOADFILE, $docid, $path);
    }

    /**
     * Access edit file by webdav.
     *
     * @param int $docid document ID
     * @param string $path file relative path
     * @return boolean
     */
    public static function editWebDav ($docid = null, $path = null, $sessionid = null) {
    	        return false;
    }
    /**
     * Access view file info.
     *
     * @param int $docid document ID
     * @param string $path file relative path
     * @return boolean
     */
    public static function viewFileInfo ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_VIEWFILEINFO, $docid, $path);
    }
    
    /**
     * Access download file.
     *
     * @param int $docid document id
     * @param string $path file relative path
     * @return boolean
     */
    public static function download ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_DOWNLOAD, $docid, $path);
    }
    /**
     * Access view folder content.
     *
     * @param int $docid document id
     * @param string $path file relative path
     * @return boolean
     */
    public static function enterFolder ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_ENTERFOLDER, $docid, $path);
    }
    /**
     * Access rename folder/file.
     *
     * @param int $docid document id
     * @param string $path file relative path
     * @return boolean
     */
    public static function rename ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_RENAME, $docid, $path);
    }
    /**
     * Access copy/move folder/file.
     *
     * @param int $docid document id
     * @param string $path file relative path
     * @return boolean
     */
    public static function copyMove ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_COPY_MOVE, $docid, $path);
    }
    /**
     * Access refresh file's list.
     *
     * @return boolean
     */
    public static function refresh () {
        return JoomDOCAccess::authorise(JOOMDOC_CORE_REFRESH);
    }
        /**
     * Access untrash file.
     *
     * @param int $docid document id
     * @param string $path file relative path
     * @return boolean
     */
    public static function  untrash ($docid = null, $path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_UNTRASH, $docid, $path);
    }
        
}
?>