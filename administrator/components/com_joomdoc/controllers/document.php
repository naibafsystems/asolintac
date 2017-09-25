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
     * Rename file/folder.
     */
    public function rename () {
        $renamePath = JString::trim(JRequest::getString('renamePath'));
         
        if (JoomDOCAccessFileSystem::rename(false, $renamePath)) {
        	
            if ($success = JoomDOCFileSystem::rename($renamePath, JString::trim($newName = JRequest::getString('newName')))){
	
            	//if renamed directory, do refresh, else it will appear empty
	            $newPath = ($parentPath = JoomDOCFileSystem::getParentPath($renamePath)) ? $parentPath.DIRECTORY_SEPARATOR.JRequest::getString('newName') : $newName;

	            if (JFolder::exists(JoomDOCFileSystem::getFullPath($newPath))){
	            	$mainfrane = JFactory::getApplication();
	           	 	$mainfrane->enqueueMessage(JText::sprintf('JOOMDOC_REFRESHED', JoomDOCFileSystem::refresh()));
	            }
            }
            
            $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::_($success ? 'JOOMDOC_RENAME_SUCCESS' : 'JOOMDOC_RENAME_FAILED'), $success ? 'message' : 'error');
        } else
            JError::raiseError(403, JText::_('JOOMDOC_UNABLE_RENAME'));
    }
}
?>