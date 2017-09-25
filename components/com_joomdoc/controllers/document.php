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

jimport('joomla.application.component.controllerform');

class JoomDOCControllerDocument extends JoomDOCControllerForm {

    /**
     * Complet URL to redirect document detail.
     */
    public function getRedirectToListAppend () {
        // after form submit document data are in request array jform
        $jform = JRequest::getVar('jform');
        return JoomDOCRoute::viewDocuments($jform['path'], false,true);
    }

    /**
     * Delete selected folders/files.
     *
     * @return void
     */
    public function deleteFile () {
        JoomDOCFileSystem::delete();
    }

    /**
     * Publish document.
     */
    public function publish () {
        $this->setPublish(JOOMDOC_STATE_PUBLISHED, 'JOOMDOC_PUBLISHED');
    }
    /**
     * Unpublish document.
     */
    public function unpublish () {
        $this->setPublish(JOOMDOC_STATE_UNPUBLISHED, 'JOOMDOC_UNPUBLISHED');
    }
    /**
     * Set document publish state.
     *
     * @param int $value new state value
     * @param string $msg message after success
     */
    public function setPublish ($value, $msg) {
        $path = JoomDOCRequest::getPath();
        $success = $this->getModel()->setPublish($path, $value);
        $this->setRedirect(JRoute::_(JoomDOCRoute::viewDocuments(JoomDOCFileSystem::getParentPath($path))), JText::_($success ? $msg : 'JOOMDOC_UNABLE_SET_STATE'), $success ? 'message' : 'error');
    }
    
    /**
     * Add items to copy into clipboard.
     */
    public function copy () {
        JoomDOCFileSystem::setOperation(JOOMDOC_OPERATION_COPY);
        $this->setRedirect(JoomDOCRoute::viewDocuments(JoomDOCRequest::getPath(), false));
    }
    /**
     * Add items to move into clipboard.
     */
    public function move () {
        JoomDOCFileSystem::setOperation(JOOMDOC_OPERATION_MOVE);
        $this->setRedirect(JoomDOCRoute::viewDocuments(JoomDOCRequest::getPath(), false));
    }
    /**
     * Do copy/move into current folder.
     */
    public function paste () {      
        JoomDOCFileSystem::doOperation();
        //display "added X files/folders" only if not 0
        $this->setRedirect(JoomDOCRoute::viewDocuments(JoomDOCRequest::getPath(), false), ($refreshed = JoomDOCFileSystem::refresh())>1 ? JText::sprintf('JOOMDOC_REFRESHED', $refreshed) : null);
    }
    /**
     * Reset operation from clipboard.
     */
    public function reset () {
        JoomDOCFileSystem::resetOperation();
        $this->setRedirect(JoomDOCRoute::viewDocuments(JoomDOCRequest::getPath(), false));
    }
}
?>