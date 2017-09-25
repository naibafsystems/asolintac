<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JoomDOCAccessHelper {
    public $docid;

    public $isFolder;
    public $isFile;
    public $fileType;

    public $isTrashed;

    public $relativePath;
    public $absolutePath;

    public $inRoot;

    public $name;
    public $alias;

    public $isChecked;
    public $isLocked;

    public $canViewFileInfo;
    public $fileVersion;
    public $canRename;
    public $canWebDav;
    public $canEdit;
    public $canAnyEditOp;
    public $canCreate;
    public $canDownload;

    public $canEnterFolder;
    public $canOpenFolder;
    public $canOpenFile;

    public $canEditState;
    public $canEditStates;
    public $canCopyMove;
    public $canDeleteDoc;
    public $canDeleteDocs;
    public $canDeleteFile;
    public $canUpload;
    public $canCreateFolder;
    public $canViewVersions;

    public $canShowFileDates;
    public $canShowFileInfo;
    public $canShowAllDesc;

    public $isFavorite;
    public $canDisplayFavorite;

    public $licenseID;
    public $licenseAlias;
    public $licenseTitle;

    public $canManageVersions;
    public $canUntrash;

    public function __construct (&$item) {
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $user = JFactory::getUser();

        $this->isFile = JoomDOCFileSystem::isFile($item);
        $this->isFolder = JoomDOCFileSystem::isFolder($item);       

        $isFileSystemItem = $this->isFile || $this->isFolder;

        $this->docid = $isFileSystemItem ? JoomDOCHelper::getDocumentID($item) : $item->id;

        if (isset($item->document)) {
            $document = new JObject();
            $document->setProperties($item->document);
        } elseif (!$isFileSystemItem) {
            if ($item instanceof JObject) {
                $document = $item;
            } else {
                $document = new JObject($item);
                $document->setProperties($item);
            }
        } else {
            $document = new JObject();
        }

        $this->isTrashed = $isFileSystemItem ? @$item->file_state == JOOMDOC_STATE_TRASHED : $document->get('file_state') == JOOMDOC_STATE_TRASHED;

        if ($mainframe->isSite() && $document->get('state') == JOOMDOC_STATE_TRASHED) {
            $this->docid = null;
        }

        $this->relativePath = $isFileSystemItem ? $item->getRelativePath() : $item->path;
        $this->absolutePath = $isFileSystemItem ? $item->getAbsolutePath() : JoomDOCFileSystem::getFullPath($this->relativePath);

        $this->inRoot = $this->absolutePath == $config->path;

        $this->name = $isFileSystemItem ? $item->getFileName() : JFile::getName($this->relativePath);
        $this->alias = JoomDOCHelper::getDocumentAlias($item);

        $this->isChecked = JoomDOCHelper::isChecked($item);
        $this->isLocked = false;
                $this->fileType = JoomDOCHelper::getFileType($this->name);

        $this->canViewFileInfo = JoomDOCAccessFileSystem::viewFileInfo($this->docid, $this->relativePath);
        $this->fileVersion = JoomDOCHelper::getMaxVersion($this->relativePath);
        $this->canRename = JoomDOCAccessFileSystem::rename($this->docid, $this->relativePath);
        $this->canWebDav = JoomDOCAccessFileSystem::editWebDav($this->docid, $this->relativePath);
        $this->canEdit = $this->docid && JoomDOCAccessDocument::canEdit($document);
        $this->canCreate = !$this->docid && JoomDOCAccessDocument::create($this->relativePath);
        if ($config->documentAccess == 2 && $mainframe->isSite()) {
            $this->canDownload = $this->isFile && $document && $user->id == $document->get('access') && $document->get('download');
        } else {
            $this->canDownload = $this->isFile && JoomDOCAccessFileSystem::download($this->docid, $this->relativePath);
        }
        $this->canEnterFolder = JoomDOCAccessFileSystem::enterFolder($this->docid, $this->relativePath);
        $this->canOpenFolder = $this->isFolder && $this->canEnterFolder;
        $this->canOpenFile = $this->isFile;

        $this->canEditStates = JoomDOCAccessDocument::editState($this->docid, $document->get('checked_out'));
        $this->canEditState = $this->docid && JoomDOCAccessDocument::editState($this->docid, $document->get('checked_out'));
        if ($mainframe->isAdmin()) {
            $this->canEditState = JoomDOCAccessDocument::editState(); 
        }
        $this->canCopyMove = JoomDOCAccessFileSystem::copyMove($this->docid, $this->relativePath);
        $this->canDeleteDocs = JoomDOCAccessDocument::delete($this->docid);
        $this->canDeleteDoc = $this->docid && JoomDOCAccessDocument::delete($this->docid);
        $this->canDeleteFile = JoomDOCAccessFileSystem::deleteFile($this->docid, $this->relativePath);
        $this->canUpload = JoomDOCAccessFileSystem::uploadFile($this->docid, $this->relativePath);
        $this->canCreateFolder = JoomDOCAccessFileSystem::newFolder($this->docid, $this->relativePath);
        $this->canViewVersions = JoomDOCAccessDocument::viewVersions($this->docid);

        $this->canShowFileDates = $config->showCreated || $config->showModified;
        $this->canShowFileInfo = $config->showFilesize || $config->showHits;
        $this->canShowAllDesc = $config->showFolderDesc && $config->showFileDesc;

        $this->isFavorite = $document->get('favorite') == 1;
        $this->canDisplayFavorite = $this->isFavorite && $config->displayFavorite;

        $this->canAnyEditOp = $config->accessHandling && ($this->canEdit || $this->canWebDav || $this->canEditState || $this->canCreate || $this->canDeleteFile || $this->canDeleteDoc);

        if (!$this->docid || !$document->get('license_id')) {
            $license = JoomDOCHelper::license($this->relativePath);
            if ($license) {
                $this->licenseID = $license->id;
                $this->licenseAlias = $license->alias;
                $this->licenseTitle = $license->title;
            }
        } elseif ($document->get('license_state') == JOOMDOC_STATE_PUBLISHED) {
            $this->licenseID = $document->get('license_id');
            $this->licenseAlias = $document->get('license_alias');
            $this->licenseTitle = $document->get('license_title');
        }
        
                $this->canManageVersions = false;
                
        $this->canUntrash = JoomDOCAccessFileSystem::untrash($this->docid, $this->relativePath);
    }
}
?>