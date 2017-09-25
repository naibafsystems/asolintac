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

// JoomDOC defines.
require_once(JPATH_ADMINISTRATOR . '/components/com_joomdoc/defines.php');

// JoomDOC framework files are registered for auto load.
JLoader::register('JoomDOCRoute', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/route.php');
JLoader::register('JoomDOCMenu', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/menu.php');
JLoader::register('JoomDOCConfig', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/config.php');
JLoader::register('JoomDOCFileSystem', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/filesystem.php');
JLoader::register('JoomDOCString', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/utilities/string.php');
JLoader::register('JoomDOCModuleConfig', JPATH_SITE . '/modules/mod_joomdoc/config.php');
JLoader::register('JoomDOCModelList', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/component/modellist.php');
JLoader::register('JoomDOCView', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/component/view.php');
JLoader::register('JoomDOCFolder', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/folder.php');
JLoader::register('JoomDOCFile', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/file.php');
JLoader::register('JoomDOCAccessHelper', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/helper.php');
JLoader::register('JoomDOCHelper', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/utilities/helper.php');
JLoader::register('JoomDOCAccessFileSystem', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/filesystem.php');
JLoader::register('JoomDOCAccess', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/joomdoc.php');
JLoader::register('JoomDOCAccessDocument', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/document.php');
JLoader::register('JHtmlJoomDOC', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php');

// Paths to load JoomDOC core classes (model, tables) sets.
JModelLegacy::addIncludePath(JOOMDOC_MODELS);
JModelLegacy::addIncludePath(JOOMDOC_SITE_MODELS);
JTable::addIncludePath(JOOMDOC_TABLES);

$document = JFactory::getDocument();
/* @var $document JDocument */
$document->addStyleSheet(JOOMDOC_ASSETS . 'css/general.css?' . JOOMDOC_VERSION_ALIAS);

// Module and JoomDOC comfiguration.
$moduleConfig = JoomDOCModuleConfig::getInstance($params, $module->id);
$globalConfig = JoomDOCConfig::getInstance();

$modelDocuments = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_SITE_PREFIX);
/* @var $modelDocuments JoomDOCSiteModelDocuments */

$listFields = $modelDocuments->getListFields();

// Only files are loaded.
$root = JoomDOCFileSystem::getFolderContent($moduleConfig->parent ? JoomDOCFileSystem::getFullPath($moduleConfig->parent) : $globalConfig->docroot, '', true, false, true);
// Model searchs in database for founded paths.
$modelDocuments->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_PATHS), $root->getPaths());
// Filesystem extends items informations from documents.
$root->setDocuments($modelDocuments->getItems());
// Filesystem reordes items for given setting.
$root->reorder($moduleConfig->documentOrdering, $moduleConfig->fileOrdering, JOOMDOC_ORDER_DESC, 0, $moduleConfig->limit, $moduleConfig->limit);

// Active module template displayed.
require(JModuleHelper::getLayoutPath('mod_joomdoc', $moduleConfig->layout));
?>