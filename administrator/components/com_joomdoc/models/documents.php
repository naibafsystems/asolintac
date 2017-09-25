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

class JoomDOCModelDocuments extends JoomDOCModelList {

    /**
     * Paths to search documents.
     *
     * @var array
     */
    var $paths;

    /**
     * Database connector.
     *
     * @var JDatabaseMySQL
     */
    var $_db;
    /**
     * Create object and set filter.
     *
     * @param array $config
     * @return void
     */
    function __construct ($config = array ()) {

        $this->filter[JOOMDOC_FILTER_TITLE] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_FILENAME] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_ACCESS] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_CATEGORY] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_STATE] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_ORDERING] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_DIRECTION] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_START] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_LINKS] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_LIMIT] = JOOMDOC_INT;
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(JOOMDOC_FILTER_ID, JOOMDOC_FILTER_TITLE, JOOMDOC_FILTER_STATE, JOOMDOC_FILTER_ACCESS, JOOMDOC_FILTER_CREATED, JOOMDOC_FILTER_ORDERING, JOOMDOC_ORDER_ORDERING, JOOMDOC_FILTER_HITS, JOOMDOC_FILTER_PUBLISH_UP, JOOMDOC_FILTER_PUBLISH_DOWN, JOOMDOC_FILTER_PATH, JOOMDOC_FILTER_UPLOAD);
        }

        parent::__construct($config);
    }

    /**
     * Get SQL query for documents list.
     *
     * @return string
     */
    protected function getListQuery () {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        $config = JoomDOCConfig::getInstance();

        // have concrete paths of search files
        if (count(($paths = $this->getState(JOOMDOC_FILTER_PATHS)))) {
            // cleanup paths and quote
            $paths = $this->getQuotedArray($paths);
            if (count($paths) && (!isset($this->docids) && !isset($this->fileids))) {
                $paths = implode(', ', $paths);
                // search latest version of files document
                $this->_db->setQuery('SELECT MIN(`id`) FROM `#__joomdoc` WHERE `path` IN (' . $paths . ') GROUP BY `path`');
                $this->docids = $this->_db->loadColumn();
                // search lastest version ids of file
                $this->_db->setQuery('SELECT MAX(`id`) FROM `#__joomdoc_file` WHERE `path` IN (' . $paths . ') GROUP BY `path`');
                $this->fileids = $this->_db->loadColumn();
                $this->_db->setQuery('SELECT `path`, SUM(`hits`) AS `hits` FROM `#__joomdoc_file` WHERE `path` IN (' . $paths . ') GROUP BY `path`');
                $this->hits = $this->_db->loadObjectList('path');
            }
        }

        if (!isset($this->docids) && !isset($this->fileids)) {
            $this->docids = $this->fileids = array();
        }

        if ($mainframe->isSite()) {

            /* on site control document access */
            $published = JoomDOCModelList::getDocumentPublished();

            $query = 'SELECT `document`.`id`, `document`.`title`, `document`.`description`, `document`.`full_alias`, `document`.`modified`, `document`.`created`, `document`.`created_by`, `document`.`state`, `document`.`params`, `document`.`favorite`, `document`.`ordering`, `document`.`publish_up`, (' . $published . ') AS `published`, `file`.`upload`, `file`.`path`, `file`.`state` AS `file_state`, `document`.`checked_out`, `license`.`id` AS `license_id`, `license`.`title` AS `license_title`, `license`.`alias` AS `license_alias`, `license`.`state` AS `license_state` ';

        } else {
            $query = 'SELECT `file`.`upload`, `file`.`path`, `file`.`state` AS `file_state`, `document`.`id`, `document`.`title`, `document`.`ordering`, `document`.`access`, `document`.`publish_up`, `document`.`publish_down`, `document`.`state` AS `published`, `document`.`checked_out`, `document`.`checked_out_time`, `document`.`created_by`, `document`.`state`, `document`.`favorite`, `document`.`parent_path`, `editor`.`name` AS `editor`, `access`.`' . ($config->documentAccess == 1 ? 'title' : 'name') . '` AS `access_title`, `document`.`full_alias`, `license`.`id` AS `license_id`, `license`.`title` AS `license_title`, `license`.`alias` AS `license_alias`, `file`.`id` AS `file_id` ';
        }

        // complet query from/join state
        $query .= 'FROM `#__joomdoc_file` AS `file` ';
        $query .= 'LEFT JOIN `#__joomdoc` AS `document` ON `file`.`path` = `document`.`path` ';
        // user who checked out document
        $query .= 'LEFT JOIN `#__users` AS `editor` ON `editor`.`id` = `document`.`checked_out` ';
        // document access name, in Joomla 1.6.x is used different table then Joomla 1.5.x
        $query .= 'LEFT JOIN `#__' . ($config->documentAccess == 1 ? 'viewlevels' : 'users') . '` AS `access` ON `access`.`id` = `document`.`access` ';
        // document license
        $query .= 'LEFT JOIN `#__joomdoc_license` AS `license` ON `license`.`id` = `document`.`license` ';

        // filter for files
        if (count($this->fileids)) {
            $where[] = '(`file`.`id` IN (' . implode(', ', $this->fileids) . ') OR `file`.`id` IS NULL)';
        }
        // filter for documents
        if (count($this->docids)) {
            $where[] = '(`document`.`id` IN (' . implode(', ', $this->docids) . ') OR `document`.`id` IS NULL)';
        }
        // without output
        if (!count($this->fileids) && !count($this->docids)) {
            $where[] = '0';
        }
        // in frontend only published files in backend according to user filter
        if ($mainframe->isSite()) {
            $where[] = '`file`.`state` = ' . JOOMDOC_STATE_PUBLISHED;
        } elseif ($this->state->get(JOOMDOC_FILTER_STATE) != 0) {
            $where[] = '`file`.`state` = ' . $this->state->get(JOOMDOC_FILTER_STATE);
        }

        $filter = $this->getState(JOOMDOC_FILTER_SEARCH);
		
        // filter for keywords
        if (!empty($filter) && ($filter->keywords || !empty($filter->fields))) {

        	$extra = array();
        	
        	if (!empty($filter->fields)) {
        		foreach ($filter->fields as $id => $data) {
        			if ($data['type'] == JOOMDOC_FIELD_DATE || $data['type'] == JOOMDOC_FIELD_RADIO || $data['type'] == JOOMDOC_FIELD_SELECT) {
        				if ($data['value'] !== '') {        			
        					$where[] = '`document`.`field' . $id . '` = ' . $this->_db->quote($data['value']);
        				}
        			} elseif ($data['type'] == JOOMDOC_FIELD_CHECKBOX || $data['type'] == JOOMDOC_FIELD_MULTI_SELECT || $data['type'] == JOOMDOC_FIELD_SUGGEST) {
        				foreach ($data['value'] as $var => $val) {
        					if ($val !== '') {
        						$where[] = '`document`.`field' . $id . '` LIKE ' . $this->_db->quote('%"' . $val . '"%');
        					}
        				}
        			} else {
        				if ($data['value']) {
        					$extra[] = $id; // text field (text, textarea, editor) use in keyword searching
        				}
        			}
        		}
        	}
        	
            $keywords = JString::strtolower($filter->keywords);

            if ($filter->type == JOOMDOC_SEARCH_ANYKEY || $filter->type == JOOMDOC_SEARCH_ALLKEY) {
                // search for any/all word from keywords
                // split to unique words
                $keywords = explode(' ', $keywords);
            } else {
                // search for full phrase
                $keywords = array($keywords);
            }
            // cleanup and quote words
            $keywords = $this->getQuotedArray($keywords, $filter->type != JOOMDOC_SEARCH_REGEXP);

            foreach ($keywords as $keyword) {
                $criteria = array();
                foreach ($extra as $id) {
                	if ($filter->type == JOOMDOC_SEARCH_REGEXP)
                		$criteria[] = '`document`.`field' . $id . '` REGEXP ' . $keyword;
                	else
                		$criteria[] = 'LOWER(`document`.`field' . $id . '`) LIKE ' . $keyword;
                }
                if ($filter->areaTitle) {
                    // document title or file path
                    if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                        $criteria[] = '`document`.`title` REGEXP ' . $keyword;
                        $criteria[] = '`file`.`path` REGEXP ' . $keyword;
                    } else {
                        $criteria[] = 'LOWER(`document`.`title`) LIKE ' . $keyword;
                        $criteria[] = 'LOWER(`file`.`path`) LIKE ' . $keyword;
                    }
                }
                if ($filter->areaText) {
                    // document description
                    if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                        $criteria[] = '`document`.`description` REGEXP ' . $keyword;
                    } else {
                        $criteria[] = 'LOWER(`document`.`description`) LIKE ' . $keyword;
                    }
                }
                if ($filter->areaFull) {
                    // file content
                    if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                        $criteria[] = '`file`.`content` REGEXP ' . $keyword;
                    } else {
                        $criteria[] = 'LOWER(`file`.`content`) LIKE ' . $keyword;
                    }
                }
                if ($filter->areaMeta) {
                    // need non escaped form
                    $keyword = JString::substr($keyword, 1, JString::strlen($keyword) - 2);
                    
                    if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                        $metakeywords = $this->_db->quote('.*"metakeywords":"' . $keyword . '".*');
                        $metadescription = $this->_db->quote('.*"metadescription":"' . $keyword . '".*');
                    } else {
                        $metakeywords = $this->_db->quote('%"metakeywords":"' . $keyword . '"%');
                        $metadescription = $this->_db->quote('%"metadescription":"' . $keyword . '"%');
                    }
                    if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                        $criteria[] = '`document`.`params` REGEXP ' . $metakeywords;
                        $criteria[] = '`document`.`params` REGEXP ' . $metadescription;
                    } else {
                        $criteria[] = 'LOWER(`document`.`params`) LIKE ' . $metakeywords;
                        $criteria[] = 'LOWER(`document`.`params`) LIKE ' . $metadescription;
                    }
                }
                // find word in one of items
                if (count($criteria)) {
                    $oneWordFilter[] = '(' . implode(' OR ', $criteria) . ')';
                }
            }
            if (isset($oneWordFilter)) {
                if ($filter->type == JOOMDOC_SEARCH_ALLKEY) {
                    // all words
                    $where[] = '(' . implode(' AND ', $oneWordFilter) . ')';
                } else {
                    // any word
                    $where[] = '(' . implode(' OR ', $oneWordFilter) . ')';
                }
            }
        }

        if (isset($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        if (in_array($this->state->get(JOOMDOC_FILTER_ORDERING), array(JOOMDOC_ORDER_PATH, JOOMDOC_ORDER_UPLOAD, JOOMDOC_ORDER_HITS, JOOMDOC_ORDER_TITLE, JOOMDOC_ORDER_ORDERING))) {
           // $query .= ' ORDER BY `' . $this->_db->getEscaped($this->state->get(JOOMDOC_FILTER_ORDERING) . '` ' . JString::strtoupper($this->state->get(JOOMDOC_FILTER_DIRECTION)));
        }

        return $query;
    }

    /**
     * Get documents list.
     *
     * @return array
     */
    public function getItems ($searchActive = false) {
        $items = $this->_getList($this->getListQuery($searchActive));
        $count = count($items);
        for ($i = 0; $i < $count; $i++) {
            $item =& $items[$i];
            $item->hits = isset($this->hits[$item->path]) ? $this->hits[$item->path]->hits : 0;
        }

        return $items;
    }
    
    public function getTotal($searchActive = false) {
    	$query = $this->getListQuery($searchActive);
		$total = (int) $this->_getListCount((string) $query);
		return $total;
    }

    /**
     * Prepare string array to using in SQL query.
     * Walk array and remove empty strings.
     * Non empty string escape and quote.
     *
     * @param array $items
     * @param boolean $search use for search, add % at begin and the end of item
     * @return array
     */
    public function getQuotedArray ($items, $search = false) {
        $mark = $search ? '%' : '';
        foreach ($items as $i => $item) {
            $item = JString::trim($item);
            if ($item)
                $items[$i] = $this->_db->quote($mark . $item . $mark);
            else
                unset($items[$i]);
        }
        // reindexing
        $items = array_merge($items);
        return $items;
    }

    /**
     * Get document titles for selected paths.
     *
     * @param array $paths relative paths
     * @return array stdClass with params title and path (title can be empty if file hasn't document)
     */
    public function getPathsDocsTitles ($paths) {
        $paths = $this->getQuotedArray($paths);
        if (!count($paths))
            return array();
        $query = 'SELECT `title`, `path`, `full_alias` ';
        $query .= 'FROM `#__joomdoc_flat` AS `document` ';
        $query .= 'WHERE `path` IN (' . implode(', ', $paths) . ') AND ((' . JoomDOCModelList::getDocumentPublished() . ') OR `document`.`id` IN (NULL, 0))';
        $this->_db->setQuery($query);        
        return $this->_db->loadObjectList();
    }
    
    /**
     * Filter path's list for trashed items.
     * 
     * @param array $paths relative path's of items
     * @since 3.3.1
     * @return array relative path's of non trashed items
     */
    public function getNonTrashedFiles($paths) {
    	if (empty($paths)) return array();
    	$paths = array_map(array($this->_db, 'quote'), $paths);
        
    	$this->_db->setQuery('SELECT MAX(`id`) FROM `#__joomdoc_file` WHERE `state` <> ' . JOOMDOC_STATE_TRASHED . ' AND `path` IN (' . implode(', ', $paths) . ') GROUP BY `path`');
        
        $files = $this->_db->loadColumn(); // ids of non trashed latest versions
        
        if (empty($files)) return array();
        $this->_db->setQuery('SELECT `path` FROM `#__joomdoc_file` WHERE `id` IN (' . implode(', ', $files) . ')');
        return $this->_db->loadColumn();
    }

    /**
     * Set documents as favorite/unfavorite
     *
     * @param array $ids   documents IDs
     * @param int   $value value use constantS JOOMDOC_FAVORITE/JOOMDOC_STANDARD to set as favorite/unfavorite
     * @return int num of affected rows
     */
    function setFavorite ($ids, $value) {
        if (count($ids)) {
            JArrayHelper::toInteger($ids);
            $this->_db->setQuery(sprintf('UPDATE `#__joomdoc` SET `favorite` = %d WHERE `id` IN (%s)', $value, implode(', ', $ids)));
            $this->_db->query();
            JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat(null, $ids);
            return $this->_db->getAffectedRows();
        }
        return 0;
    }

    function searchPaths ($paths) {
        if (count($paths)) {
            $this->_db->setQuery('SELECT DISTINCT `path` FROM `#__joomdoc_file` WHERE `path` IN (' . implode(', ', array_map(array($this->_db, 'quote'), $paths)) . ')');
            return $this->_db->loadColumn();
        }
        return array();
    }
    
    function getSearchableFields() {
    	$fields = JModelLegacy::getInstance(JOOMDOC_FIELDS, JOOMDOC_MODEL_PREFIX);
    	/* @var $fields JoomdocModelFields */
    	return $fields->getAllowedFields('joomdoc.field.searchable');
    }
    
    public function getListFields() {
    	$fields = JModelLegacy::getInstance(JOOMDOC_FIELDS, JOOMDOC_MODEL_PREFIX);
    	/* @var $fields JoomdocModelFields */
    	return $fields->getAllowedFields('joomdoc.field.list');
    }
    
    public function flat($path = null, $ids = null) {
    	
    	$db = $this->getDbo();
    	
    	$app = JFactory::getApplication();
    	
    	if (count($ids))
    		$path = $db->setQuery('SELECT path FROM #__joomdoc WHERE id IN (' . implode(', ', array_map(array($db, 'quote'), $ids)) . ')')->loadColumn();
    	elseif ($path)
    		$path = (array) $path;
    	
    	$path = $path ? implode(', ', array_map(array($db, 'quote'), $path)) : null;
    	
    	$qw = $path ? "WHERE path IN ($path)" : '';
    	$db->setQuery('CREATE TEMPORARY TABLE IF NOT EXISTS #__joomdoc_findex (file_id INT, path TEXT)')->query();
    	$db->setQuery('CREATE TEMPORARY TABLE IF NOT EXISTS #__joomdoc_dindex (document_id INT, path TEXT)')->query();
    	$db->setQuery('CREATE TEMPORARY TABLE IF NOT EXISTS #__joomdoc_index (file_id INT, document_id INT)')->query();
    	$db->setQuery("INSERT INTO #__joomdoc_findex SELECT MAX(id), path FROM #__joomdoc_file $qw GROUP BY path")->query();
    	$db->setQuery("INSERT INTO #__joomdoc_dindex SELECT MIN(id), path FROM #__joomdoc $qw GROUP BY path")->query();
    	$db->setQuery('INSERT INTO #__joomdoc_index SELECT file_id, document_id FROM #__joomdoc_findex AS f LEFT JOIN #__joomdoc_dindex AS d USING(path)')->query();
    
    	$path ? $db->setQuery("DELETE FROM #__joomdoc_flat WHERE path IN ($path) OR parent_path IN ($path)")->query() : $db->setQuery('TRUNCATE TABLE #__joomdoc_flat')->query();

    	$fs = $db->setQuery('SELECT id FROM #__joomdoc_field')->loadColumn();
    	for ($qf = '', $qc = '', $c = count($fs), $i = 0; $i < $c; $qf .= ', d.field' . $fs[$i], $qc .= ', field' . $fs[$i], $i ++); // flat custom fields as well
    	
    	$qw = $path ? "WHERE f.path IN ($path) OR d.parent_path IN ($path)" : '';
    	
    	$db->setQuery("INSERT INTO #__joomdoc_flat (id, file_id,title, alias, full_alias, description, state, file_state, access, download, params, ordering, created, created_by, modified, modified_by, upload, uploader, publish_up, publish_down, parent_path, path, version, file_version, favorite, license_id, license_title, license_alias, license_state, hits, content $qc)
			               SELECT d.id, f.id AS file_id, d.title, d.alias, d.full_alias, d.description, d.state, f.state AS file_state, d.access, d.download, d.params, d.ordering, d.created, d.created_by, d.modified, d.modified_by, f.upload, f.uploader, d.publish_up, d.publish_down, d.parent_path, f.path, d.version, f.version AS file_version, d.favorite, l.id AS license_id, l.title AS license_title, l.alias AS license_alias, l.state AS license_state, f.hits, f.content $qf
			               FROM #__joomdoc_index AS i
    		               LEFT JOIN #__joomdoc_file AS f ON f.id = i.file_id
    		               LEFT JOIN #__joomdoc AS d ON d.id = i.document_id
    		               LEFT JOIN #__joomdoc_license AS l ON d.license = l.id $qw")->query();
    }
}
?>