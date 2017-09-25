<?php

/**
 * Process migration data from DocMAN or JoomDOC2 into new structure of JoomDOC3.
 *
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2012 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

class JoomDOCControllerMigration extends JControllerLegacy
{

    public function run()
    {
        $db = JFactory::getDbo();
        /* @var $db JDatabase */
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        // selected source docman or joomdoc
        $component = JRequest::getString('component');
        // current database prefix
        $pr = $mainframe->getCfg('dbprefix');
        // all DocMAN or JoomDOC2 tables
        if ($component == 'docman')
            $tables = array($pr . 'docman', $pr . 'docman_groups', $pr . 'docman_history', $pr . 'docman_licenses', $pr . 'docman_log');
        else
            $tables = array($pr . 'joomdoc2', $pr . 'joomdoc2_groups', $pr . 'joomdoc2_history', $pr . 'joomdoc2_licenses', $pr . 'joomdoc2_log');
        // all tables in Joomla database
        $joomla = $db->getTableList();
        // check if all tables exists, are required
        if (array_intersect($tables, $joomla) != $tables) {
            return $this->setRedirect(JoomDOCRoute::viewJoomDOC(), JText::sprintf('JOOMDOC_MIGRATION_TABLES_UNAVAILABLE', implode(', ', $tables)), 'error');
        }
        // relative path folder where DocMAN or JoomDOC stored files, is required
        $docbase = JPath::clean(JPATH_ROOT . '/' . JRequest::getString('docbase'));
        if (!JFolder::exists($docbase)) {
            return $this->setRedirect(JoomDOCRoute::viewJoomDOC(), JText::sprintf('JOOMDOC_MIGRATION_DOCBASE_UNAVAILABLE', $docbase), 'error');
        }
        // statictis counters
        $newFolders = $newFiles = 0;
        /* copy DocMAN or JoomDOC2 licenses into JoomDOC3 */
        $license = JTable::getInstance(JOOMDOC_LICENSE, JOOMDOC_TABLE_PREFIX);
        /* @var $license JoomDOCTableLicense */
        $table = $component == 'docman' ? 'docman_licenses ' : 'joomdoc2_licenses ';
        // total of licenses in DocMAN or JoomDOC2
        $db->setQuery('SELECT COUNT(*) FROM #__' . $table);
        $total = $db->loadResult();
        // map Ids of licenses from DocMAN or JoomDOC2 into Ids of licenses in new JoomDOC3 structure
        $licensesMap = array();
        for ($i = 0; $i < $total; $i += 100) {
            // migrate licenses in batch process
            $db->setQuery('SELECT * FROM #__' . $table, $i, 100);
            $items = $db->loadObjectList();
            foreach ($items as $item) {
                // prepare new JoomDOC3 license
                $license->id = 0;
                $license->alias = null;
                $license->state = JOOMDOC_STATE_PUBLISHED;
                // copy data from DocMAN or JoomDOC2 license into JoomDOC3
                $license->title = $item->name;
                $license->text = $item->license;
                $license->store();
                // store licenses maping for using in new JoomDOC3 documents
                $licensesMap[$item->id] = $license->id;
            }
        }
        $document = JTable::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_TABLE_PREFIX);
        /* @var $document JoomDOCTableDocument */
        // convert DocMAN or JoomDOC categories into JoomDOC3 folders
        $parents = array(0 => ''); // root of categories tree
        // do for levels of categories tree
        // store map of Joomla categories and DocMAN/JoomDOC2 documents to JoomDOC3 paths
        $categoriesMap = $documentsMap = array();
        do {
            // name of Joomla category section - identify component
            $section = $db->quote($component == 'docman' ? 'com_docman' : 'com_joomdoc');
            $parentsIds = implode(', ', array_keys($parents));
            // load total of DocMAN or JoomDOC2 categories in one level of categories tree
            if ($component == 'docman' && in_array($pr . 'docman_categories', $joomla)) // since DocMAN 1.5
            	$db->setQuery('SELECT COUNT(*) FROM #__docman_categories WHERE parent_id IN (' . $parentsIds . ')');
            else
            	$db->setQuery('SELECT COUNT(*) FROM #__categories WHERE section = ' . $section . ' AND parent_id IN (' . $parentsIds . ')');
            try {
                $total = $db->loadResult();
            } catch (Exception $e) { // new format of table jos_categories without section column
                $c = $db->replacePrefix('#__categories');
                $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_MIGRATION_OLD_CATEGORIES', $c, $c, $c, $c), 'notice');
                return;
            }
            $nextLevel = isset($nextLevel) ? array() : array(0 => ''); // in the first batch process uncategorized documents
            for ($i = 0; $i < $total; $i += 100) {
                // load DocMAN or JoomDOC2 categories tree level full node list in batch process
            	if ($component == 'docman' && in_array($pr . 'docman_categories', $joomla)) // since DocMAN 1.5
            		$db->setQuery('SELECT id, title, parent_id, description, published, ordering, access FROM #__docman_categories WHERE parent_id IN (' . $parentsIds . ')', $i, 100);
            	else
                	$db->setQuery('SELECT id, title, parent_id, description, published, ordering, access FROM #__categories WHERE section = ' . $section . ' AND parent_id IN (' . $parentsIds . ')', $i, 100);
                $children = $db->loadObjectList('id');
                foreach ($children as $id => $kid) {
                    /* @var $kid JTableCategory */
                    // create folder name from category title
                    $folder = JFilterOutput::stringURLSafe($kid->title);
                    // create document for folder
                    $document->id = 0;
                    $document->parent_path = $parents[$kid->parent_id];
                    $document->path = ($document->parent_path ? $document->parent_path . DIRECTORY_SEPARATOR : '') . $folder;
                    // copy data form category into document
                    $document->title = $kid->title;
                    $document->alias = null;
                    $document->description = $kid->description;
                    $document->state = $kid->published == JOOMDOC_STATE_PUBLISHED ? JOOMDOC_STATE_PUBLISHED : JOOMDOC_STATE_UNPUBLISHED;
                    $document->ordering = $kid->ordering;
                    $document->access = $kid->access ? $kid->access : 1;
                    if (!JFolder::exists(JoomDOCFileSystem::getFullPath($document->path))) {
                        // create folder only if doesn't exists
                        JoomDOCFileSystem::newFolder($document->parent_path, $folder, false, false);
                    } else {
                        // folder exist, search last exists document
                        $db->setQuery('SELECT id FROM #__joomdoc WHERE path = ' . $db->Quote($document->path) . ' ORDER BY version DESC');
                        $document->id = $db->loadResult();
                    }
                    if (JFolder::exists(JoomDOCFileSystem::getFullPath($document->path))) {
                        if ($document->store(false, false)) {
                            $newFolders++;
                            // current kid will be in parents for next level of categories tree
                            $nextLevel[$id] = $document->path;
                            $categoriesMap[$kid->id] = $document->path;
                        }
                    }
                }
            }
            // next level parents
            $parents = $nextLevel;
            if (count($parents)) {
                /* Convert DocMAN or JoomDOC2 categories documents into JoomDOC3 files in folder */
                $table = $component == 'docman' ? 'docman' : 'joomdoc2';
                $parentsIds = implode(', ', array_keys($parents));
                // load total of DocMAN or JoomDOC2 documents for categories in current level
                $db->setQuery('SELECT COUNT(*) FROM #__' . $table . ' WHERE catid IN (' . $parentsIds . ')');
                $total = $db->loadResult();
                // DocMAN or JoomDOC2 documents from categories in current tree level - batch process
                for ($i = 0; $i < $total; $i += 100) {
                    $db->setQuery('SELECT * FROM #__' . $table . ' WHERE catid IN (' . $parentsIds . ')', $i, 100);
                    $items = $db->loadObjectList();
                    foreach ($items as $item) {
                        // create document for successfully uploaded file
                        $document->id = 0;
                        $document->parent_path = $parents[$item->catid];
                        $document->path = $document->parent_path . DIRECTORY_SEPARATOR . $item->dmfilename;
                        // copy data from DocMAN or JoomDOC2 document into JoomDOC3 document
                        $document->title = $item->dmname;
                        $document->alias = null;
                        $document->description = $item->dmdescription;
                        $document->publish_up = $item->dmdate_published;
                        $document->created_by = $item->dmsubmitedby;
                        $document->state = $item->published == JOOMDOC_STATE_PUBLISHED ? JOOMDOC_STATE_PUBLISHED : JOOMDOC_STATE_UNPUBLISHED;
                        $document->modified = $item->dmlastupdateon;
                        $document->modified_by = $item->dmlastupdateby;
                        $document->access = $item->access ? $item->access : 1;
                        $document->license = isset($licensesMap[$item->dmlicense_id]) ? $licensesMap[$item->dmlicense_id] : 0;
                        if (JFile::exists(JoomDOCFileSystem::getFullPath($document->path))) {
                            // if file exists search for last document
                            $db->setQuery('SELECT id FROM #__joomdoc WHERE path = ' . $db->quote($document->path) . ' ORDER BY version DESC');
                            $document->id = $db->loadResult();
                        }
                        if (JoomDOCFileSystem::uploadFile(JoomDOCFileSystem::getFullPath($parents[$item->catid]), ($docbase . DIRECTORY_SEPARATOR . $item->dmfilename), $item->dmfilename, true, false)) {
                            // upload into JoomDOC3 tree structure
                            if ($document->store(false, false)) {
                                $newFiles++;
                                // copy document hits into JoomDOC3 file
                                $db->setQuery('UPDATE #__joomdoc_file SET hits = ' . $item->dmcounter . ' WHERE path = ' . $db->quote($document->path));
                                $db->query();
                                $documentsMap[$item->id] = $document->path;
                            }
                        }
                    }
                }
            }
        } while (count($parents));
        // reindex at the end
       	JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat();	
        /* Migrate DocMAN/JoomDOC2 URLs to JoomDOC3 in JoomSEF and Joomla tables*/
        $entities = array(
            array('table' => 'menu', 'html' => false, 'columns' => array('link', 'params'), 'where' => ' WHERE client_id != 1 '), // menu items
            array('table' => 'content', 'html' => true, 'columns' => array('introtext', '`fulltext`')), // articles
            array('table' => 'modules', 'html' => true, 'columns' => array('content')), // custom html module
            array('table' => 'sefurls', 'html' => false, 'columns' => array('origurl')) // JoomSEF
            );
        foreach ($entities as $entity) {
            // check if table is available
            // backup table with name eq. jos_content_joomdoc3_migration_backup
            $backup = $pr . $entity['table'] . '_joomdoc3_migration_backup';
            // check if backup already exists
            $db->setQuery('SHOW TABLES LIKE ' . $db->quote($backup));
            if ($db->loadResult() === null) {
                // create clone of backuped table
                $db->setQuery('CREATE TABLE IF NOT EXISTS ' . $backup . ' LIKE #__' . $entity['table']);
                try {
                    $db->query();
                } catch(Exception $e) {
                    continue; // JoomSEF not installed
                }
                // copy data of table into backup
                $db->setQuery('INSERT INTO ' . $backup . ' SELECT * FROM #__' . $entity['table']);
                $db->query();
                // inform user about backup available
                $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_MIGRATION_BACKUPED', $pr . $entity['table'], $backup));
            }
            // get total of rows
            $db->setQuery('SELECT COUNT(*) FROM #__' . $entity['table'] . (empty($entity['where']) ? '' : $entity['where']));
            $total = $db->loadResult();
            for ($i = 0; $i < $total; $i += 500) {
                // load URLs in batch process
                $db->setQuery('SELECT id, ' . implode(', ', $entity['columns']) . ' FROM #__' . $entity['table'] . (empty($entity['where']) ? '' : $entity['where']), $i, 500);
                // load as multidimensional array
                $items = $db->loadRowList();
                foreach ($items as $item) {
                    // information if any change was made
                    $affected = false;
                    $count = count($item);
                    // process columns without id
                    for ($j = 1; $j < $count; $j++) {
                        $urls = array();
                        if ($entity['html']) {
                            // content is HTML code from editor (article, module)
                            $matches = array();
                            if (preg_match_all('/href="([^"]*)"/', $item[$j], $matches)) {
                                $urls = $matches[1];
                            }
                        } else {
                            // URL is whole content (JoomSEF, menu)
                            $urls = array($item[$j]);
                        }
                        // analyse URLs
                        foreach ($urls as $url) {
                            $uri = JFactory::getURI($url);
                            /* @var $uri JURI Joomla support for working with URLs */
                            if ($uri->getVar('option') == 'com_joomdoc' || $uri->getVar('option') == 'com_docman') {
                                // JoomDOC3 option same as JoomDOC2                            	
                                $uri->setVar('option', 'com_joomdoc');
                                // ID of DocMAN/JoomDOC2 category or document
                                $gid = $uri->getVar('gid');
                                // unused in new URL
                                $uri->delVar('gid');
                                // analyse DocMAN/JoomDOC task value to recognise typoe of URL
                                switch ($uri->getVar('task')) {
                                    case 'cat_view': /* display DocMAN/JoomDOC2 category */
                                        $uri->setVar('view', JOOMDOC_DOCUMENTS);
                                        $uri->delVar('task'); // don't use task wit wire together
                                        break;
                                    case 'doc_details': /* display DocMAN/JoomDOC2 document */
                                    case 'doc_view':
                                        $uri->setVar('view', JOOMDOC_DOCUMENTS);
                                        $uri->delVar('task'); // don't use task with view together
                                        break;
                                    case 'doc_download':
                                        $uri->setVar('task', JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_DOWNLOAD));
                                        break;
                                    case 'doc_edit':
                                        $uri->setVar('task', JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_EDIT));
                                        break;
                                    case 'doc_delete':
                                        $uri->setVar('task', JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_DELETE));
                                        break;
                                    default: /* URLs not supported by JoomDOC3 redirect to root */
                                        $uri->setVar('view', JOOMDOC_DOCUMENTS);
                                        break;
                                }
                                if (isset($documentsMap[$gid]))
                                    $uri->setVar('path', $documentsMap[$gid]);
                                // menu items to open documents list store for using in next entities
                                if ($entity['table'] == 'menu' && $uri->getVar('view') == JOOMDOC_DOCUMENTS) {
                                    $registry = new JRegistry($item[2]); // load menu item configuration
                                    $registry->set('virtual_folder', 0); // disable JoomDOC3 virtual folder
                                    $item[2] = $registry->toString(); /* back to database format */
                                }
                                $new = $uri->toString(); // back to string
                                if ($entity['html']) {
                                    // safe for valid HTML code
                                    $new = str_replace('&', '&amp;', $new);
                                }
                                // remove encoding in path added with JURI - prevent for making duplicity with JoomSEF
                                $new = str_replace('%2F', '/', $new);
                                $item[$j] = str_replace($url, $new, $item[$j]); // update in data
                                // row will be updated
                                $affected = true;
                            }
                        }
                    }
                    if ($affected) {
                        $cols = array();
                        for ($j = 1; $j < $count; $j++) {
                            $cols[] = $entity['columns'][$j - 1] . ' = ' . $db->quote($item[$j]);
                        }
                        $db->setQuery('UPDATE #__' . $entity['table'] . ' SET ' . implode(', ', $cols) . ' WHERE id = ' . $item[0]);
                        $db->query();
                    }
                }
            }
        }
        // final report
        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_MIGRATION_REPORT', $newFolders, $newFiles));
        $this->setRedirect(JoomDOCRoute::viewMigration());
    }
}
?>