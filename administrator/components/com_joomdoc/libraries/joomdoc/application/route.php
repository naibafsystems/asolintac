<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.methods');

class JoomDOCRoute extends JRoute {

    /**
     * Get URL to open component.
     *
     * @return string
     */
    public static function viewJoomDOC () {
        return 'index.php?option=' . JOOMDOC_OPTION;
    }

    /**
     * Get URL to open page with licenses list.
     *
     * @return string
     */
    public static function viewLicenses () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_LICENSES;
    }
    
    /**
     * Get URL to open page with fields list.
     *
     * @return string
     */
    public static function viewFields () {
    	return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_FIELDS;
    }    

    /**
     * Get URL to open page with documents.
     *
     * @param string file relative path
     * @param string document alias if false search for alias in database
     * @param boolean $short return only path and Itemid parameter
     * @return string
     */
    public static function viewDocuments ($path = null, $alias = null, $short = false) {
        $itemID = null;
        JoomDOCRoute::frontend($path, $alias, $itemID);
        $query = ($path ? '&path=' . JoomDOCString::urlencode($path) : '') . ($itemID ? '&Itemid=' . $itemID : '');
        if ($short)
            return $query;
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_DOCUMENTS . $query;
    }

    /**
     * Get URL to open edit document page.
     *
     * @param int $id document ID
     * @return string
     */
    public static function editDocument ($id) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_EDIT) . '&id=' . $id;
    }

    /**
     * Get URL to open edit license page.
     *
     * @param int $id license ID
     * @return string
     */
    public static function editLicense ($id) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_EDIT) . '&id=' . $id;
    }

    /**
     * Get URL to add new document.
     *
     * @param string $id file path
     */
    public static function addDocument ($path) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_ADD) . '&path=' . JoomDOCString::urlencode($path);
    }

    /**
     * Get URL to save document.
     *
     * @param int $id document ID
     * @return string
     */
    public static function saveDocument ($id) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&layout=edit&id=' . $id;
    }

    /**
     * Get URL to save license.
     *
     * @param int $id license ID
     * @return string
     */
    public static function saveLicense ($id) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&layout=edit&id=' . $id;
    }

    /**
     * Open documents list in modal window.
     *
     * @param string $folder
     * @param boolean $useLinkType
     * @return string
     */
    public static function modalDocuments ($folder = null, $useLinkType = false, $addSymLink = false, $symLinkSource = false) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_DOCUMENTS . '&layout=modal&tmpl=component' . ($folder ? '&path=' . JoomDOCString::urlencode($folder) : '') . ($useLinkType ? '&useLinkType=1' : '') . ($addSymLink ? '&addSymLink=' . $addSymLink : '') . ($symLinkSource ? '&symLinkSource=' . JoomDOCString::urlencode($symLinkSource) : '');
    }

    /**
     * Get URL to download file.
     *
     * @param string $path
     * @param string $alias document alias
     * @param int $version wanted file version
     * @return string
     */
    public static function download ($path, $alias = null, $version = null) {
        $itemID = null;
        JoomDOCRoute::frontend($path, $alias, $itemID);
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_DOWNLOAD) . '&path=' . JoomDOCString::urlencode($path) . ($version ? '&version=' . $version : '') . ($itemID ? '&Itemid=' . $itemID : '');
    }
    
    /**
     * Get URL to show file content.
     *
     * @param string $path file path
     * @param int $version file version
     * @return string
     */
    public static function content ($path, $version) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=content&path=' . JoomDOCString::urlencode($path) . '&version=' . $version . '&tmpl=component';
    }    

    /**
     * Get URL to add file.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function add ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_ADD, $path, $alias);
    }

    /**
     * Get URL to edit file.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function edit ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_EDIT, $path, $alias);
    }

    /**
     * Get URL to publish document.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function publish ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_PUBLISH, $path, $alias);
    }

    /**
     * Get URL to unpublish document.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function unpublish ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_UNPUBLISH, $path, $alias);
    }

    /**
     * Get URL to delete file.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function deleteFile ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_DELETEFILE, $path, $alias);
    }

    /**
     * Get URL to delete document.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function delete ($path, $alias = null) {
        return JoomDOCRoute::frontendDocumentTask(JOOMDOC_TASK_DELETE, $path, $alias, JOOMDOC_DOCUMENTS);
    }

    /**
     * Get URL to make task on frontend file/document.
     *
     * @param string $path
     * @param string $alias document alias
     * @return string
     */
    public static function frontendDocumentTask ($task, $path, $alias, $item = JOOMDOC_DOCUMENT) {
        $itemID = null;
        JoomDOCRoute::frontend($path, $alias, $itemID);
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask($item, $task) . '&path=' . JoomDOCString::urlencode($path) . ($itemID ? '&Itemid=' . $itemID : '');
    }

    /**
     * Get URL to view upgrade page.
     *
     * @return string
     */
    public static function viewUpgrade () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_UPGRADE;
    }
    
    /**
     * Get URL to view upgrade&migration page.
     * 
     * @return string
     */
    public static function viewUpgradeMigration () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_UPGRADE_MIGRATION;
    }
    
    /**
     * Get URL to view migration page.
     * 
     * @return string
     */
    public static function viewMigration () {
    	return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_MIGRATION;
    }
    
	/**
     * Get URL to view symlinks page.
     * 
     * @return string
     */
    public static function viewSymlinks ($filter = null) {
    	return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_SYMLINKS . ($filter ? '&filter=' . JoomDOCString::urlencode($filter) . '&tmpl=component' : '');
    }
    
    /**
     * Get URL to view manual page.
     *
     * @return string
     */
    public static function viewManual () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&layout=' . JOOMDOC_MANUAL;
    }
    /**
     * Get URL to view changelog page.
     *
     * @return string
     */
    public static function viewChangelog () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&layout=' . JOOMDOC_CHANGELOG;
    }
    /**
     * Get URL to view support page.
     *
     * @return string
     */
    public static function viewSupport () {
        return 'index.php?option=' . JOOMDOC_OPTION . '&layout=' . JOOMDOC_SUPPORT;
    }

    /**
     * Get URL to view file detail.
     *
     * @param string $path
     * @return string
     */
    public static function viewFileInfo ($path) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_FILE . '&path=' . JoomDOCString::urlencode($path);
    }

    public static function frontend (&$path, $alias, &$itemID) {
        static $isSite;
        if (is_null($isSite))
            $isSite = JFactory::getApplication()->isSite();
        if (!$isSite)
            return $path;
        // if alias is false on site search for document alias by path
        if ($alias === false) {
            static $model;
            /* @var $model JoomDOCModelDocument */
            if (is_null($model))
                $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
            $alias = $model->searchFullAliasByPath($path);
        }
        $itemID = JoomDOCMenu::getMenuItemID($path);
        $path = $alias ? $alias : $path;
        $path = JoomDOCFileSystem::getVirtualPath($path);
    }

    /**
     * Get URL to view License Text.
     *
     * @param int $id License ID
     * @param string $alias License Alias
     * @param string $path Path to File if License has to be confirmed before dovnloading
     * @return string
     */
    public static function viewLicense ($id, $alias, $path = null, $docAlias = null) {
        $itemID = null;
        JoomDOCRoute::frontend($path, $docAlias, $itemID);
        return JRoute::_('index.php?option=' . JOOMDOC_OPTION . '&view=license&id=' . $id . ':' . $alias . '&tmpl=component' . ($path ? '&path=' . JoomDOCString::urlencode($path) : '') . ($itemID ? '&Itemid=' . $itemID : ''));
    }

    /**
     * Get URL to untrash file.
     *
     * @param string $path
     * @return string
     */
    public static function untrash ($path) {
        return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_FILE, JOOMDOC_TASK_UNTRASH) . '&path=' . JoomDOCString::urlencode($path);
    }
    
    /**
     * Get URL to update tree explorer via Ajax.
     *
     * @param string $path
     * @return string
     */
    public static function updatemootree () {
    	return 'index.php?option=' . JOOMDOC_OPTION . '&task=' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_UPDATEMOOTREE);
    }    
}
?>