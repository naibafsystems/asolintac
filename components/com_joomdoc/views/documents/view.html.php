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

jimport('joomla.html.parameter');
jimport('joomla.html.pagination');

jimport('cms.toolbar.toolbar');
$tlb = JPATH_ADMINISTRATOR . '/includes/toolbar.php';
if (JFile::exists($tlb)) {
    require_once $tlb;
}

class JoomDOCViewDocuments extends JoomDOCView
{

    /**
     * Current viewed folder.
     *
     * @var JoomDOCFolder
     */
    protected $root;
    /**
     * Root access
     *
     * @var JoomDOCAccessHelper
     */
    protected $access;
    /**
     * Filter folder/file name.
     *
     * @var string
     */
    protected $filter;
    /**
     * Request filter state.
     *
     * @var JObject
     */
    public $state;
    /**
     * Support for page listing.
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
    public function display($tpl = null)
    {

        $mainframe = JFactory::getApplication();
        /* @var $mainframe JSite */
        $document = JFactory::getDocument();
        /* @var $documents JDocumentHTML */
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */

        $modelDocument = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $modelDocument JoomDOCModelDocument */
        $modelDocuments = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_SITE_PREFIX);
        /* @var $modelDocuments JoomDOCSiteModelDocuments */
        $modelFile = JModelLegacy::getInstance(JOOMDOC_FILE, JOOMDOC_MODEL_PREFIX);
        /* @var $modelFile JoomDOCModelFile */

        $this->filter = $this->getLayout() == 'modal' ? $mainframe->getUserStateFromRequest(JoomDOCRequest::getSessionPrefix() . 'filter', 'filter', '', 'string') : ''; 
        
        $path = JoomDOCRequest::getPath();
        
        // convert to absolute path, if path si empty use document root path
        $path = $path ? JoomDOCFileSystem::getFullPath($path) : $config->path;

        // request path value isn't subfolder of document root
        if (!JoomDOCFileSystem::isSubFolder($path, $config->path)) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        
        $this->searchablefields = $this->get('searchablefields');
        $this->publishedfields = $this->get('publishedfields');
        
        foreach ($this->searchablefields as $field)
        	$field->name = 'joomdoc_field' . $field->id;
		
        if ($config->useSearch || $this->getLayout() == 'modal') {
            $this->search = new JObject();
            // load setting from request or Joomla session storage (server session, database, apc etc.)
            $this->search->search = $this->getSearchParam('joomdoc_search', 0, 'int');
            if ($config->searchKeyword == 1) {
                $this->search->keywords = $this->getSearchParam('joomdoc_keywords', $this->filter, 'string');
            } elseif ($config->searchKeyword == 2) {
                // separate keywords for each area
                $this->search->keywords_title = $this->getSearchParam('joomdoc_keywords_title', $this->filter, 'string');
                $this->search->keywords_text = $this->getSearchParam('joomdoc_keywords_text', $this->filter, 'string');
                $this->search->keywords_meta = $this->getSearchParam('joomdoc_keywords_meta', $this->filter, 'string');
                $this->search->keywords_full = $this->getSearchParam('joomdoc_keywords_full', $this->filter, 'string');
                foreach ($this->searchablefields as $field) {
                    if ($field->type == JOOMDOC_FIELD_TEXT || $field->type == JOOMDOC_FIELD_TEXTAREA || $field->type == JOOMDOC_FIELD_EDITOR) {
                        $this->search->set(('keywords_field' . $field->id), $this->getSearchParam(('joomdoc_keywords_field' . $field->id), $this->filter, 'string'));
                    }
                }
            }
            // selected parent (folder)
            $this->search->parent = $config->searchShowParent ? $this->getSearchParam('path', '', 'string') : '';
            // path to load items list, if not select parent use document root
            $this->search->path = JoomDOCFileSystem::getFullPath($this->search->parent);
            // search areas (document title or file path), text and metadata, file content
            $this->search->areaTitle = $config->searchShowTitle ? $this->getSearchParam('joomdoc_area_title', 1, 'int', true, 'joomdoc_search') : 0;
            $this->search->areaText = $config->searchShowText ? $this->getSearchParam('joomdoc_area_text', 1, 'int', true, 'joomdoc_search') : 0;
            $this->search->areaMeta = $config->searchShowMetadata ? $this->getSearchParam('joomdoc_area_meta', 1, 'int', true, 'joomdoc_search') : 0;
            $this->search->areaFull = $config->searchShowFulltext ? $this->getSearchParam('joomdoc_area_full', 1, 'int', true, 'joomdoc_search') : 0;
            // searching type (any/all word, complet phrase, regular expresion
            $this->search->type = $this->getSearchParam('joomdoc_type', $config->searchDefaultType, 'int');
            // ordering
            $this->search->ordering = $this->getSearchParam('joomdoc_ordering', $config->searchDefaultOrder, 'string');

            foreach ($this->searchablefields as $field) {
            	if ($field->type == JOOMDOC_FIELD_TEXT)
            		$this->search->fields[$field->id] = array('type' => $field->type, 'value' => $this->getSearchParam($field->name, 1, 'int', true, 'joomdoc_search'));
            	elseif ($field->type == JOOMDOC_FIELD_CHECKBOX || $field->type == JOOMDOC_FIELD_MULTI_SELECT || $field->type == JOOMDOC_FIELD_SUGGEST)
            		$this->search->fields[$field->id] = array('type' => $field->type, 'value' => $this->getSearchParam($field->name, array(), 'array'));
            	else
            		$this->search->fields[$field->id] = array('type' => $field->type, 'value' => $this->getSearchParam($field->name, '', 'string'));
            }
            
            // set ordering from search setting
            switch ($this->search->ordering) {
                case JOOMDOC_ORDER_NEWEST:
                    /* Newest items */
                    // files order upload date and documents order publish up date descending
                    $documentOrdering = JOOMDOC_ORDER_PUBLISH_UP;
                    $fileOrdering = JOOMDOC_ORDER_UPLOAD;
                    $orderingDirection = JOOMDOC_ORDER_DESC;
                    break;
                case JOOMDOC_ORDER_OLDEST:
                    /* Oldest items */
                    // files order upload date and documents order publish up date ascending
                    $documentOrdering = JOOMDOC_ORDER_PUBLISH_UP;
                    $fileOrdering = JOOMDOC_ORDER_UPLOAD;
                    $orderingDirection = JOOMDOC_ORDER_ASC;
                    break;
                case JOOMDOC_ORDER_HITS:
                    /* Most popular (downloaded) */
                    // files order hits descending
                    $documentOrdering = null;
                    $fileOrdering = JOOMDOC_ORDER_HITS;
                    $orderingDirection = JOOMDOC_ORDER_DESC;
                    break;
                case JOOMDOC_ORDER_TITLE:
                    /* Alphabetical */
                    // files order path and documents order title ascending
                    $documentOrdering = JOOMDOC_ORDER_TITLE;
                    $fileOrdering = JOOMDOC_ORDER_PATH;
                    $orderingDirection = JOOMDOC_ORDER_ASC;
                    break;
            }
        }

        $searchActive = $config->useSearch && $this->search->search;
        
        if (!$searchActive) {
            // if search isnt't set use ordering from configuration
            $documentOrdering = $config->documentOrdering;
            $fileOrdering = $config->fileOrdering;
            $orderingDirection = $config->orderingDirection;
        }
        
        // get content of selected folder
        $this->root = JoomDOCFileSystem::getFolderContent($path, '', $searchActive);
        
        if (JoomDOCFileSystem::isFolder($this->root)) {
            // selected path is folder
            $modelDocuments->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_PATHS), $this->root->getPaths(false));
            // get child documents
            $modelDocuments->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_SEARCH), $config->useSearch || $this->getLayout() == 'modal' ? $this->search : null);
            $this->documents = $modelDocuments->getItems($searchActive);
            $this->listfields = $modelDocuments->getListFields();
            $this->state = $modelDocuments->getState();
            // add documents to given subfolders and files
            $this->root->setDocuments($this->documents);
            $this->access = new JoomDOCAccessHelper($this->root);
            // control permissions to access folder
            if (!$this->access->canEnterFolder) {
                JError::raiseError(403, JText::_('JOOMDOC_UNABLE_ACCESS_FOLDER'));
            }
            $this->pagination = new JPagination($modelDocuments->getTotal($searchActive), $mainframe->getUserStateFromRequest('com_joomdoc.documents.limitstart', 'limitstart', 0, 'int'), $mainframe->getUserStateFromRequest('com_joomdoc.documents.limit', 'limit', $mainframe->getCfg('list_limit'), 'int'));
            // reorder
            $this->root->reorder($documentOrdering, $fileOrdering, $orderingDirection, $this->pagination->limitstart, $this->pagination->limit, $this->pagination->total, $config->foldersFirstSite);
            // set root parent
            $this->root->parent = $modelDocument->getParent(JoomDOCFileSystem::getParentPath($this->root->getRelativePath()));
            $this->root->document = $modelDocument->getItemByPath($this->root->getRelativePath());

        } elseif (JoomDOCFileSystem::isFile($this->root)) {
            // use different layout
            $this->setLayout('file');
            // search document by path
            $this->root->document = $modelDocument->getItemByPath($this->root->getRelativePath());
            $this->publishedfields = $modelDocument->getPublishedFields();
            $this->access = new JoomDOCAccessHelper($this->root);
            // document unpublished
            $this->root->parent = $modelDocument->getParent(JoomDOCFileSystem::getParentPath($this->root->getRelativePath()));
                    } else {
            JError::raiseError(404, JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'));
        }

        // control root access
        if ($this->access->docid) {
            // item with document
            if (!$this->access->canAnyEditOp && $this->root->document->published == JOOMDOC_STATE_UNPUBLISHED) {
                // root unpublished and user hasn't admin rights
                JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            }
            if ($this->root->document->state == JOOMDOC_STATE_TRASHED) {
                // root trashed
                JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
            }
        } elseif (isset($this->root->document->file_state)) {
            // item without document but with file
            if ($this->root->document->file_state == JOOMDOC_STATE_TRASHED) {
                // file is trashed
                JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
            }
        } elseif (!$this->access->inRoot && !$searchActive && $this->getLayout() != 'modal') {
            // item without file can be total root but it's not root
            JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // take a configuration of an active menu item
        $active = $mainframe->getMenu()->getActive();
        $this->pageHeading = $this->pageclassSfx = '';
        if ($active) {
            if ($active->params->get('show_page_heading')) {
                $this->pageHeading = JString::trim($active->params->get('page_heading'));
                if (!$this->pageHeading) {
                    $this->pageHeading = JString::trim($active->params->get('page_title'));
                }
            }
            $this->pageclassSfx = JString::trim($active->params->get('pageclass_sfx'));
            $titles[] = JString::trim($active->params->get('page_title'));
            $metakeywords[] = JString::trim($active->params->get('menu-meta_keywords'));
            $metadescriptions[] = JString::trim($active->params->get('menu-meta_description'));
        }
        
        // take candidates for metadata sort on priority
        if ($this->access->docid AND ($this->root->document->published==JOOMDOC_STATE_PUBLISHED)) { //use document only if published. (but for owner is published always).
            // from document data
            $params = new JRegistry($this->root->document->params);
            $titles[] = JString::trim($this->root->document->title);
            $metakeywords[] = JString::trim($params->get('metakeywords'));
            $metadescriptions[] = JString::trim($params->get('metadescription'));
            $metadescriptions[] = JoomDOCHelper::getMetaDescriptions($this->root->document->description);
        }
        
        // default candidates
        $titles[] = $this->access->name;
        $titles[] = $config->defaultTitle;
        $metakeywords[] = $config->defaultMetakeywords;
        $metadescriptions[] = $config->defaultMetadescription;

        // set meta data from candidates acording to priority

        // set meta keywords
        $document->setMetaData('keywords', JoomDOCHelper::getFirstNoEmpty($metakeywords));
        // set page title
        $document->setTitle(JoomDOCHelper::getCompletTitle(JoomDOCHelper::getFirstNoEmpty($titles)));
        // set head meta description
        $document->setDescription(JoomDOCHelper::getFirstNoEmpty($metadescriptions));

        $modelDocuments->setPathway($this->root->getRelativePath());
        
        if ($this->access->canCopyMove){            
            JoomDOCHelper::clipboardInfo();
        }        
        
        parent::display($tpl);
    }

    /**
     * Get param from search form (module or component).
     *
     * @param string $name field name from component (module prefix is tested automaticaly)
     * @param mixed $default default value if field isn't neither in request nor session
     * @param string $type data type int/string/bool
     * @param booelan $checkbox field is checkbox
     * @param string $tester field to test if form is submited
     * @return mixed request, session or default value
     */
    function getSearchParam($name, $default, $type, $checkbox = false, $tester = '')
    {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */

        /* first look for value from module
         module field has prefix mod_ to prevent colision */
        $module = JRequest::getVar('mod_' . $name, null, 'default', $type);
        if (!is_null($module)) {
            $mainframe->setUserState($name, $module);
            return $module;
        }
        // search for value from component
        if ($checkbox) {
            return JoomDOCRequest::getCheckbox($name, $tester, $default);
        }
        return $mainframe->getUserStateFromRequest($name, $name, $default, $type);
    }
    
    public function hasFields() {
        foreach ($this->publishedfields as $field) {
            if ($this->showField($field)) {
                return true;
            }
        }
        return false;
    }

    public function showField($field) {
        $name = 'field' . $field->id;
        if (isset($this->root->document->$name)) {
            return JHtml::_('joomdoc.showfield', $field, $this->root->document);
        }
        return null;
    }

}
?>