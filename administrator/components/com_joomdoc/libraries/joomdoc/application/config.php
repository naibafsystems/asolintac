<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class AUpdateHelper {

    /**
     * Update URL for ARTIO Updater by adding or updating the Download ID if set
     * 
     * @param string $componentName
     * @param string $downloadID
     * @return boolean
     */
    static function setUpdateLink($componentName, $downloadID)
    {
        $db = JFactory::getDbo();
        
        // look for update record in DB        
        $query = $db->getQuery(true);
        $query->select('location')->from('#__update_sites')->where('name = '.$db->quote($componentName));
        $db->setQuery($query);
        $origLocation = $location = $db->loadResult();
        
        $location_match = array();
        // if some ID is already set, update or remove it
        if (preg_match("/(-([A-Za-z0-9]*)).xml/", $location, $location_match)) {
            // update existing download ID
            if (strlen($downloadID)) {
                $location = str_replace($location_match[0], '-' . $downloadID.'.xml', $location);
            // or remove it, if not set
            } else {
                $location = str_replace($location_match[0], '.xml', $location);
            }
        // if not set yet but just entered, attach it
        } else if (strlen($downloadID)) {
            $location = str_replace('.xml', '-'.$downloadID.'.xml', $location);        
        }
        
        // if location string has changed, update it in DB
        if ($location != $origLocation) {
            $query = "UPDATE #__update_sites SET location = " . $db->quote($location)." WHERE name = " . $db->quote($componentName);
            $db->setQuery($query);
            // write to DB
            if (!$db->query()) {
                $this->setError($db->stderr(true));
                return false;
            }
        }
        return true;
    }

}

class JoomDOCConfig extends JObject {
    /**
     * Download ID.
     *
     * @var string
     */
    public $downloadId;
    /**
     * Filesystem document root.
     *
     * @var string
     */
    public $docroot;
    /**
     * Default document root title.
     *
     * @var string
     */
    public $defaultTitle;
    /**
     * Default meta keywords. If document hasn't own meta keywords use them instead.
     *
     * @var string
     */
    public $defaultMetakeywords;
    /**
     * Default meta description. If document hasn't own meta description use them instead.
     *
     * @var string
     */
    public $defaultMetadescription;
    /**
     * Default document root description.
     *
     * @var string
     */
    public $defaultDescription;
    /**
     * Root path for menu item
     *
     * @var string
     */
    public $path;
    /**
     * Show folder subfolders
     *
     * @var string
     */
    public $showSubfolders;
    /**
     * Show subfolders/subfiles without document.
     *
     * @var int 0/1 - false/true
     */
    public $filesWithoutDoc;
    /**
     * Document ordering.
     *
     * @var string
     */
    public $documentOrdering;
    /**
     * Files without documents ordering.
     *
     * @var string
     */
    public $fileOrdering;
    /**
     * Ordering direction.
     *
     * @var string asc/desc
     */
    public $orderingDirection;
    /**
     * Show file mime/type icon.
     *
     * @var int
     */
    public $showFileicon;
    /**
     * Show file size.
     *
     * @var int
     */
    public $showFilesize;
    /**
     * Show documents date create.
     *
     * @var int
     */
    public $showCreated;
    /**
     * Show documents modified date.
     *
     * @var int
     */
    public $showModified;
    /**
     * Show documents hits.
     *
     * @var string
     */
    public $showHits;
    /**
     * Version file. Save date upload and who upload.
     *
     * @var boolean
     */
    public $versionFile;
    /**
     * Display list of file version on frontend.
     *
     * @var boolean
     */
    public $versionFileFrontend;
    /**
     * Version document. Save history of description and settings.
     *
     * @var boolean
     */
    public $versionDocument;
    /**
     * Version note field in document editing can be empty or no.
     *
     * @var boolean
     */
    public $versionRequired;
    /**
     * Trash old versions in given schedule.
     * @var array(days, months, years, secret)
     */
    public $versionTrash;
    /**
     * Display icon document is favorite on frontend.
     *
     * @var boolean
     */
    public $displayFavorite;

    /**
     * Allow use webdav for user group.
     *
     * @var mixed
     */
    public $webdavAllow;

    /**
     * WebDav type.
     * 1: Apache Mod Dav 
     * 2: SabreDav
     * @var int 
     */
    public $webdavType;
    
    /**
     * Usergroup who has some editing right (edit document, edit document state, edit webdav, delete folder/file or delete document) can access unpublished documents or documents in different view level.
     * @var int 1/0 
     */
    public $accessHandling;
    /**
     * Show open file link on file list bellow file detail.
     *
     * @var boolean
     */
    public $showOpenFile;
    /**
     * Show download file link on file list bellow file detail.
     *
     * @var boolean
     */
    public $showDownloadFile;
    /**
     * Show open folder link on file list bellow folder detail.
     *
     * @var boolean
     */
    public $showOpenFolder;

    /**
     * Show files documents description in documents list.
     *
     * @var boolean
     */
    public $showFileDesc;
    /**
     * Show folders documents description in documents list.
     *
     * @var boolean
     */
    public $showFolderDesc;
    /**
     * Show link below document title to show license in popup window
     * 
     * @var boolean
     */
    public $showLicense;
    /**
     * Display or hide powered signature.
     *
     * @var boolean
     */
    public $displaySignature;
    /**
     * If turn on folder seted as root of this menu item is used as virtual folder. It means that relative path to subfolders and subfiles is show from this folder without parent path.
     *
     * @var boolean
     */
    public $virtualFolder;
    /**
     * Name of folder with file icons.
     *
     * @var string
     */
    public $iconTheme;
    
    public $icoTheme;
    
    public $docLayout;
    /**
     * Option completely disable WebDav support.
     *
     * @var boolean
     */
    public $useWebdav;
    /**
     * Option to completely disable symbolic links.
     * 
     * @var boolean 
     */
    public $useSymlinks;
    /**
     * Type of file deleting.
     * Force deleting: file is force delete and cannot be restored.
     * Trash deleting: file is only trashed and can be restore.
     *
     * @var boolean
     */
    public $fileDeleting;
    /**
     * Create file document automatically after upload.
     * 
     * @var boolean 
     */    
    public $fileDocumentAutomatically;
    /**
     * Edit file document immediately after upload a file. The option only works when option "File Document Automatically" is enabled.
     * 
     * @var boolean 
     */    
    public $editDocumentImmediately;
    /**
     * Use frontend search module above document list.
     *
     * @var boolean
     */
    public $useSearch;
    public $searchShowParent;
    public $searchKeyword;
    public $searchShowTitle;
    public $searchShowText;
    public $searchShowMetadata;
    public $searchShowFulltext;
    public $searchShowType;
    public $searchTypes;
    public $searchTypeAnykey;
    public $searchTypeAllkey;
    public $searchTypePhrase;
    public $searchTypeRegexp;
    public $searchDefaultType;
    public $searchShowOrder;
    public $searchOrders;
    public $searchOrderNewest;
    public $searchOrderOldest;
    public $searchOrderHits;
    public $searchOrderTitle;
    public $searchDefaultOrder;
    
    /**
     * Use MooTree navigator in backend.
     * 
     * @var boolean
     */
    public $useExplorer;
    /**
     * Name of root folder in exporer tree
     * 
     * @var string
     */
    public $explorerRoot;
	/**
	 * Send e-mail to other users after changed it. Only PRO.
	 * @var bool
	 */    
   	public $useVersioningMailing;
   	/**
   	 * E-mail subject. 
   	 * @var string
   	 */
   	public $versioningMailingSubject;
   	/**
   	 * E-mail body.
   	 * @var string (HTML with transcription marks)
   	 */
   	public $versioningMailingBody;
    
    
    public $documentAccess;
    
    public $edocsList;
    public $edocsDetail;
    /**
     * Upload files by drop & drag.
     * @var bool 
     */
    public $dropAndDrag;
    
    public $confirmOverwrite;
    /**
     * Get JoomDOC configuration instance.
     *
     * @return JoomDOCConfig
     */
    public static function getInstance ($path = null) {
        static $instances;
        if (empty($instances))
            $instances = array();
        foreach ($instances as $instance)
            if ($instance->path == $path)
                return $instance->cfg;
        $instance = new JObject();
        $instance->path = $path;
        $instance->cfg = new JoomDOCConfig($path);
        $instances[] = $instance;
        return $instance->cfg;
    }

    /**
     * Create object and load JoomDOC configuration.
     *
     * @return void
     */
    public function __construct ($path = null) {
        $params = JComponentHelper::getParams(JOOMDOC_OPTION);
        /* @var $params JRegistry */
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */

        $defaultDocRoot = JPATH_ROOT . DIRECTORY_SEPARATOR . 'documents';
        $maskDocRoot = '[%DOCROOT%]';

        $this->docroot = JPath::clean(JString::trim($params->get('docroot', $defaultDocRoot)));

        if (JFile::exists(JOOMDOC_CONFIG) && is_writable(JOOMDOC_CONFIG)) {
            $content = JFile::read(JOOMDOC_CONFIG);
            if (JString::strpos($content, $maskDocRoot) !== false) {
                $content = str_replace($maskDocRoot, $defaultDocRoot, $content);
                JFile::write(JOOMDOC_CONFIG, $content);
            }
        }

        $this->docrootrel = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $this->docroot);
        if (!JFolder::exists($this->docroot)) {
            if (!JFolder::create($this->docroot)) {
                if ($mainframe->isAdmin()) {
                    JError::raiseWarning(21, JText::sprintf('JOOMDOC_UNABLE_CREATE_DOCROOT', $this->docroot));
                }
                $this->docroot = false;
            } elseif ($mainframe->isAdmin()) {
                $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_DOCROOT_CREATED', $this->docroot));
            }
        }
        
        $this->downloadId               = JString::trim($params->get('download_id', ''));
        AUpdateHelper::setUpdateLink('ARTIO JoomDOC Updates', $this->downloadId);
        $this->defaultTitle             = JString::trim($params->get('default_title'));
        $this->defaultDescription       = JString::trim($params->get('default_description'));
        $this->defaultMetakeywords      = JString::trim($params->get('default_metakeywords'));
        $this->defaultMetadescription   = JString::trim($params->get('default_metadescription'));
                
         $this->versionFile = 0;
        $this->versionDocument = 0;
        $this->versionFileFrontend = 0;
        $this->versionRequired = 0;
        
        $this->displayFavorite          = (int) $params->get('display_favorite', 1);
        $this->webdavAllow              = (int) $params->get('webdav_allow', 25);
        $this->displaySignature         = (int) $params->get('display_signature', 1);

        $this->path                     = $this->docroot;
        $this->documentOrdering         = JOOMDOC_ORDER_ORDERING;
        $this->fileOrdering             = JOOMDOC_ORDER_PATH;
        $this->orderingDirection        = JString::trim($params->get('ordering_direction')) == 'asc' ? JOOMDOC_ORDER_ASC : JOOMDOC_ORDER_DESC;
        $this->foldersFirstSite 		= (bool)$params->get('folders_first_site', 0);
        $this->foldersFirstAdmin 		= (bool)$params->get('folders_first_admin', 0);
        if (!($this->iconTheme          = JString::trim($params->get('icon_theme', 'default'))))
        	$this->iconTheme = 'default'; //fallback
        if (!($this->iconThemeBackend   = JString::trim($params->get('icon_theme_backend', 'default'))))
        	$this->iconThemeBackend = 'default'; //fallback
        $this->docLayout                = JString::trim($params->get('document_layout', 0));
        $this->docLayout = 0; //not done now
        $this->useWebdav                = (int) $params->get('use_webdav', 0);
        $this->useSymlinks              = (int) $params->get('use_symlinks', 1);  
        $this->webdavType               = (int) $params->get('webdav_type', 1);
        $this->accessHandling           = (int) $params->get('access_handling', 1);
        $this->fileDeleting             = (int) $params->get('file_deleting', 1);
        $this->fileDocumentAutomatically= (int) $params->get('file_document_automatically', 0);
        $this->editDocumentImmediately  = (int) $params->get('edit_document_immediately', 0);
        
        $this->useSearch                = (int) $params->get('use_search', 0);
        $this->searchShowParent         = (int) $params->get('search_show_parent', 1);
        $this->searchKeyword            = (int) $params->get('search_keyword', 1);
        
        $this->searchShowTitle          = (int) $params->get('search_show_title', 1);
        $this->searchShowText           = (int) $params->get('search_show_text', 1);
        $this->searchShowMetadata       = (int) $params->get('search_show_metadata', 1);
        $this->searchShowFulltext		= (int) $params->get('search_show_fulltext', 1);
        
        $this->searchShowType			= (int) $params->get('search_show_type', 1);
        $this->searchTypes				= (array) $params->get('search_types', array(JOOMDOC_SEARCH_ANYKEY, JOOMDOC_SEARCH_ALLKEY, JOOMDOC_SEARCH_PHRASE, JOOMDOC_SEARCH_REGEXP));
        
        $this->searchTypeAnykey         = in_array(JOOMDOC_SEARCH_ANYKEY, $this->searchTypes);
        $this->searchTypeAllkey         = in_array(JOOMDOC_SEARCH_ALLKEY, $this->searchTypes);
        $this->searchTypePhrase         = in_array(JOOMDOC_SEARCH_PHRASE, $this->searchTypes);
        $this->searchTypeRegexp         = in_array(JOOMDOC_SEARCH_REGEXP, $this->searchTypes);
        
        $this->searchDefaultType        = (int) $params->get('search_default_type', JOOMDOC_SEARCH_ANYKEY);
        
        $this->searchShowOrder			= (int) $params->get('search_show_order', 1);
        $this->searchOrders				= (array) $params->get('search_orders', array(JOOMDOC_ORDER_NEWEST, JOOMDOC_ORDER_OLDEST, JOOMDOC_ORDER_HITS, JOOMDOC_ORDER_TITLE));
        
        $this->searchOrderNewest		= in_array(JOOMDOC_ORDER_NEWEST, $this->searchOrders);
        $this->searchOrderOldest		= in_array(JOOMDOC_ORDER_OLDEST, $this->searchOrders);
        $this->searchOrderHits			= in_array(JOOMDOC_ORDER_HITS, $this->searchOrders);
        $this->searchOrderTitle			= in_array(JOOMDOC_ORDER_TITLE, $this->searchOrders);
        
        $this->searchDefaultOrder		= $params->get('search_default_order', JOOMDOC_ORDER_NEWEST);
        
        $this->useExplorer              = (int) $params->get('use_explorer', 1);
        $this->explorerRoot             = JString::trim($params->get('explorer_root', 'Documents'));

        $this->documentAccess           = (int) $params->get('document_access', 1);
        
        $this->edocsList                = (int) $params->get('edocs_list', 0);
        $this->edocsDetail              = (int) $params->get('edocs_detail', 0);
        $this->dropAndDrag              = (int) $params->get('upload_type', 1);
        $this->confirmOverwrite         = (int) $params->get('confirm_overwrite', 0);
        
        if ( $mainframe->isSite() ) {
            
            $menu = $mainframe->getMenu();
            /* @var $menu JMenuSite */

            $itemID = $path ? JoomDOCMenu::getMenuItemID($path) : null;
            $itemID = $itemID ? $itemID : JRequest::getInt('Itemid');

            $item = $itemID ? $menu->getItem($itemID) : $menu->getActive();

            if ( is_object($item) ) {
                
                if ( isset($item->query['path']) ) {
                    
                    // get start folder from menu item URL (param path)
                    $path = JString::trim($item->query['path']);
                    
                    if ( $path ) {
                        
                        $path = JPath::clean($this->docroot . DIRECTORY_SEPARATOR . $path);
                        
                        if ( JFolder::exists($path) || JFile::exists($path) ) {
                            
                            $this->path = $path;
                            
                        } else {
                            
                            $this->path = false;
                            
                        }
                    }
                }

                $params->merge($item->params);
                
                // get display options from menu item setting
                $this->showSubfolders = (int) $params->get('show_subfolders', 1);
                $this->filesWithoutDoc = (int) $params->get('files_without_doc', 1);
                $this->documentOrdering = $params->get('document_ordering', JOOMDOC_ORDER_ORDERING);
                $this->orderingDirection = JString::trim($params->get('ordering_direction')) == 'asc' ? JOOMDOC_ORDER_ASC : JOOMDOC_ORDER_DESC;
                $this->fileOrdering = $params->get('file_ordering', JOOMDOC_ORDER_PATH);
                $this->showFileicon = (int) $params->get('show_fileicon', 1);
                $this->showFilesize = (int) $params->get('show_filesize', 1);
                $this->showCreated = (int) $params->get('show_created', 1);
                $this->showModified = (int) $params->get('show_modified', 1);
                $this->showHits = (int) $params->get('show_hits', 1);
                $this->showOpenFile = (int) $params->get('show_open_file', 1);
                $this->showDownloadFile = (int) $params->get('show_download_file', 1);
                $this->showOpenFolder = (int) $params->get('show_open_folder', 1);
                $this->showFileDesc = (int) $params->get('show_file_desc', 1);
                $this->showFolderDesc = (int) $params->get('show_folder_desc', 1);
                $this->showLicense = (int) $params->get('show_license', 1);
                $this->virtualFolder = (int) $params->get('virtual_folder', 0);
            }
        }
    }
}
?>