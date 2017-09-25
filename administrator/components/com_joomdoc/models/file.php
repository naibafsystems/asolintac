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

class JoomDOCModelFile extends JoomDOCModelList {
    /**
     * Get list of file versions.
     *
     * @param JObject $filter
     * @return array
     */
    function getData (&$filter) {
        $joins = ' FROM `#__joomdoc_file` AS `file` ';
        $joins .= 'LEFT JOIN `#__users` AS `uploader` ON `file`.`uploader` = `uploader`.`id` ';

        $where = ' WHERE `path` = ' . $this->_db->quote($filter->path);
        
        if (JString::trim($filter->uploader))
        	$where .= ' AND LOWER(`uploader`.`name`) LIKE ' . $this->_db->quote('%' . $filter->uploader . '%') . ' ';
        
        if ($filter->state != 0) {
            $where .= 'AND `state` = ' . $filter->state . ' ';
        }

        $this->_db->setQuery('SELECT COUNT(*)' . $joins . $where);
        $filter->total = (int) $this->_db->loadResult();

        if ($filter->total <= $filter->offset && $filter->limit) {
            $filter->offset = floor($filter->total / $filter->limit) * $filter->limit;
        }

        $query = 'SELECT `file`.*, `uploader`.`name` ';
        $query .= $joins . $where . ' ORDER BY `' . $filter->listOrder . '` ' . $filter->listDirn;

        return $this->_getList($query, $filter->offset, $filter->limit);
    }

    /**
     * Get full database Row by Path and Version.
     *
     * @param string $path
     * @param int $version if null get last version
     * @return stdClass null if not found
     */
    function getItem ($path, $version = null) {
        $query = 'SELECT * FROM `#__joomdoc_file` WHERE `path` = ' . $this->_db->quote($path);
        if ($version) {
            // concrete version
            $query .= ' AND `version` = ' . (int) $version;
        } else {
            // last version
            $query .= ' ORDER BY id DESC';
        }
        $this->_db->setQuery($query);
        return $this->_db->loadObject();
    }

    /**
     * Get maximum, published File Version by Path.
     *
     * @param string $path relativ Path
     * @return int
     */
    function getMaxVersion ($path) {
        $query = 'SELECT MAX(`version`) FROM `#__joomdoc_file` WHERE `path` = ' . $this->_db->quote($path) . ' AND `state` = ' . JOOMDOC_STATE_PUBLISHED . ' GROUP BY `path`';
        $this->_db->setQuery($query);
        return (int) $this->_db->loadResult();
    }

    /**
     * Get file last version document.
     *
     * @param mixed $filter stdClass or null if not found
     */
    public function getDocument (&$filter) {
        $this->_db->setQuery('SELECT * FROM `#__joomdoc` WHERE `path` = ' . $this->_db->quote($filter->path) . ' ORDER BY `version` DESC', 0, 1);
        return $this->_db->loadObject();
    }

    /**
     * Save file download hits.
     *
     * @param string $path file relative path
     * @return boolean
     */
    public function saveHits ($path, $version = null) {
    	        $this->_db->setQuery('UPDATE `#__joomdoc_file` SET `hits`=`hits`+1 WHERE `path` = ' . $this->_db->quote($path) . ' AND `version` = ' . (int) $version);
        return $this->_db->query();
    }
    /**
     * Set selected File's Versions's as trashed.
     *
     * @param int $cid Array of Files DB Row's ID's
     * @param string $path Path to control if ID's are in allowed Path
     * @return int Count of affected Item's
     */
    public function trash ($cid, $path) {
        return $this->setStates($cid, $path, JOOMDOC_TASK_TRASH);
    }

    /**
     * Untrash selected File. Search for latest version and publish it.
     * Others versions leave trashed.
     *
     * @param string $path
     * @return boolean
     */
    public function untrash ($path) {
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */
        return $table->untrash($path);
    }

    /**
     * Set selected File's Version's as published.
     *
     * @param int $cid Array of Files DB Row's ID's
     * @param string $path Path to control if ID's are in allowed Path
     * @return int Count of affected Item's
     */
    public function restore ($cid, $path) {
        return $this->setStates($cid, $path, JOOMDOC_TASK_RESTORE);
    }

    /**
     * Set selected File's Version's State.
     *
     * @param int $cid Array of Files DB Row's ID's
     * @param string $path Path to control if ID's are in allowed Path
     * @param string $state Name of new State (trash, restore)
     * @return int Count of affected Item's
     */
    public function setStates ($cid, $path, $state) {
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */
        return call_user_func(array($table, $state), $cid, $path);
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
    public function revert ($id, $path) {
        $table = JTable::getInstance(JOOMDOC_FILE, JOOMDOC_TABLE_PREFIX);
        /* @var $table JoomDOCTableFile */
        return $table->revert($id, $path);
    }
        
    /**
     * Get a file relative path by the file ID.
     * 
     * @param int $fileId ID of the file last version
     * @return string relative path of the file
     */
    public function getPathById($fileId) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('path')
                ->from('#__joomdoc_file')
                ->where('id = ' . $fileId);        
        return $db->setQuery($query)->loadResult();
    }
}
?>