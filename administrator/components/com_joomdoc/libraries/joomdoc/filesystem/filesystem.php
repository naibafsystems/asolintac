<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.filesystem.archive');

class JoomDOCFileSystem {

    /**
     * Get informations about folders and files in folder.
     *
     * @param string $absolutePath absolute folder path
     * @param string $filter filter folder/file name
     * @param boolean $recurse use recursive folder scaning (in subfolders), default false (ignore subitems)
     * @param boolean $addFolders add folders in output, default true
     * @param boolean $addFiles add files in output, default true
     * @param string $exploded exploded node in tree
     * @return JoomDOCFolder function returns false if path doesn't exists
     */
    public static function getFolderContent ($absolutePath, $filter = '', $recurse = false, $addFolders = true, $addFiles = true, $exploded = null) {
        if (JFolder::exists($absolutePath)) {
            $root = new JoomDOCFolder($absolutePath);
            if ($addFolders) {
            	$folders = JFolder::folders($absolutePath, $filter . '.', $recurse, true);
            	if ($exploded)
            		$folders = JoomDOCFileSystem::getExplodedFolders($absolutePath, $filter, $recurse, $exploded);
            	else
            		$folders = JFolder::folders($absolutePath, $filter . '.', $recurse, true);
                foreach ($folders as $folder)
                	if (JString::strpos($folder, JOOMDOC_VERSION_DIR) === false)
                    	$root->addFolder(new JoomDOCFolder($folder));
            }
            if ($addFiles)
                foreach (JFolder::files($absolutePath, $filter . '.', $recurse, true) as $file)
            	                		if (JString::strpos($file, JOOMDOC_VERSION_DIR) === false)
                    		$root->addFile(new JoomDOCFile($file));
                        return $root;
        } elseif (JFile::exists($absolutePath))
            return new JoomDOCFile($absolutePath);
        return false;
    }
    
    /**
     * Get one level folder list exploded to the some folder.
     *
	 * @param string $absolutePath absolute folder path
     * @param string $filter filter folder/file name
     * @param boolean $recurse use recursive folder scaning (in subfolders), default false (ignore subitems)
     * @param string $exploded exploded node in tree
     * @return array folder absolute path list
     */
    public static function getExplodedFolders($absolutePath, $filter, $recurse, $exploded) {
    	$folders = JFolder::folders($absolutePath, $filter . '.', $recurse, true); // default folder level
    	$crumbs = JoomDOCFileSystem::getPathBreadCrumbs($exploded, true); // crumbs to the exploded folder
    	while (!empty($crumbs)) { // add all crumbs are in the folder list
    		foreach ($folders as $l => $folder) {
    			if (JString::strpos($folder, JOOMDOC_VERSION_DIR) === false)
	    			if (in_array($folder, $crumbs)) { // expand to the depth
	    				$children = JFolder::folders($folder, $filter . '.', $recurse, true);
	    				if (!empty($children)) { // append next level after crumb
	    					$head = array_slice($folders, 0, $l + 1);
	    					$tail = array_slice($folders, $l + 1);
	    					$folders = array_merge($head, $children, $tail);
	    				}
	    				unset($crumbs[array_search($folder, $crumbs)]); // crumb satisfied
	    			}
    		}
    	}
    	$folders = array_unique($folders);
    	return $folders;
    }

    /**
     * Get list of available parents (folders).
     *
     * $return array stdClass with params: path, title, parent
     */
    public static function getParents () {
        $config = JoomDOCConfig::getInstance();
        $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX);
        /* @var $model JoomDOCModelDocuments */

        // get all folders relative paths
        $paths = JFolder::folders($config->docroot, '.', true, true);
        foreach ($paths as $i => $path) {
        	if (JString::strpos($path, JOOMDOC_VERSION_DIR) === false)
            	$paths[$i] = JoomDOCFileSystem::getRelativePath($path);
        	else
        		unset($paths[$i]);
        }
        $paths = array_merge($paths);
        // get paths documents titles
        $parents = $model->getPathsDocsTitles($paths);
        
        foreach ($parents as $parent) {
            if (empty($parent->title)) {
                $parent->title = JFile::getName($parent->path);
            }
            $parent->parent = JoomDOCFileSystem::getParentPath($parent->path);
        }
        return $parents;
    }

    /**
     * Get all parents. List is filtered only for trashed items.
     * 
     * @since 3.3.1
     * @return array relative paths of non trashed parents
     */
    public static function getNonTrashedParents($parent = '', $recurse = true, $exploded = null) {
        
    	$config = JoomDOCConfig::getInstance();
        $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX);
        
        /* @var $model JoomDOCModelDocuments */
        $docroot = $parent === '' ? $config->docroot : JoomDOCFileSystem::getFullPath($parent);
        if ($exploded)
        	$paths = JoomDOCFileSystem::getExplodedFolders($docroot, '', $recurse, $exploded);
        else
        	$paths = JFolder::folders($docroot, '.', $recurse, true); // absolute paths of all folders into deep
        $paths = array_map('JPath::clean', $paths);
        
        foreach ($paths as &$path) // add slash at the end to fix problem with asort
        	$path .= DIRECTORY_SEPARATOR;
        // Sort the folders again with asort like JFolder::folders
        asort($paths);
        foreach ($paths as &$path) // remove slash at the end after asort
        	$path = JString::substr($path, 0, JString::strlen($path) - 1);
        
        
        $paths = array_map('JoomDOCFileSystem::getRelativePath', $paths); // convert to relative paths
        
        
        if ( DIRECTORY_SEPARATOR == "\\") {

             foreach ($paths as $i => $pth) {

                 $paths[$i] = str_replace($config->docroot . DIRECTORY_SEPARATOR, '', $pth);

             }

        }
        
        $nonTrashed = $model->getNonTrashedFiles($paths); // filter for trashed
        
        if ($parent)
        	$nonTrashed[] = $parent;
        foreach ($paths as $i => $path) {
        	$parent = JoomDOCFileSystem::getParentPath($path);            
                if (!in_array($path, $nonTrashed) || ($parent && !in_array($parent, $nonTrashed))) unset($paths[$i]); // item or parent are trashed

        }

        $paths = array_merge($paths); // reindexing
        natcasesort($paths);
        
        return $paths;
    }
    
    /**
     * Convert absolute filepath in file system to relative path from Joomla root.
     *
     * @param string $absolutePath absolute path to convert
     * @return string relative path
     */
    public static function getRelativePath ($absolutePath) {
        $config = JoomDOCConfig::getInstance();
        if ($config->docroot == $absolutePath)
            return '';
        return JPath::clean(str_replace($config->docroot . DIRECTORY_SEPARATOR, '', $absolutePath));
    }

    /**
     * Get file URL.
     *
     * @param string $relativePath file relative path from document root
     * @return string
     */
    public static function getURL ($relativePath) {
        $config = JoomDOCConfig::getInstance();
        return JURI::root() . $config->docrootrel . JPath::clean('/' . $relativePath, '/');
    }

    /**
     * Get last item from path. File or last folder name.
     *
     * @param string $absolutePath absolute path
     * @return string name of last folder or file, if not found function return false
     */
    public static function getLastPathItem ($absolutePath) {
        if (is_array(($parts = explode(DIRECTORY_SEPARATOR, $absolutePath))))
            return end($parts);
        return false;
    }

    /**
     * Get bread crumbs of given path from root.
     *
     * @param string $relativePath relative path
     * @return array of JObject with params path (relative path to bread crumb) and name (name of bread crumb)
     */
    public static function getPathBreadCrumbs ($relativePath, $asPathArray = false) {
    	$mainframe = JFactory::getApplication();
    	if (!$asPathArray) {
        	$breadCrumb = new JObject();
        	$breadCrumb->path = $mainframe->isSite() ? '' : JText::_('JOOMDOC_ROOT'); 
        	$breadCrumb->name = JText::_('JOOMDOC_ROOT');
        	$breadCrumbs[] = $breadCrumb;
    	}
        $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
        if (is_array($parts)) {
            foreach ($parts as $part)
                if (($part = JString::trim($part))) {
                	$paths[] = $part;
                	if ($asPathArray) {
                		$breadCrumbs[] = JoomDOCFileSystem::getFullPath(implode(DIRECTORY_SEPARATOR, $paths));
                		continue;
                	}
                    $breadCrumb = new JObject();
                    $breadCrumb->path = implode(DIRECTORY_SEPARATOR, $paths);
                    $breadCrumb->name = $part;
                    $breadCrumbs[] = $breadCrumb;
                }
        }
        return $breadCrumbs;
    }

    /**
     * Create new folder in parent folder.
     *
     * @param string $parentFolder relative path of parent folder, if null is used param path from request
     * @param string $newFolder name of new folder, in null is used param newFolder from request
     * @param boolean $msg display message after creating
     * @return void
     */
    public static function newFolder ($parentFolder = null, $newFolder = null, $msg = true, $reindex = true) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        if (is_null($parentFolder)) {
            // creating new folder from user request
            $parentFolder = JoomDOCRequest::getPath();
        }
        if (is_null($newFolder)) {
            // creating new folder from user request
            $newFolder = JString::trim(JRequest::getString('newfolder'));
            JRequest::setVar('newfolder', '');
        }
        $newFolder = JoomDOCString::safe($newFolder);
        $parentFolder = JoomDOCFileSystem::getFullPath($parentFolder);
        // parent folder is relative, convert to full and add new folder name
        $absolutePath = JPath::clean($parentFolder . DIRECTORY_SEPARATOR . $newFolder);
        $relativePath = JoomDOCFileSystem::getRelativePath($absolutePath);
        if (JoomDOCAccessFileSystem::newFolder(false, JoomDOCFileSystem::getRelativePath($parentFolder))) {
            if (!JFolder::exists($parentFolder)) {
                JError::raiseWarning(21, JText::sprintf('JOOMDOC_PARENT_FOLDER_NO_EXISTS', $parentFolder));
            } elseif (!$newFolder) {
                JError::raiseWarning(21, JText::sprintf('JOOMDOC_NEW_FOLDER_CANNOT_HAVE_EMPTY_NAME', $relativePath));
            } elseif (JString::strpos($newFolder, DIRECTORY_SEPARATOR) !== false) {
                JError::raiseWarning(21, JText::sprintf('JOOMDOC_FOLDER_NAME_CANNOT_CONTAIN_SLASH', $relativePath));
            } elseif (JFolder::exists($absolutePath)) {
                JError::raiseWarning(21, JText::sprintf('JOOMDOC_FOLDER_ALREADY_EXISTS', $relativePath));
            } else {
                if (JFolder::create($absolutePath)) {
                    $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
                    /* @var $table JoomDOCTableFile */
                    $table->path = JoomDOCFileSystem::getRelativePath($absolutePath);
                    $table->store(false, $reindex);
                    if ($msg) {
                        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_FOLDER_CREATED', $newFolder));
                    }
                    $mainframe->setUserState('com_joomdoc.new.folder', $table->path);
                    return true;
                }
            }
        } else {
            JError::raiseWarning(21, JText::sprintf('JOOMDOC_UNABLE_CREATE_FOLDER', $relativePath));
        }
        return false;
    }

    /**
     * Test if folder is subfolder of its parent folder.
     *
     * @param string $subfolder absolute path to folder which we need test
     * @param string $folder absolute path to tested folder parent folder
     * @return mixed true/false/null - is subfolder/is not subfolder/unable test
     */
    public static function isSubFolder ($subfolder, $folder) {
        $subfolder = JPath::clean($subfolder);
        $folder = JPath::clean($folder);
        if (!JoomDOCFileSystem::exists($subfolder) || !JoomDOCFileSystem::exists($folder)) {
            return null;
        }
        return JString::strpos($subfolder, $folder) === 0;
    }

    /**
     * File or folder exists.
     *
     * @param string $path absolute path
     * @return boolean true/false - exists/no exists
     */
    public static function exists ($path) {
        return JFolder::exists($path) || JFile::exists($path);
    }

    /**
     * Delete folder or file.
     *
     * @param string $path absolute path
     * @return boolean true/false - success/unsuccess
     */
    public static function deleteItem ($path) {
        return (((JFolder::exists($path) && JFolder::delete($path)) || (JFile::exists($path) && JFile::delete($path))));
    }

    /**
     * Delete folders/files.
     *
     * @param mixed $path paths to delete
     * @return void
     */
    public static function delete () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $model JoomDOCModelDocument */
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */

        $paths = $mainframe->isSite() ? array($path = JoomDOCRequest::getPath()) : JoomDOCRequest::getPaths();
        $count = 0;

        // control permissions
        foreach ($paths as $path)
            JoomDOCAccessFileSystem::deleteFile(false, $path) ? $allowDelete[] = $path : $notAllowDelete[] = $path;

        // alert not allowed files
        if (isset($notAllowDelete))
            $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_UNABLE_DELETE_ITEMS', implode(', ', $notAllowDelete)));

        if (isset($allowDelete)) {
            if ($config->fileDeleting) {
                // only trash files
                $count = $table->setStates(false, $allowDelete, JOOMDOC_STATE_TRASHED);
            } else {
                // get all paths to delete (with versions)
                $tree =& $table->delete($allowDelete);
                foreach ($tree as $item) {
                	                    if (JoomDOCFileSystem::deleteItem(JoomDOCFileSystem::getFullPath($item)))
                        // try delete last version (counted to alert)
                        $count++;
                }
            }
        }
        $mainframe->redirect(JoomDOCRoute::viewDocuments($path ? JoomDOCFileSystem::getParentPath($path) : null), JText::sprintf('JOOMDOC_FILES_DELETED', $count));
    }

    /**
     * Get file size in human readable format.
     *
     * @param string $absolutePath file absolute path
     * @return string file size with unit
     */
    public static function getFileSize ($absolutePath) {
        if (JFile::exists($absolutePath)) {
            // function sometimes provide warning - for this reason is used @
            $filesize = @filesize($absolutePath);
            if ($filesize) {
                if ($filesize >= 1000000000)
                    return JText::sprintf('JOOMDOC_GB_SIZE', round($filesize / 1000000000, 1));
                elseif ($filesize >= 1000000)
                    return JText::sprintf('JOOMDOC_MB_SIZE', round($filesize / 1000000, 1));
                elseif ($filesize >= 1000)
                    return JText::sprintf('JOOMDOC_KB_SIZE', round($filesize / 1000, 1));
                return JText::sprintf('JOOMDOC_B_SIZE', round($filesize), 1);
            }
        }
        return '-';
    }

    /**
     * Upload file or zipe archive.
     *
     * @return void
     */
    public static function upload ($redirect = true) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $config = JoomDOCConfig::getInstance();
        // folder where upload
        $path = JoomDOCRequest::getPath();
        $folder = JoomDOCFileSystem::getFullPath($path);
        // control if given folder is subfolder of documents root
        if (!JoomDOCFileSystem::isSubFolder($folder, $config->docroot))
            JError::raiseError(403, JText::sprintf('JOOMDOC_UNABLE_UPLOAD_FILE'));
        // unpack uploaded file (multiupload)
        $isZip = JRequest::getInt('iszip');
        // count of uploaded files
        $count = 0;
        $upload = JRequest::getVar('upload', null, 'files', 'array');
        $modelDocument = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $modelDocument JoomDOCModelDocument */
        if ($upload) {
            // mutltiple or single upload
            $size = is_array($upload['error']) ? count($upload['error']) : 1;
            for ($i = 0; $i < $size; $i++) {
                if (is_array($upload['error'])) { // mutliple
                    $data = array();
                    foreach ($upload as $property => $values) {
                        $data[$property] = $values[$i]; // extract single file
                    }
                } else {
                    $data = $upload; // single upload
                }
                if ($data['error'] != 0)
                    JError::raiseWarning(21, JText::sprintf('JOOMDOC_UNABLE_UPLOAD_FILE', ''));
                elseif (!JFolder::exists($folder))
                    JError::raiseWarning(21, JText::sprintf('JOOMDOC_PARENT_FOLDER_NO_EXISTS'), $folder);
                else {
                    if ($isZip && ($tmpFolder = JoomDOCFileSystem::createTemporaryFolder('joomdoc_unpack'))) {
                        $zip = JArchive::getAdapter('zip');
                        /* @var $zip JArchiveZip */
                        if ($zip->extract($data['tmp_name'], $tmpFolder) !== true)
                            JError::raiseWarning(21, JText::sprintf('JOOMDOC_UNABLE_EXTRACT_FILE', $data['name']));
                        else {
                            $rfolder = $path ? $path . DIRECTORY_SEPARATOR : '';
                            foreach (JFolder::folders($tmpFolder, '.', true, true) as $zipFolder) {
                                $newfolder = JPath::clean(str_replace($tmpFolder . DIRECTORY_SEPARATOR, $rfolder, $zipFolder));
                                if (!JoomDOCFileSystem::newFolder(JoomDOCFileSystem::getParentPath($newfolder), JFile::getName($newfolder), false, false))
                                    return false;
                            }
                            foreach (JFolder::files($tmpFolder, '.', true, true) as $zipFile)
                                if ($filePath = JoomDOCFileSystem::uploadFile($folder, $zipFile, str_replace($tmpFolder . DIRECTORY_SEPARATOR, '', $zipFile), true, false)) {
                                    if ($config->fileDocumentAutomatically && JoomDOCAccessDocument::create($filePath)) {
                                        $modelDocument->setState('document.id', null);
                                        $modelDocument->save(array('path' => $filePath, 'title' => JFile::getName($filePath), 'state' => JOOMDOC_STATE_PUBLISHED), true);
                                    }
                                    $count++;
                                }
                        }
                        JFolder::delete($tmpFolder);
                        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat();
                    } elseif ($filePath = JoomDOCFileSystem::uploadFile($folder, $data['tmp_name'], $data['name'])) {
                        if ($config->fileDocumentAutomatically && JoomDOCAccessDocument::create($filePath)) {
                            $modelDocument->setState('document.id', null);
                            $modelDocument->save(array('path' => $filePath, 'title' => JFile::getName($filePath), 'state' => JOOMDOC_STATE_PUBLISHED), true);
                            $document = $modelDocument->getItem();
                        }
                        $count++;
                    }
                }
            }
        }
        if ($redirect) {
            $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_FILES_UPLOADED', $count));
            if ($config->fileDocumentAutomatically && $config->editDocumentImmediately && !$isZip && !empty($document) && JoomDOCAccessDocument::edit($document->id) && $size == 1) {
                if ($mainframe->isAdmin()) {
                    $location = JoomDOCRoute::editDocument($document->id);
                } else {
                    $location = JoomDOCRoute::edit($document->path, $document->full_alias);
                }
            } else {
                $location = JoomDOCRoute::viewDocuments($path, false);
            }
            if (!$config->dropAndDrag) {
                $mainframe->redirect($location);
            } else {
                $session = JFactory::getSession();
                /* @var $session JSession */
                $session->set('application.queue', $mainframe->getMessageQueue());
                ob_clean();
                echo JRoute::_($location, false);
                $mainframe->close();
            }
        }
    }

    /**
     * Upload file in folder from source destination.
     *
     * @param string $folder absolute path to destination folder
     * @param string $filenameSource source file path
     * @param string $filenameDest destnation file name
     * @param boolean $copy use copy function instead upload, default false
     * @return string relative path of an uploaded file
     */
    public static function uploadFile ($folder, $filenameSource, $filenameDest, $copy = false, $reindex = true) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $model JoomDOCModelDocument */
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */

        $absolutePath = JPath::clean(dirname($folder . DIRECTORY_SEPARATOR . $filenameDest) . DIRECTORY_SEPARATOR . JoomDOCString::safe(JFile::getName($filenameDest)));
        $table->path = JoomDOCFileSystem::getRelativePath($absolutePath);

        if (JFile::exists($absolutePath) && !JoomDOCAccessFileSystem::uploadFile(null, $table->path)) {
            // control access to reupload old file
            $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_UNABLE_REUPLOAD_FILE', $table->path), 'notice');
            return false;
        }

        if (!JoomDOCAccessFileSystem::uploadFile(false, JoomDOCFileSystem::getRelativePath($folder))) {
            // control access to upload new file
            $mainframe->enqueueMessage(JText::_('JOOMDOC_UNABLE_UPLOAD'), 'notice');
            return false;
        }

                $upload = $copy ? JFile::copy($filenameSource, $absolutePath) : JFile::upload($filenameSource, $absolutePath);
        if ($upload) {
                        
             $table->store(false, $reindex);
             
        }
        return $upload ? $table->path : false;
    }
        /**
     * Get temporary folder. Use setting from Joomla! global configuration.
     *
     * @return string folder absolute path, if folder no exists or is unwritable function return false
     */
    public static function getJoomlaTemporaryFolder () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        if (!JFolder::exists(($tmpFolder = $mainframe->getCfg('tmp_path'))))
            JError::raiseNotice(21, JText::sprintf('JOOMDOC_JOOMLA_TEMPORARY_FOLDER_NO_EXISTS', $tmpFolder));
        elseif (!is_writable($tmpFolder))
            JError::raiseNotice(21, JText::sprintf('JOOMDOC_JOOMLA_TEMPORARY_FOLDER_IS_UNWRITABLE', $tmpFolder));
        else
            return $tmpFolder;
        if (JFolder::exists(($tmpFolder = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp')) && is_writable($tmpFolder))
            return $tmpFolder;
        return false;
    }

    /**
     * Create temporary folder in Joomla! template directory.
     *
     * @param string $mask name of new folder if folder already exist function given number on name begin
     * @return string template folder absolute path, if unable folder create function return false
     */
    public static function createTemporaryFolder ($mask) {
        if (($tmpFolder = JoomDOCFileSystem::getJoomlaTemporaryFolder())) {
            $r = '';
            do {
                $absolutePath = JPath::clean($tmpFolder . DIRECTORY_SEPARATOR . ($r++) . $mask);
            } while (JFolder::exists($absolutePath));
            if (!JFolder::create($absolutePath))
                JError::raiseWarning(21, JText::sprintf('JOOMDOC_UNABLE_CREATE_TEMPLATE_FOLDER', $absolutePath));
            else
                return $absolutePath;
        }
        return false;
    }

    /**
     * Get path of parent folder from path.
     *
     * @param string $path
     * @return string
     */
    public static function getParentPath ($path) {
        if (($pos = JString::strrpos($path, DIRECTORY_SEPARATOR)) === false)
            return null;
        return JString::substr($path, 0, $pos);
    }

    /**
     * Item is folder or no.
     *
     * @param mixed $item file/folder instance
     * @return boolean
     */
    public static function isFolder (&$item) {
        return ($item instanceof JoomDOCFolder);
    }

    /**
     * Item is file or no.
     *
     * @param mixed $item file/folder instance
     * @return boolean
     */
    public static function isFile (&$item) {
        return ($item instanceof JoomDOCFile);
    }

    /**
     * Get full absolute path.
     *
     * @param string $path relative path
     * @return string
     */
    public static function getFullPath ($path) {
        $config = JoomDOCConfig::getInstance();
        $path = JString::trim($path);
        return JPath::clean($config->docroot . ($path ? (DIRECTORY_SEPARATOR . $path) : ''));
    }

    /**
     * Download file by request param path (relative path from doc root).
     *
     * @param booelan $saveHits if true increment file download counter
     * @return void
     */
    public static function download ($saveHits = true) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $modelDocument = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $modelDocument JoomDOCModelDocument */
        $modelFile = JModelLegacy::getInstance(JOOMDOC_FILE, JOOMDOC_MODEL_PREFIX);
        /* @var $modelFile JoomDOCModelFile */
        $config = JoomDOCConfig::getInstance();
        $user = JFactory::getUser();

        // get file path from request
        $path = JoomDOCRequest::getPath();
        $version = null;
        
        // control user access to download file
        if ($config->documentAccess == 1 && !JoomDOCAccessFileSystem::download(false, $path)) {
            jexit(JText::_('JOOMDOC_DOWNLOAD_NOT_ALLOWED'));
        }

        // get document by file relative path
        $document = $modelDocument->getItemByPath($path);
        if ($config->documentAccess == 2 && ($user->id != $document->access || !$document->download)) {
            jexit(JText::_('JOOMDOC_DOWNLOAD_NOT_ALLOWED'));
        }
        $file = $modelFile->getItem($path, $version);

                
         $fullPath = JoomDOCFileSystem::getFullPath($path);
        
        // open file to reading
        $content = JFile::read($fullPath);

        // control file access
        $fileNotAvailable = !$file || is_null($file->id) || $file->state == JOOMDOC_STATE_TRASHED;
        $documentUnpublished = $document && $document->id && $document->published == JOOMDOC_STATE_UNPUBLISHED;

        if (($mainframe->isSite() && $documentUnpublished) || !JFile::exists($fullPath) || $content === false || $fileNotAvailable) {
            // file document unpublish or file doesn't exists or cannot read
            JError::raiseWarning(404, JText::_('JOOMDOC_FILE_NO_AVAILABLE'));
            $mainframe->redirect(JoomDOCRoute::viewDocuments());
            return;
        }

        if ($saveHits) {
            // save file downloading
        	        	
        	 $modelFile->saveHits($path, 1);
        	
        }

        // clean output
        ob_clean();

        // prepare packet head
        if (function_exists('mime_content_type')) {
            header('Content-Type: ' . mime_content_type($fullPath) . '; charset=UTF-8');
        } else {
        	$ext = JFile::getExt($fullPath);
        	// Open mime types
        	$mimemap['pdf'] = 'application/pdf';
        	// MS Office mime types
        	$mimemap['doc'] = 'application/msword';
        	$mimemap['xls'] = 'application/vnd.ms-excel';
        	$mimemap['ppt'] = 'application/vnd.ms-powerpoint';
        	// MS Office mime types XML
        	$mimemap['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        	$mimemap['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        	$mimemap['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        	// Archives
        	$mimemap['zip'] = 'application/zip';
        	$mimemap['tar'] = 'application/x-tar';
        	// Open Office
        	$mimemap['odb'] = 'application/vnd.oasis.opendocument.database';               
			$mimemap['odc'] = 'application/vnd.oasis.opendocument.chart';                  
			$mimemap['odf'] = 'application/vnd.oasis.opendocument.formula';                
			$mimemap['odg'] = 'application/vnd.oasis.opendocument.graphics';              
			$mimemap['odi'] = 'application/vnd.oasis.opendocument.image';                  
			$mimemap['odm'] = 'application/vnd.oasis.opendocument.text-master';            
			$mimemap['odp'] = 'application/vnd.oasis.opendocument.presentation';           
			$mimemap['ods'] = 'application/vnd.oasis.opendocument.spreadsheet';            
			$mimemap['odt'] = 'application/vnd.oasis.opendocument.text';                   
			$mimemap['otg'] = 'application/vnd.oasis.opendocument.graphics-template';      
			$mimemap['oth'] = 'application/vnd.oasis.opendocument.text-web';               
			$mimemap['otp'] = 'application/vnd.oasis.opendocument.presentation-template';  
			$mimemap['ots'] = 'application/vnd.oasis.opendocument.spreadsheet-template';   
			$mimemap['ott'] = 'application/vnd.oasis.opendocument.text-template';          
        	
        	if (isset($mimemap[$ext]))
        		header('Content-Type: ' . $mimemap[$ext] . '; charset=UTF-8');
        }
        header('Content-Transfer-Encoding: 8bit');
        header('Content-Disposition: attachment; filename="' . JFile::getName($fullPath) . '";');
        $fileSize = @filesize($fullPath);
        if ($fileSize) {
            header('Content-Length: ' . $fileSize);
        }
        // flush file
        die($content);
    }
        /**
     * Rename file/folder and versions folder. With failed roll back changes.
     * Rename path in database (file/document).
     *
     * @param string $oldPath relative path of file/folder to rename
     * @param string $newName new file/folder mame (without path)
     * @return boolean true/false - success/unsuccess
     */
    public static function rename ($oldPath, $newName) {
        
        $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        /* @var $model JoomDOCModelDocument */
        // parent folder of affected file/folder
        $parentPath = JoomDOCFileSystem::getParentPath($oldPath);
        if ($parentPath) {
            $newPath = $parentPath . DIRECTORY_SEPARATOR . $newName;
        } else {
            $newPath = $newName;
        }
        // old absolute path of affected file/folder
        $oldPathFull = JoomDOCFileSystem::getFullPath($oldPath);
        // new absolute path of affected file/folder
        $newPathFull = JoomDOCFileSystem::getFullPath($newPath);
                // control if user is allowed to edit this item
        if (!JoomDOCAccessFileSystem::rename(false, $oldPath))
            JError::raiseError(403, JText::_('JOOMDOC_RENAME_NOT_ALLOW'));
        // try to rename file or folder
        if ((JFile::exists($oldPathFull) && !JFile::move($oldPathFull, $newPathFull)) || (JFolder::exists($oldPathFull) && !JFolder::move($oldPathFull, $newPathFull))) {
            // unable rename
            return false;
        }
                // rename in database
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */
        $table->rename($oldPath, $newPath);
                return true;
    }
    /**
     * Get virtual path. Relative path without root folder parent path.
     *
     * @param string $path relative path to convert
     * @return string virtual relative path, if virtual path is turn off in menu item config return the same path as given
     */
    public static function getVirtualPath ($path) {
        static $virtualRootPath, $virtualRootAlias, $virtualRootPathLength, $virtualRootAliasLength;
        /* @var $virtualRootDepth depth of virtual folder parent */
        if ((is_null($virtualRootPath) && is_null($virtualRootAlias)) || isset($GLOBALS['joomDOCPath'])) {
            $config = JoomDOCConfig::getInstance(isset($GLOBALS['joomDOCPath']) ? $GLOBALS['joomDOCPath'] : null);
            if ($config->virtualFolder) {
                $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
                /* @var $model JoomDOCModelDocument */
                $virtualRootPath = JoomDOCFileSystem::getRelativePath($config->path);
                $virtualRootAlias = $model->searchFullAliasByPath($virtualRootPath);
                if (is_null($virtualRootAlias))
                    $virtualRootAlias = JoomDOCString::stringURLSafe($virtualRootPath);
                $virtualRootPathLength = JString::strlen($virtualRootPath) + 1;
                $virtualRootAliasLength = JString::strlen($virtualRootAlias) + 1;
            } else
                $virtualRootPath = $virtualRootAlias = false;
        }
        if ($path) {
            if ($virtualRootPath && JString::strpos($path, $virtualRootPath) === 0)
                $path = JString::substr($path, $virtualRootPathLength);
            elseif ($virtualRootAlias && JString::strpos($path, $virtualRootAlias) === 0)
                $path = JString::substr($path, $virtualRootAliasLength);
        }
        return $path;
    }

    /**
     * Get full relative path from virtual relative path.
     *
     * @param string $path virtual relative path
     * @return string full relative path, with docroot relative path, if virtual folder is turn off return the same path as given
     */
    public static function getNonVirtualPath ($path) {
        $config = JoomDOCConfig::getInstance();
        if ($config->virtualFolder && $config->path) {
            // if turn on virtual folder try to complete virtual relative path to path from docroot
            if (($root = JoomDOCFileSystem::getRelativePath($config->path)) && $root != $path)
                $path = JPath::clean($root . ($path ? (DIRECTORY_SEPARATOR . $path) : $path));
        }
        return $path;
    }

    /**
     * Clean relative path.
     *
     * @param string $path with single slash and without slash on the begin at the end
     * For example: //seg1/seg2///seg3/ clean to seg1/seg2/seg3
     */
    public static function clean ($path) {
        // detect slash type
        $unixSlash = JString::strpos($path, '/');
        $backSlash = JString::strpos($path, '\\');
        if ($unixSlash !== false && $backSlash === false)
            $slash = '/';
        elseif ($unixSlash === false && $backSlash !== false)
            $slash = '\\';
        if (isset($slash)) {
            $segments = explode($slash, $path);
            if ($segments !== false) {
                foreach ($segments as $segment)
                    if (($segment = JString::trim($segment)))
                        $paths[] = $segment;
                if (isset($paths))
                    $path = implode($slash, $paths);
            }
        }
        return $path;
    }
    /**
     * Set operation type and items paths into clipboard.
     *
     * @param int $operation operation type
     * @return void
     */
    public static function setOperation ($operation) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $mainframe->setUserState(JOOMDOC_USER_STATE_PATHS, JoomDOCRequest::getPaths());
        $mainframe->setUserState(JOOMDOC_USER_STATE_OPERATION, $operation);
    }
    /**
     * Check if user set operation into clipboard.
     *
     * @return boolean
     */
    public static function haveOperation () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        return $mainframe->getUserState(JOOMDOC_USER_STATE_OPERATION) !== null;
    }
    /**
     * Get user operation type from clipboard.
     *
     * @return int
     */
    public static function getOperation () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        return $mainframe->getUserState(JOOMDOC_USER_STATE_OPERATION);
    }
    /**
     * Get operations items paths from clipboard.
     *
     * @return array
     */
    public static function getOperationPaths () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        return $mainframe->getUserState(JOOMDOC_USER_STATE_PATHS);
    }
    /**
     * Reset operation clipboard.
     *
     * @return void
     */
    public static function resetOperation () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $mainframe->setUserState(JOOMDOC_USER_STATE_PATHS, null);
        $mainframe->setUserState(JOOMDOC_USER_STATE_OPERATION, null);
    }
    /**
     * Do user operation.
     *
     * @return void
     */
    public static function doOperation () {
        JoomDOCFileSystem::copyMove(JoomDOCFileSystem::getOperation() == JOOMDOC_OPERATION_MOVE);
    }

    public static function copyMove ($move = false) {
        
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */    
                    
        $tableFile = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $tableFile JoomDOCTableFile */

        // current folder copy into
        $folder = JoomDOCRequest::getPath();

        $folderAbsolutePath = JoomDOCFileSystem::getFullPath($folder);

        // current folder acces
        $folderCanCreateSubfolders = JoomDOCAccessFileSystem::newFolder(false, $folder);

        $folderCanUploadFiles = JoomDOCAccessFileSystem::uploadFile(false, $folder);

        // items to copy
        $paths = JoomDOCFileSystem::getOperationPaths();

        foreach ($paths as $path) {

            $absolutePath = JoomDOCFileSystem::getFullPath($path);
            $parent = JoomDOCFileSystem::getParentPath($absolutePath);
            $parentLength = JString::strlen($parent);

            $items = array($absolutePath);

            if (JFolder::exists($absolutePath)) {
                if (!$folderCanCreateSubfolders) {
                    // copied/moved item is folder and current folder aren't allowed to create subfolders
                    $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_UNABLE_SUBFOLDERS', $folder, $path), 'notice');
                    continue;
                }
                // unable copy/move folder into own subfolder
                if (JoomDOCFileSystem::isSubFolder($folderAbsolutePath, $absolutePath)) {
                    $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_UNABLE_INTO_SELF', $folder, $path), 'notice');
                    continue;
                }
                $items = array_merge($items, JFolder::folders($absolutePath, '', true, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX', JOOMDOC_VERSION_DIR)));
                $items = array_merge($items, JFolder::files($absolutePath, '', true, true));
            } elseif (!JFile::exists($absolutePath))
                continue;

            foreach ($items as $item) {

                $itemRelativePath = JoomDOCFileSystem::getRelativePath($item);

                if (!JoomDOCAccessFileSystem::copyMove(false, $itemRelativePath)) {
                    $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_UNABLE', $itemRelativePath), 'notice');
                    continue;
                }

                // get relative path from copied path parent
                $relativePath = JString::substr($item, $parentLength);

                // destination is current folder + relative path
                $destination = $folderAbsolutePath . $relativePath;

                $destinationRelativePath = JoomDOCFileSystem::getRelativePath($destination);

                if (JFolder::exists($item)) {
                    // is folder only create
                    if (JFolder::exists($destination)) {
                        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_FOLDER_EXISTS', $destinationRelativePath), 'notice');
                        continue;
                    }
                    JFolder::create($destination);
                }

                if (JFile::exists($item)) {
                    if (!$folderCanUploadFiles) {
                        // copied/moved item is file or contain files and current folder aren't allowed to upload files
                        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_UNABLE_UPLOAD', $itemRelativePath, $folder), 'notice');
                        continue;
                    }
                    if (JFile::exists($destination)) {
                        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_CPMV_FILE_EXISTS', $destinationRelativePath), 'notice');
                        continue;
                    }
                    JFile::copy($item, $destination);
                    // get file versions
                                    }
                // copy/move table rows
                $tableFile->copyMove($itemRelativePath, $destinationRelativePath, $move);
            }

            if ($move) {
                // move item delete source after copy
                JoomDOCFileSystem::deleteItem($absolutePath);
            }
        }
        JoomDOCFileSystem::resetOperation();
    }

    public static function refresh ($folder = null) {
        static $count;
        if (is_null($count))
            $count = 0;
        if (JoomDOCAccessFileSystem::refresh()) {
            $config = JoomDOCConfig::getInstance();
            /* @var $config JoomDOCConfig */
            $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX);
            /* @var $model JoomDOCModelDocuments */
            $file = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
            /* @var $file JoomDOCTableFile */
            $mainframe = JFactory::getApplication();
            /* @var $mainframe JApplication */
            if (is_null($folder))
                $folder = $config->docroot;
            if (JFolder::exists($folder)) {
                // scandir for folders
                $folderpaths = JFolder::folders($folder, '.', false, true);
                foreach ($folderpaths as $folderpath) {
                    // for folders the same recursive
                    $foldername = JFile::getName($folderpath);
                    if ($foldername[0] != '.')
                        // no hidden folders
                        JoomDOCFileSystem::refresh($folderpath);
                }
                // scandir for files
                $filepaths = JFolder::files($folder, '.', false, true);
                foreach ($filepaths as $i => $filepath) {
                    $filename = JFile::getName($filepath);
                    if ($filename[0] == '.')
                        // no hidden files
                        unset($filepaths[$i]);
                                        else
                        $filepaths[$i] = JoomDOCFileSystem::getRelativePath($filepath);
                }
                if ($folder != $config->docroot)
                    // docroot hasn't db row
                    $filepaths[] = JoomDOCFileSystem::getRelativePath($folder);
                // search exist's paths in db
                $exists = $model->searchPaths($filepaths);
                foreach ($filepaths as $filepath)
                    if (!in_array($filepath, $exists)) {
                        // file not in db
                        $file->path = $filepath;
                        $file->store();
                        $count++;
                    }
            }
        }
        return $count;
    }
        }
?>