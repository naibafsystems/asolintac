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

class JoomDOCTableFile extends JTable {
    /**
     * Primary key
     *
     * @var int
     */
    var $id;
    /**
     * Relative path of original file
     *
     * @var string
     */
    var $path;
    /**
     * Version number
     *
     * @var int
     */
    var $version;
    /**
     * File state 1 published, -2 trashed
     *
     * @var int
     */
    var $state;
    /**
     * Date of file upload in GMT0
     *
     * @var string MySQL datetime format
     */
    var $upload;
    /**
     * Joomla user ID who uploaded file
     *
     * @var int ID from table #__users
     */
    var $uploader;
    /**
     * Cleaned content of file to full text search
     *
     * @var string
     */
    var $content;

    /**
     * Create object and set database conector
     *
     * @param JDatabaseMySQL $db
     */
    function __construct (&$db) {
        parent::__construct('#__joomdoc_file', 'id', $db);
    }

    /**
     * Store file row into database
     *
     * @param boolean $versions save file version
     */
    function store ($versions = false, $reindex = true, $userId = null) {
        $this->id = null;
                if (!$versions) {
            $this->_db->setQuery('SELECT `id` FROM `#__joomdoc_file` WHERE `path` = ' . $this->_db->Quote($this->path));
            // if versioning turn off update exists row
            $this->id = $this->_db->loadResult();
        }
        $this->version = 1;
                $this->state = JOOMDOC_STATE_PUBLISHED;
        // current datetime in GMT0 as upload date
        $this->upload = JFactory::getDate()->toSql();
        // current user as uploader
        $this->uploader = $userId ? $userId : JFactory::getUser()->id;
                $success = parent::store();
        if ($success && $reindex)
			JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($this->path);
                return $success;
    }    
    
    /**
     * Delete files and documents with their versions with path.
     *
     * @param array $paths absolute path to deleted files
     * @return array absolute paths of files and their versions to delete
     */
    function delete ($paths = null) {
        // prepare data
        foreach ($paths as $key => $path) {
            // search selected files
            $paths[$key] = $this->_db->Quote($path);
            // search children
            $childrenPaths[] = '`path` LIKE ' . $this->_db->Quote($path . DIRECTORY_SEPARATOR . '%');
        }

        // sql format where
        $where = 'WHERE `path` IN (' . implode(', ', $paths) . ') OR ' . implode(' OR ', $childrenPaths);

        // search files row and rows their children
        $query = 'SELECT `path` FROM `#__joomdoc_file` ' . $where;
        $this->_db->setQuery($query);
        // complet tree parents and children with versions
        $tree = $this->_db->loadColumn();

        // delete tree
        $query = 'DELETE FROM `#__joomdoc_file` ' . $where;
        $this->_db->setQuery($query);
        $this->_db->query();

        // delete tree documents
        $query = 'DELETE FROM `#__joomdoc` ' . $where;
        $this->_db->setQuery($query);
        $this->_db->query();

        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($paths);
        
        return $tree;
    }

    /**
     * Rename file/folder. Change their path and path of their children in file and document.
     *
     * @param string $oldPath
     * @param string $newPath
     */
    function rename ($oldPath, $newPath) {
        $oldPathParent = $this->_db->Quote($oldPath);
        $oldPathChildren = $this->_db->Quote($oldPath . DIRECTORY_SEPARATOR . '%');
        $newPathParent = $this->_db->Quote($newPath);
        $newPathChildren = $this->_db->Quote($newPath);

        $query = 'UPDATE `#__joomdoc` SET path = replace(path,' . $oldPathParent . ',' . $newPathParent . ') WHERE `path` = ' . $oldPathParent . ' OR `path` LIKE ' . $oldPathChildren;
        $this->_db->setQuery($query);
        $this->_db->query($query);
        $query = 'UPDATE `#__joomdoc_file` SET path = replace(path,' . $oldPathParent . ',' . $newPathParent . ') WHERE `path` = ' . $oldPathParent . ' OR `path` LIKE ' . $oldPathChildren;
        $this->_db->setQuery($query);
        $this->_db->query($query);
        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat(array($oldPath, $newPath));
    }
    /**
     * Copy/move files and documents database rows.
     *
     * @param string $oldPath old file relative path
     * @param string $newPath new file relative path
     * @param boolean $move false copy, true move
     */
    function copyMove ($oldPath, $newPath, $move) {

        $app = JFactory::getApplication();
        /* @var $app JApplication */
        $toflat = array($oldPath, $newPath);
        
        $oldPath = $this->_db->quote($oldPath);

        $newParentPath = JoomDOCFileSystem::getParentPath($newPath);
        $newParentPathQuote = $this->_db->quote($newParentPath);

        $newPath = $this->_db->quote($newPath);

        if (!$move) {
            // get IDs of all old rows
            $this->_db->setQuery('SELECT `id` FROM `#__joomdoc_file` WHERE `path` = ' . $oldPath);
            $fileIDs = $this->_db->loadColumn();

            $this->_db->setQuery('SELECT `id` FROM `#__joomdoc` WHERE `path` = ' . $oldPath);
            $docIDs = $this->_db->loadColumn();
            
            // copy all old rows
            foreach ($fileIDs as $id)
                $newFileIDs[] = JoomDOCModelList::copyRow('#__joomdoc_file', 'id', $id);

            foreach ($docIDs as $id)
                $newDocIDs[] = JoomDOCModelList::copyRow('#__joomdoc', 'id', $id);

            // update path in new rows
            if (isset($newFileIDs)) {
                $this->_db->setQuery('UPDATE `#__joomdoc_file` SET `path` = ' . $newPath . ' WHERE `id` IN (' . implode(', ', $newFileIDs) . ')');
                $this->_db->query();
            }
            // update path and parent path in new rows
            if (isset($newDocIDs)) {
                $newDocIDs = implode(',', $newDocIDs);
                $this->_db->setQuery('UPDATE `#__joomdoc` SET `path` = ' . $newPath . ', `parent_path` = ' . $newParentPathQuote . ' WHERE `id` IN (' . $newDocIDs . ')');
                $this->_db->query();
                $this->_db->setQuery('SELECT `id`, `alias` FROM `#__joomdoc` WHERE `id` IN (' . $newDocIDs . ')');
                $newDocs = $this->_db->loadObjectList();
            }
        } else {
            // for move only update paths
            $this->_db->setQuery('UPDATE `#__joomdoc_file` SET `path` = ' . $newPath . ' WHERE `path` = ' . $oldPath);
            $this->_db->query();

            $this->_db->setQuery('UPDATE `#__joomdoc` SET `path` = ' . $newPath . ', `parent_path` = ' . $newParentPathQuote . ' WHERE `path` = ' . $oldPath);
            $this->_db->query();

            $this->_db->setQuery('SELECT `id`, `alias` FROM `#__joomdoc` WHERE `path` = ' . $newPath);
            $newDocs = $this->_db->loadObjectList();
        }
        if (isset($newDocs)) {
            // get alias of last version of new parent
            $this->_db->setQuery('SELECT `alias` FROM `#__joomdoc` WHERE `path` = ' . $newParentPathQuote . ' ORDER BY `version` DESC');
            $parentAlias = $this->_db->loadResult();
            if (is_null($parentAlias))
                $parentAlias = $newParentPath;
            foreach ($newDocs as $newDoc) {
                // new full alias from new parent alias and new document alias
                $newDoc->full_alias = $parentAlias . '/' . $newDoc->alias;
                // update path, parent path and full alias in new document
                $this->_db->setQuery('UPDATE `#__joomdoc` SET `full_alias` = ' . $this->_db->quote($newDoc->full_alias) . ' WHERE `id` = ' . $newDoc->id);
                $this->_db->query();
            }
        }
        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($toflat);
    }

    /**
     * Set selected File's Version's as trashed.
     * Only no last Version's can be trashed.
     *
     * @param int $cid Array of Files DB Row's ID's
     * @param string $path Path to control if ID's are in allowed Path
     * @return int Count of affected Item's
     */
    function trash ($cid, $path) {
        if (is_array($cid) && count($cid)) {
            JArrayHelper::toInteger($cid);
            $this->_db->setQuery('SELECT MAX(`id`) FROM `#__joomdoc_file` GROUP BY `path`');
            $lid = $this->_db->loadColumn();
            // filter selected ID's for no last Version's
            $query = 'SELECT `id` FROM `#__joomdoc_file` WHERE `id` IN (' . implode(', ', $cid) . ') ';
            if ($lid)
	            $query .= ' AND `id` NOT IN (' . implode(', ', $lid) . ')';
            $this->_db->setQuery($query);
            $cid = $this->_db->loadColumn();
            return $this->setStates($cid, $path, JOOMDOC_STATE_TRASHED);
        }
    }

    /**
     * Untrash selected File. Search for latest version and publish it.
     * Others versions leave trashed.
     *
     * @param string $path
     * @return boolean
     */
    function untrash ($path) {
        $query = 'SELECT MAX(`id`) FROM `#__joomdoc_file` WHERE `path` = ' . $this->_db->quote($path);
        $this->_db->setQuery($query);
        $toUntrash = (int) $this->_db->loadResult();
        $query = 'UPDATE `#__joomdoc_file` SET `state` = ' . JOOMDOC_STATE_PUBLISHED . ' WHERE `id` = ' . $toUntrash;
        $this->_db->setQuery($query);
        $success = $this->_db->query();
        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($path);
        return $success;
    }

    /**
     * Set selected File's Version's as trashed.
     *
     * @param int $cid Array of Files DB Row's ID's
     * @param string $path Path to control if ID's are in allowed Path
     * @return int Count of affected Item's
     */
    function restore ($cid, $path) {
        return $this->setStates($cid, $path, JOOMDOC_STATE_PUBLISHED);
    }

    /**
     * Set selected File's Version's State.
     *
     * @param int $cid Array of Files DB Row's ID's, if is false use only path
     * @param string $path Path to control if ID's are in allowed Path
     * @param int $state State Value
     * @return int Count of affected Item's
     */
    function setStates ($cid = false, $path, $state) {
        if (is_array($cid) && count($cid)) {
            JArrayHelper::toInteger($cid);
            $query = 'UPDATE `#__joomdoc_file` SET `state` = ' . $state . ' WHERE `id` IN (' . implode(', ', $cid) . ') AND `path` = ' . $this->_db->quote($path);
        } elseif ($cid === false) {
            $query = 'UPDATE `#__joomdoc_file` SET `state` = ' . $state . ' WHERE `path` IN (' . implode(', ', array_map(array($this->_db, 'quote'), $path)) . ')';
        }
        if (isset($query)) {
            $this->_db->setQuery($query);
            $this->_db->query();
            $success = $this->_db->getAffectedRows();
            JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($path);
            return $success;
        }
        return 0;
    }

    /**
     * Revert selected Version as last Version.
     *
     * @param int $id restored Version row ID
     * @param string $path restored Version Path
     * @return JObject
     *     revertVersion   reverted Version Number
     *     oldLastVersion  archived last Version Number
     *     newLastVersion  new last Version from reverted Version number
     */
    function revert ($id, $path) {
        $safePath = $this->_db->quote($path);
        $this->_db->setQuery('SELECT MAX(`id`) FROM `#__joomdoc_file` WHERE `path` = ' . $safePath . ' GROUP BY `path`');
        $cid = $this->_db->loadColumn(); // the latest file version list
        // get Row with given ID and given Path which isn't last Version - candidate to revert as last Version
        $query = 'SELECT `id`, `version` FROM `#__joomdoc_file` WHERE `id` = ' . (int) $id . ' AND `path` = ' . $safePath;
		if ($cid)
	        $query .= ' AND `id` NOT IN (' . implode(', ', $cid) . ')';
        $this->_db->setQuery($query);
        $candidate = $this->_db->loadObject();
        if (is_null($candidate)) {
            return false;
        }
        // get last Version number
        $query = 'SELECT MAX(`version`) FROM `#__joomdoc_file` WHERE `path` = ' . $safePath;
        $this->_db->setQuery($query);
        $lastVersion = $this->_db->loadResult();
        if (is_null($lastVersion)) {
            return false;
        }
        // full Path of last Version
        $lastVersionPath    = JoomDOCFileSystem::getFullPath($path);
        // full Path of revert Version
        $revertVersionPath  = JoomDOCFileSystem::getVersionPath($lastVersionPath, $candidate->version, true);
        // full Path to archive last Version
        $archiveVersionPath = JoomDOCFileSystem::getVersionPath($lastVersionPath, $lastVersion, true);
        // move last Version as archive Version
        if ( !JFile::move($lastVersionPath, $archiveVersionPath) ) {
            return false;
        }
        
        // copy revert Version as last Version
        if ( !JFile::copy($revertVersionPath, $lastVersionPath) ) {
            return false;
        }
        
        // copy revert Version DB Row
        $newLastVersionID = JoomDOCModelList::copyRow( '#__joomdoc_file', 'id', $candidate->id );
        // increment new last Version number
        $newLastVersion   = $lastVersion + 1;
        $query = 'UPDATE `#__joomdoc_file` SET `version` = ' . $newLastVersion . ', `state` = ' . JOOMDOC_STATE_PUBLISHED . ' WHERE `id` = ' . $newLastVersionID;
        $this->_db->setQuery( $query );
        $this->_db->query();
        $output = new JObject();
        $output->revertVersion  = $candidate->version;
        $output->oldLastVersion = $lastVersion;
        $output->newLastVersion = $newLastVersion;
        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($path);
        return $output;
    }
}
?>