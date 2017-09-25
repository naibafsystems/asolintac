<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage		JoomDOC
 * @author      	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright		Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

JLoader::register('JoomDOCModelDocuments', JOOMDOC_ADMINISTRATOR . '/models/documents.php');

class JoomDOCSiteModelDocuments extends JoomDOCModelDocuments {

    /**
     * Get SQL query for documents list.
     *
     * @return string
     */
    protected function getListQuery ($searchActive = false) {
        $config = JoomDOCConfig::getInstance();
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */

        // have concrete paths of search files
        $paths = $this->getState(JOOMDOC_FILTER_PATHS);
        // cleanup paths and quote
        $paths = $this->getQuotedArray($paths);
        if (count($paths))
	        if (JRequest::getVar('com_joomdoc_scope') != 'mod_joomdoc_explorer') {
				$this->_db->setQuery('SELECT `path`, SUM(`hits`) AS `hits` FROM `#__joomdoc_file` ' . ($searchActive ? '' : 'WHERE `path` IN (' . implode(', ', $paths) . ')') . ' GROUP BY `path`');
				$this->hits = $this->_db->loadObjectList('path');
			}

        if (!isset($this->docids) && !isset($this->fileids)) {
            $this->docids = $this->fileids = array();
        }

        $published = JoomDOCModelList::getDocumentPublished();
        $fields = $this->getListFields();

        $query = 'SELECT `id`, `title`, `description`, `full_alias`, `modified`, `created`, `created_by`, `state`, `params`, `favorite`, `ordering`, `publish_up`, (' . $published . ') AS `published`, `upload`, `parent_path`, `path`, `file_state`,  `license_id`, `license_title`, `license_alias`, `license_state`, `access`, `download`, `file_id` ';

        foreach ($this->getListFields() as $field)
        	$query .= ', `field' . $field->id . '` ';
        
        // load from flat index
        $query .= 'FROM `#__joomdoc_flat` AS `document`';

        // filter for files
        if (!$searchActive) {
            $where[] = !empty($paths) ? '`path` IN (' . implode(', ', $paths) . ')' : 0;
        }
        
        // published files only
       	$where[] = '`file_state` = ' . JOOMDOC_STATE_PUBLISHED;

        $filter = $this->getState(JOOMDOC_FILTER_SEARCH);
		
        // filter for keywords
        if ($searchActive) {

        	$extra = array();
        	
            if ($filter->parent) {
                $where[] = 'LOWER(`document`.`path`) LIKE ' . $this->_db->q(JString::strtolower($filter->parent) . '%');
            }
            
        	if (!empty($filter->fields)) {
        		foreach ($filter->fields as $id => $data) {
        			if ($data['type'] == JOOMDOC_FIELD_DATE || $data['type'] == JOOMDOC_FIELD_RADIO || $data['type'] == JOOMDOC_FIELD_SELECT) {
        				if ($data['value'] !== '') {        			
        					$where[] = '`field' . $id . '` = ' . $this->_db->quote($data['value']);
        				}
        			} elseif ($data['type'] == JOOMDOC_FIELD_CHECKBOX || $data['type'] == JOOMDOC_FIELD_MULTI_SELECT || $data['type'] == JOOMDOC_FIELD_SUGGEST) {
        				foreach ($data['value'] as $var => $val) {
        					if ($val !== '') {
                                // J!2.5 uses native JSON, J!3 converts JSON UCS-2BE into UTF-8 by MBString
        						$where[] = '(`field' . $id . '` LIKE ' . $this->_db->quote('%"' . $val . '"%') . ' OR `field' . $id . '` LIKE ' . $this->_db->quote('%' . $this->_db->escape(json_encode($val)) . '%') . ')';
        					}
        				}
        			} else {
        				if ($data['value'] || $config->searchKeyword == 2) {
        					$extra[] = $id; // text field (text, textarea, editor) use in keyword searching
        				}
        			}
        		}
        	}

            if ($config->searchKeyword == 1) { // single keyword for all areas             
                $keywords['global'] = JString::strtolower($filter->keywords);
            } elseif ($config->searchKeyword == 2) { // separate keyword for every area
                if ($config->searchShowTitle) { // document title
                    $keywords['title'] = JString::strtolower($filter->keywords_title);
                }
                if ($config->searchShowText) { // document description
                    $keywords['text'] = JString::strtolower($filter->keywords_text);
                }
                if ($config->searchShowMetadata) { // document metadata
                    $keywords['meta'] = JString::strtolower($filter->keywords_meta);
                }
                if ($config->searchShowFulltext) { // file content
                    $keywords['full'] = JString::strtolower($filter->keywords_full);
                }
                foreach ($extra as $id) { // custom text fields keyword
                    $keywords['field' . $id] = JString::strtolower($filter->get('keywords_field' . $id));
                }
            }

            if ($filter->type == JOOMDOC_SEARCH_ANYKEY || $filter->type == JOOMDOC_SEARCH_ALLKEY) {
                // search for any/all word from keywords
                // split to unique words
                foreach ($keywords as $type => $keys) {
                    $keywords[$type] = explode(' ', $keys);
                }
            } else {
                // search for full phrase
                foreach ($keywords as $type => $keys) {
                    $keywords[$type] = array($keys);
                }
            }
            // cleanup and quote words
            foreach ($keywords as $type => $keys) {
                $keywords[$type] = $this->getQuotedArray($keys, $filter->type != JOOMDOC_SEARCH_REGEXP);
            }

            foreach ($keywords as $type => $keys) {
                foreach ($keys as $keyword) {
                    $criteria = array();
                    foreach ($extra as $id) {
                        if ($type == 'global' || $type == ('field' . $id)) {
                            if ($filter->type == JOOMDOC_SEARCH_REGEXP)
                                $criteria[] = '`field' . $id . '` REGEXP ' . $keyword;
                            else
                                $criteria[] = 'LOWER(`field' . $id . '`) LIKE ' . $keyword;
                        }
                    }
                    if (($filter->areaTitle && $type == 'global') || $type == 'title') {
                        // document title or file path
                        if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                            $criteria[] = '`title` REGEXP ' . $keyword;
                            $criteria[] = '`path` REGEXP ' . $keyword;
                        } else {
                            $criteria[] = 'LOWER(`title`) LIKE ' . $keyword;
                            $criteria[] = 'LOWER(`path`) LIKE ' . $keyword;
                        }
                    }
                    if (($filter->areaText && $type == 'global') || $type == 'text') {
                        // document description
                        if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                            $criteria[] = '`description` REGEXP ' . $keyword;
                        } else {
                            $criteria[] = 'LOWER(`description`) LIKE ' . $keyword;
                        }
                    }
                    if (($filter->areaFull && $type == 'global') || $type == 'full') {
                        // file content
                        if ($filter->type == JOOMDOC_SEARCH_REGEXP) {
                            $criteria[] = '`content` REGEXP ' . $keyword;
                        } else {
                            $criteria[] = 'LOWER(`content`) LIKE ' . $keyword;
                        }
                    }
                    if (($filter->areaMeta && $type == 'globa') || $type == 'meta') {
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
                            $criteria[] = '`params` REGEXP ' . $metakeywords;
                            $criteria[] = '`params` REGEXP ' . $metadescription;
                        } else {
                            $criteria[] = 'LOWER(`params`) LIKE ' . $metakeywords;
                            $criteria[] = 'LOWER(`params`) LIKE ' . $metadescription;
                        }
                    }
                    // find word in one of items
                    if (count($criteria)) {
                        $oneWordFilter[] = '(' . implode(' OR ', $criteria) . ')';
                    }
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
        
        return $query;
    }
    
    public function setPathway($path) {
		$pieces = explode(DIRECTORY_SEPARATOR, $path);
    	foreach ($pieces as $offset => $piece)
    		$crumbs[] = implode(DIRECTORY_SEPARATOR, array_slice($pieces, 0, $offset + 1));
    	if (!empty($crumbs)) {
			$crumbs = $this->getDbo()
				->setQuery('SELECT path, title, full_alias 
				       	    FROM #__joomdoc_flat 
				            WHERE path IN (' . implode(', ', array_map(array($this->getDBO(), 'quote'), $crumbs)) . ')')->loadObjectList();
			$pathway = JFactory::getApplication()->getPathway();
			foreach ($crumbs as $crumb)
				$pathway->addItem($crumb->title ? $crumb->title : JFile::getName($crumb->path), JoomDOCRoute::viewDocuments($crumb->path), $crumb->full_alias);
    	}
    }
}