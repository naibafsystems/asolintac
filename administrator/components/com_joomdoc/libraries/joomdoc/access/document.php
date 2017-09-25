<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

class JoomDOCAccessDocument {

    /**
     * Access create document.
     *
     * @param string $path file relative path
     * @return boolean
     */
    public static function create ($path = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_CREATE, null, $path);
    }

    /**
     * Access edit document.
     *
     * @param int $docid document ID
     * @return boolean
     */
    public static function edit ($docid = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_EDIT, $docid, null);
    }
    /**
     * Complet access to edit. Can edit/can edit own/isn't checked.
     *
     * @param JObject $document has to contain id, created_by and checked_out paramters
     * @return boolean
     */
    public static function canEdit (&$document) {
        $canEdit = JoomDOCAccessDocument::edit($document->get('id'));
        $canEditOwn = JoomDOCAccessDocument::editOwn($document->get('id'), $document->get('created_by'));
        // document isn't checked or is checked by current user
        $isNotChecked = $document->get('checked_out') == 0 || $document->get('checked_out') == JFactory::getUser()->id;
        return ($canEdit || $canEditOwn) && $isNotChecked;
    }

    /**
     * Access manage document.
     *
     * @param int $checkedOut user ID who checked out document
     * @param int $userId user ID who want to manage document
     * @return boolean
     */
    public static function manage ($checkedOut) {
        return JoomDOCAccess::authorise(JOOMDOC_CORE_EDIT, 'com_checkin') || $checkedOut == JFactory::getUser()->id || $checkedOut == 0;
    }

    /**
     * Access edit own document.
     *
     * @param int $docid document ID
     * @param int $createdBy user ID who created document
     * @return boolean
     */
    public static function editOwn ($docid = null, $createdBy = null) {
        return ($createdBy == JFactory::getUser()->get('id') && JoomDOCAccessDocument::authorise(JOOMDOC_CORE_EDIT_OWN, $docid, null));
    }

    /**
     * Access set document state.
     *
     * @param int $docid document ID
     * @param int $checkedOut user ID who checked out document
     * @param string $path file path
     * @return boolean
     */
    public static function editState ($docid = null, $checkedOut = null, $path = null) {
        if ($checkedOut)
            return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_EDIT_STATE, $docid, $path) && JoomDOCAccessDocument::manage($checkedOut);
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_EDIT_STATE, $docid, $path);
    }

    /**
     * Access delete documents.
     *
     * @return boolean
     */
    public static function delete ($docid = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_DELETE, $docid, null);
    }

    /**
     * Access view documents versions.
     *
     * @param int $docid document ID
     * @return boolean
     */
    public static function viewVersions ($docid = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_VIEW_VERSIONS, $docid, null);
    }
    
    /**
     * Access receive document notifications.
     * 
     * @param int $docid document ID
     * @return boolean
     */
    public static function notifications($docid = null) {
        return JoomDOCAccessDocument::authorise(JOOMDOC_CORE_RECEIVE_NOTIFICATION, $docid, null);
    }
    
    /**
     * Access item by document ID or file path.
     * If document ID not available search last parent with document to inherit access.
     * If parent document not found use global setting.
     *
     * @param string $task accessed task name
     * @param mixed $docid document ID if null search parent, if false search document ID by path
     * @param string $path file path
     * @return boolean
     */
    public static function authorise ($task, $docid, $path, $sessionid = null) {
        static $model;
        /* @var $model JoomDOCModelDocument */
        if (is_null($model))
            $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        if ($docid === null)
            // search last parent document ID
            $docid = $model->getParentDocumentID($path);
        if ($docid === false)
            // search document ID by path
            $docid = $model->searchIdByPath($path);
        if ($docid)
            // get access from file document or inherit from last parent
            return JoomDOCAccess::authorise($task, JoomDOCHelper::getDocumentAsset($docid), $sessionid);
        // get global access
        return JoomDOCAccess::authorise($task, JOOMDOC_OPTION, $sessionid);
    }
}
?>