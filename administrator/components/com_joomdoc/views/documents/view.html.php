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

jimport('joomla.html.pagination');

class JoomDOCViewDocuments extends JoomDOCView {
    /**
     * Current viewed folder.
     *
     * @var JoomDOCFolder
     */
    protected $root;
    /**
     * Filter folder/file name.
     *
     * @var string
     */
    protected $filter;
    /**
     * Documents list for listed files/folders.
     *
     * @var array
     */
    protected $documents;

    /**
     * Request filter state.
     *
     * @var JObject
     */
    public $state;
    /**
     * Root folder access rules.
     *
     * @var JoomDOCAccessHelper
     */
    public $access;
    /**
     * Select folder is in doc root.
     *
     * @var boolean
     */
    public $inRoot;
    /**
     * Page listing.
     *
     * @var JPagination
     */
    public $pagination;
    /**
     * Search criteria.
     *
     * @var JObject
     */
    public $search;

    /**
     * Display page with folder content.
     *
     * @param $tpl used template
     * @return void
     */
    public function display ($tpl = null) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */
        $model = $this->getModel();
        /* @var $model JoomDOCModelDocuments */
        $document = JFactory::getDocument();
        /* @var $document JDocumentHTML */
        if ($this->getLayout() != 'modal') {
            //$mainframe->enqueueMessage(JText::_('JOOMDOC_KEEP_FLAT'));        
        }

        // relative path from request or user session
        $path = JoomDOCRequest::getPath();
        if ($path == JText::_('JOOMDOC_ROOT'))
            $path = '';

        // convert to absolute path
        $path = JoomDOCFileSystem::getFullPath($path);

        //if folder not exists (e.g. was renamed), fallback to root
        if (!JFolder::exists($path))
        	$path = JoomDOCFileSystem::getFullPath('');
        
        $this->filter = $mainframe->getUserStateFromRequest(JoomDOCRequest::getSessionPrefix() . 'filter', 'filter', '', 'string');
        $this->root = JoomDOCFileSystem::getFolderContent($path, '');

        // control if select folder is subfolder of docroot
        if ((!JoomDOCFileSystem::isSubFolder($path, $config->docroot) || $this->root === false) && $config->docroot !== false)
            JError::raiseError(403, JText::_('JOOMDOC_UNABLE_ACCESS_FOLDER'));

        // search filter setting
        $this->search = new JObject();
        $this->search->keywords = $this->filter;
        // search areas (search everywhere - don't use frontend detail setting)
        $this->search->areaTitle = $this->search->areaText = $this->search->areaFull = true;
        $this->search->areaMeta = false;
        // looking for any word
        $this->search->type = JOOMDOC_SEARCH_ANYKEY;

        $model->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_PATHS), $this->root->getPaths());
        $model->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_SEARCH), $this->search);
        $model->setState(JOOMDOC_FILTER_STATE, $mainframe->getUserStateFromRequest(JoomDOCRequest::getSessionPrefix() . 'state', 'state', JOOMDOC_STATE_PUBLISHED, 'int'));

        $this->documents = $model->getItems();
        $this->state = $model->getState();
        $this->pagination = new JPagination($model->getTotal(), $this->state->get(JOOMDOC_FILTER_START), $this->state->get(JOOMDOC_FILTER_LIMIT));

        $this->access = new JoomDOCAccessHelper($this->root);

        $this->root->setDocuments($this->documents);
        $this->root->reorder($this->state->get(JOOMDOC_FILTER_ORDERING), $this->state->get(JOOMDOC_FILTER_ORDERING), $this->state->get(JOOMDOC_FILTER_DIRECTION), $this->pagination->limitstart, $this->pagination->limit, $this->pagination->total, $config->foldersFirstAdmin);

        // control permissions to access folder
        if (!$this->access->canEnterFolder) {
            $mainframe->setUserState('joomdoc_documents_path', null);
            JError::raiseError(403, JText::_('JOOMDOC_UNABLE_ACCESS_FOLDER'));
        }

        $this->addToolbar();

        JoomDOCHelper::setSubmenu(JOOMDOC_DOCUMENTS, true);
        JoomDOCHelper::clipboardInfo();
        JoomDOCHelper::folderInfo($this->access->absolutePath);

        parent::display($tpl);
    }

    /**
     * Add page main toolbar.
     *
     * @return void
     */
    protected function addToolbar () {
        $bar = JToolBar::getInstance('toolbar');
        /* @var $bar JToolBar */
        JToolBarHelper::title(JText::_('JOOMDOC_DOCUMENTS'), 'documents');
        if ($this->access->canEditStates) {
            JToolBarHelper::publish(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_PUBLISH));
            JToolBarHelper::unpublish(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_UNPUBLISH));
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_CHECKIN), 'checkin', '', 'JTOOLBAR_CHECKIN', true);
        } else {
            $bar->appendButton('Standard', 'publish', 'JTOOLBAR_PUBLISH');
            $bar->appendButton('Standard', 'unpublish', 'JTOOLBAR_UNPUBLISH');
            $bar->appendButton('Standard', 'checkin', 'JTOOLBAR_CHECKIN');
        }
        JToolBarHelper::divider();
        if ($this->access->canCopyMove && !JoomDOCFileSystem::haveOperation()) {
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_COPY), 'copy', '', 'JTOOLBAR_COPY', true);
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_MOVE), 'move', '', 'JTOOLBAR_MOVE', true);
        } else {
            //$bar->appendButton('Standard', 'copy', 'JTOOLBAR_COPY');
            //$bar->appendButton('Standard', 'move', 'JTOOLBAR_MOVE');
        }
        if ($this->access->canCopyMove && JoomDOCFileSystem::haveOperation()) {
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_PASTE), 'save', '', 'JTOOLBAR_PASTE', false);
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_RESET), 'remove', '', 'JTOOLBAR_RESET', false);
        } else {
            //$bar->appendButton('Standard', 'save', 'JTOOLBAR_PASTE');
            //$bar->appendButton('Standard', 'remove', 'JTOOLBAR_RESET');
        }
        JToolBarHelper::divider();
        // Document delete
        //if ($this->access->canDeleteDocs)
            //$bar->appendButton('Confirm', 'JOOMDOC_ARE_YOU_SURE_DELETE_DOCUMETS', 'docs-delete', 'JOOMDOC_DELETE_DOCUMENT', JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_DELETE), true);
        //else
            //$bar->appendButton('Standard', 'docs-delete', 'JOOMDOC_DELETE_DOCUMENT');
        // Item delete
        
        if ($this->access->canDeleteFile)
            JToolBarHelper::deleteList('JOOMDOC_ARE_YOU_SURE_DELETE_ITEMS', JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_DELETEFILE), 'JTOOLBAR_DELETE');
        else
            $bar->appendButton('Standard', 'delete', 'JOOMDOC_DELETE_ITEM');
        if (JoomDOCHelper::trashedItemsCount() >= 1) {
        if ($this->access->canDeleteDocs && $this->access->canDeleteFile)
            $bar->appendButton('Confirm', 'JOOMDOC_ARE_YOU_SURE_EMPTY_TRASH', 'trash', 'JTOOLBAR_EMPTY_TRASH', JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_TRASH), false);
        else
            $bar->appendButton('Standard', 'trash', 'JTOOLBAR_TRASH');
        }
        if (JoomDOCAccessFileSystem::refresh()) {
        	JToolBarHelper::divider();
            JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_REFRESH), 'refresh', '', 'JTOOLBAR_REFRESH', false);
            JHtml::_('joomdoc.tooltip', 'toolbar-refresh', 'JTOOLBAR_REFRESH', 'JOOMDOC_REFRESH_TIP');
                        JToolBarHelper::custom(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_FLAT), 'reflat', '', 'JOOMDOC_REFLAT', false);
            JHtml::_('joomdoc.tooltip', 'toolbar-reflat', 'JOOMDOC_REFLAT', 'JOOMDOC_REFLAT_TIP');
        }
        if (JoomDOCAccess::admin()) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
        }
            }
}
?>