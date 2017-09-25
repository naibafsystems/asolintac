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

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

class plgSearchJoomdoc extends JPlugin {

    public function onContentSearchAreas() {
        JFactory::getLanguage()->load('plg_search_joomdoc', JPATH_ADMINISTRATOR);
        return array('joomdoc' => JText::_('PLG_SEARCH_JOOMDOC_AREA'));
    }

    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {
        if (!JString::trim($text) || (is_array($areas) && !array_intersect($areas, array_keys(plgSearchJoomDOC::onContentSearchAreas())))) {
            return array();
        }

        // import component JoomDOC framework
        $jdcAdm = JPATH_ADMINISTRATOR . '/components/com_joomdoc/';
        $jdcSit = JPATH_SITE . '/components/com_joomdoc/';
        $jdcLib = $jdcAdm . 'libraries/joomdoc/';

        $includes['JoomDOCRoute'] = $jdcLib . 'application/route.php';
        $includes['JoomDOCMenu'] = $jdcLib . 'application/menu.php';
        $includes['JoomDOCConfig'] = $jdcLib . 'application/config.php';
        $includes['JoomDOCFileSystem'] = $jdcLib . 'filesystem/filesystem.php';
        $includes['JoomDOCString'] = $jdcLib . 'utilities/string.php';
        $includes['JoomDOCHelper'] = $jdcLib . 'utilities/helper.php';
        $includes['JoomDOCModelList'] = $jdcLib . 'application/component/modellist.php';
        $includes[] = $jdcAdm . 'defines.php';

        foreach ($includes as $classname => $include) {
            $include = JPath::clean($include);
            if (!JFile::exists($include)) {
                // JoomDOC is propably uninstalled or corupted
                return array();
            }
            is_string($classname) ? JLoader::register($classname, $include) : include_once($include);
        }
        JModelLegacy::addIncludePath(JPath::clean($jdcAdm . 'models'));
        JTable::addIncludePath(JPath::clean($jdcAdm . 'tables'));
        $db = JFactory::getDbo();
        $nullDate = $db->getNullDate();

        // prepare SQL WHERE criteria
        $wheres = array();
        // according to type of searching
        switch ($phrase) {
            case 'exact':
                // exactly phrase
                $keywords = $db->q('%' . JString::strtolower(JString::trim($text)) . '%');
                $items[] = 'LOWER(`document`.`title`) LIKE ' . $keywords;
                $items[] = 'LOWER(`document`.`description`) LIKE ' . $keywords;
                $items[] = 'LOWER(`document`.`path`) LIKE ' . $keywords;
                $items[] = 'LOWER(`document`.`content`) LIKE ' . $keywords;
                $where[] = '(' . implode(') OR (', $items) . ') ';
                break;
            case 'all':
            case 'any':
            default:
                // all words or any word or default
                $keywords = explode(' ', $text);
                // split to words
                $wheres = array();
                // search for each word
                foreach ($keywords as $keyword) {
                    $keyword = JString::trim($keyword);
                    if ($keyword) {
                        $keyword = $db->q('%' . JString::strtolower($keyword) . '%');
                        $items[] = 'LOWER(`document`.`title`) LIKE ' . $keyword;
                        $items[] = 'LOWER(`document`.`description`) LIKE ' . $keyword;
                        $items[] = 'LOWER(`document`.`path`) LIKE ' . $keyword;
                        $items[] = 'LOWER(`document`.`content`) LIKE ' . $keyword;
                        $parts[] = implode(' OR ', $items);
                    }
                }
                if (isset($parts)) {
                    if ($phrase == 'all') {
                        $where[] = '(' . implode(') AND (', $parts) . ') ';
                    } else {
                        $where[] = '(' . implode(') OR (', $parts) . ') ';
                    }
                }
                break;
        }

        $where[] = '(' . JoomDOCModelList::getDocumentPublished() . ')';

        $where = ' WHERE ' . implode(' AND ', $where) . ' ';

        // prepare SQL ORDER BY criteria
        switch ($ordering) {
            case 'oldest':
                // oldest items first (oldest documents created or oldest file uploaded)
                $order = ' ORDER BY `document`.`created` ASC, `document`.`upload` ASC ';
                break;

            case 'popular':
                // most hits items first
                $order = ' ORDER BY `document`.`hits` DESC, `document`.`title` ASC, `document`.`path` ASC ';
                break;

            case 'alpha':
                // alphabetically (document title or file name)
                $order = ' ORDER BY `document`.`title` ASC, `document`.`path` ASC ';
                break;

            case 'category':
                // by parent folder (parent folder name or document title)
                $order = ' ORDER BY `parent`.`title` ASC, `parent`.`path` ASC ';
                break;

            case 'newest':
            default:
                // newest items first (newest documents created or file uploaded)
                $order = ' ORDER BY `document`.`created` DESC, `document`.`upload` DESC ';
                break;
        }

        $query = 'SELECT `document`.`title`,`document`.`path`, ';
        $query .= '`document`.`description`, `document`.`content`, ';
        $query .= '`document`.`created`,`document`.`upload`,  ';
        $query .= '`parent`.`title` AS `ptitle`,  '; // document folder title
        $query .= '`document`.`full_alias` ';
        $query .= 'FROM `#__joomdoc_flat` AS `document` ';
        $query .= 'LEFT JOIN `#__joomdoc_flat` AS `parent` ON `document`.`parent_path` = `parent`.`path`  '; // document folder
        $query .= $where . ' GROUP BY `document`.`path` ' . $order;

        $rows = $db->setQuery($query)->loadObjectList();

        foreach ($rows as $row) {
            $GLOBALS['joomDOCPath'] = $row->path;
            $row->title = JString::trim($row->title) ? $row->title : JFile::getName($row->path);
            $row->text = JString::trim($row->description) ? $row->description : $row->content;
            $row->created = $row->created == $nullDate ? $row->upload : $row->created;
            $row->section = JString::trim($row->ptitle) ? $row->ptitle : JoomDOCFileSystem::getParentPath($row->path);
            if ($this->params->get('document_link', 1) == 1) {
                $row->href = JRoute::_(JoomDOCRoute::viewDocuments($row->path, (empty($row->full_alias) ? $row->path : $row->full_alias)));
            } else {
                $row->href = JRoute::_(JoomDOCRoute::download($row->path, (empty($row->full_alias) ? $row->path : $row->full_alias)));
            }
            $row->browsernav = 2; // open document title in the same window
        }

        unset($GLOBALS['joomDOCPath']);

        return $rows;
    }

}
