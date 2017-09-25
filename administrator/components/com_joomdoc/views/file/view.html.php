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

class JoomDOCViewFile extends JoomDOCView {
    /**
     * File which versions are displayed.
     * 
     * @var JoomDOCFile
     */
    var $item;
    /**
     * List of file versions.
     *
     * @var array
     */
    var $data;
    /**
     * Browse table filter
     *
     * @var JObject
     */
    var $filter;
    /**
     * File last version document.
     *
     * @var stdClass
     */
    var $document;
    /**
     * ACL levels.
     *
     * @var JoomDOCAccessHelper
     */
    var $access;
    /**
     * Maximum, published File Version.
     *
     * @var int
     */
    var $maxVersion;

    /**
     * Display browse table of file versions with extended filter.
     *
     * @param string $tpl used template
     */
    public function display ($tpl = null) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $modelFile = $this->getModel();
        /* @var $modelFile JoomDOCModelFile */
        $this->filter = new JObject();
        $this->filter->path = JoomDOCRequest::getPath();
        $this->item = new JoomDOCFile(JoomDOCFileSystem::getFullPath($this->filter->path));
        $sessionPrefix = JoomDOCRequest::getSessionPrefix(true);
        
        $this->filter->offset = $mainframe->getUserStateFromRequest($sessionPrefix . 'offset', 'limitstart', 0, 'int');
        //var_dump($sessionPrefix);
        $this->filter->limit = $mainframe->getUserStateFromRequest($sessionPrefix . 'limit', 'limit', 10, 'int');
        //var_dump($this->filter->limit);
        $this->filter->listOrder = $mainframe->getUserStateFromRequest($sessionPrefix . 'listOrder', 'filter_order', 'version', 'string');
        //var_dump($this->filter->listOrder);
        $this->filter->listDirn = $mainframe->getUserStateFromRequest($sessionPrefix . 'listDirn', 'filter_order_Dir', 'asc', 'string');
        //var_dump($this->filter->listDirn);
        $this->filter->uploader = $mainframe->getUserStateFromRequest($sessionPrefix . 'uploader', 'uploader', '', 'string');
        //var_dump($this->filter->uploader);
        $this->filter->state = $mainframe->getUserStateFromRequest($sessionPrefix . 'state', 'state', 0, 'int');
        //var_dump($this->filter->state);
        $this->data = $modelFile->getData($this->filter);
        $this->document = $modelFile->getDocument($this->filter);
        $this->item->document = $this->document;
        $this->maxVersion = $modelFile->getMaxVersion($this->filter->path);
        $this->access = new JoomDOCAccessHelper($this->item);
        if (!JoomDOCAccessFileSystem::viewFileInfo($this->document ? $this->document->id : null, $this->filter->path)) {
            JError::raiseError(403, JText::sprintf('JOOMDOC_VIEW_FILE_INFO_NOT_ALLOW'));
        }
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add page main toolbar.
     *
     * @return void
     */
    protected function addToolbar () {
        JToolBarHelper::title(JText::sprintf('JOOMDOC_FILE_PATH', $this->filter->path), 'file');
        if ($this->access->canManageVersions) {
            JToolBarHelper::deleteList('JOOMDOC_ARE_YOU_SURE_TRASH_VERSION', JoomDOCHelper::getTask(JOOMDOC_FILE, JOOMDOC_TASK_TRASH), 'JTOOLBAR_TRASH');
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_FILE, JOOMDOC_TASK_RESTORE), JOOMDOC_ISJ3 ? 'refresh' : JOOMDOC_TASK_RESTORE, JOOMDOC_TASK_RESTORE, JText::_('JTOOLBAR_RESTORE'));
            JToolBarHelper::divider();
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_FILE, JOOMDOC_TASK_REVERT), JOOMDOC_ISJ3 ? 'redo' : JOOMDOC_TASK_REVERT, JOOMDOC_TASK_REVERT, JText::_('JTOOLBAR_REVERT'));
            JToolBarHelper::divider();
        }
        if (JOOMDOC_ISJ3) //has icon in J3
       		JToolBarHelper::cancel(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_CANCEL), 'JTOOLBAR_CLOSE');
        else
        	JToolBarHelper::back('Back', JRoute::_(JoomDOCRoute::viewDocuments()));
    }
}
?>