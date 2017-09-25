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

jimport('joomla.application.component.controlleradmin');

class JoomDOCControllerDocuments extends JoomDOCControllerAdmin {

    /**
     * Get document model.
     *
     * @return JoomDOCModelDocument
     */
    public function getModel($name = JOOMDOC_DOCUMENT, $prefix = JOOMDOC_MODEL_PREFIX, $config = array('ignore_request' => true)) {
        return JModelLegacy::getInstance($name, $prefix, $config);
    }

    /**
     * Upload file from request in current folder.
     *
     * @return void
     */
    public function uploadFile () {
        JoomDOCFileSystem::upload();
    }

    /**
     * Delete document.
     */
    public function delete () {
        // set document ID into request
        JRequest::setVar('cid', array($this->getModel()->searchIdByPath(JoomDOCRequest::getPath())));
        // move token from GET into POST
        
        if (JOOMDOC_ISJ3){ //fix for J3 API. JRequest::setVar not works, because JSession::checkToken uses input object...
        	$app = JFactory::getApplication();
        	$app->input->post->set(JRequest::getVar('token', '', 'get', 'string'), 1);
        }
        
        JRequest::setVar(JRequest::getVar('token', '', 'get', 'string'), 1, 'post');
        parent::delete();
        $this->setRedirect(JoomDOCRoute::viewDocuments(JoomDOCFileSystem::getParentPath(JoomDOCRequest::getPath()), false));
    }

        
    public function updatemootree()
    {
    	$app = JFactory::getApplication();
    	$parent = JoomDOCRequest::getPath();
    	
   		$modelDocuments = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX);
    		
   		$root = JoomDOCFileSystem::getFolderContent(JoomDOCFileSystem::getFullPath($parent), '', 1, true, false);
   		$modelDocuments->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_PATHS), $root->getPaths());
    		
   		$root->setDocuments($modelDocuments->getItems());
   		$root->reorder(null, JOOMDOC_ORDER_PATH, JOOMDOC_ORDER_ASC, 0, PHP_INT_MAX);
    		
   		JRequest::setVar('ajax', true);
   		$folders = JHtml::_('joomdoc.folders', $root, $parent);
    	
		JHtml::_('joomdoc.mootree', $folders, $parent, false, true);
    }
    
    /**
     * Create new subfolder in current parent folder.
     *
     * @return void
     */
    public function newFolder () {
        $app = JFactory::getApplication();
        $success = JoomDOCFileSystem::newFolder();
        if ($success && JRequest::getInt('doccreate')) {
            $this->setRedirect(JoomDOCRoute::addDocument($app->getUserState('com_joomdoc.new.folder')));
        } else {
            $this->setRedirect(JRoute::_(JoomDOCRoute::viewDocuments(JoomDOCRequest::getPath(), false), false));
        }
    }
}
?>