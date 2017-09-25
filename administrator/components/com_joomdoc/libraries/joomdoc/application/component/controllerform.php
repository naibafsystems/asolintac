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

class JoomDOCControllerForm extends JControllerForm {
    /**
     * Get document model.
     *
     * @return JoomDOCModelDocument
     */
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
        return JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
    }

    /**
     * Add new document.
     */
    public function add () {
        JFactory::getApplication()->setUserState('path', JoomDOCRequest::getPath());
        parent::add();
    }

    /**
     * Allow add new document.
     */
    protected function allowAdd($data = array()) {
        // relative path of file we want add document
        $path = JoomDOCRequest::getPath();
        if ($this->getModel()->searchIdByPath($path))
            // file already has document
            return false;
        if (!JoomDOCAccessDocument::create(JoomDOCFileSystem::getParentPath($path)))
            // parent document or global config doesn't allow add document
            return false;
        return true;
    }

    /**
     * Edit document.
     */
    public function edit($key = null, $urlVar = null) {
        if (JFactory::getApplication()->isSite())
            JRequest::setVar('id', $this->getModel()->searchIdByPath(JoomDOCRequest::getPath()), 'post');
        parent::edit();
    }

    /**
     * Allow edit document.
     */
    protected function allowEdit($data = array(), $key = 'id') {
        if (JFactory::getApplication()->isSite())
            $document = $this->getModel()->getItem($this->getModel()->searchIdByPath(JoomDOCRequest::getPath()));
        else
            $document = $this->getModel()->getItem();
        return JoomDOCAccessDocument::canEdit($document);
    }

    /**
     * Allow save document.
     */
    protected function allowSave($data, $key = 'id') {
        $document = $this->getModel()->getItem(JRequest::getInt('id'));
        // can create new document
        if (!$document->id && JoomDOCAccessDocument::create(JoomDOCRequest::getPath()))
            return true;
        // can edit exists document
        if ($document->id && JoomDOCAccessDocument::canEdit($document))
            return true;
        return false;
    }
    /**
     * Download file by relative path or document full alias.
     */
    public function download () {
        JoomDOCFileSystem::download(JFactory::getApplication()->isSite());
    }
    /**
     * Add items to copy into clipboard.
     */
    public function copy () {
        JoomDOCFileSystem::setOperation(JOOMDOC_OPERATION_COPY);
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }
    /**
     * Add items to move into clipboard.
     */
    public function move () {
        JoomDOCFileSystem::setOperation(JOOMDOC_OPERATION_MOVE);
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }
    /**
     * Do copy/move into current folder.
     */
    public function paste () {      
        JoomDOCFileSystem::doOperation();
        //display "added X files/folders" only if not 0
        $this->setRedirect(JoomDOCRoute::viewDocuments(), ($refreshed = JoomDOCFileSystem::refresh())>1 ? JText::sprintf('JOOMDOC_REFRESHED', $refreshed) : null);
    }
    /**
     * Reset operation from clipboard.
     */
    public function reset () {
        JoomDOCFileSystem::resetOperation();
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }
}
?>