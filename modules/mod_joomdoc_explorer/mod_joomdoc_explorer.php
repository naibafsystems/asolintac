<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/* @var $this JModuleHelper */

defined('_JEXEC') or die;

// JoomDOC defines.
require_once(JPATH_ADMINISTRATOR . '/components/com_joomdoc/defines.php');

// JoomDOC framework files are registered for auto load.
JLoader::register('JoomDOCRoute', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/route.php');
JLoader::register('JoomDOCMenu', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/menu.php');
JLoader::register('JoomDOCConfig', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/config.php');
JLoader::register('JoomDOCFileSystem', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/filesystem.php');
JLoader::register('JoomDOCString', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/utilities/string.php');
JLoader::register('JoomDOCModuleExplorerConfig', JPATH_SITE . '/modules/mod_joomdoc_explorer/config.php');
JLoader::register('JoomDOCModelList', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/component/modellist.php');
JLoader::register('JoomDOCView', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/component/view.php');
JLoader::register('JoomDOCFolder', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/folder.php');
JLoader::register('JoomDOCFile', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/file.php');
JLoader::register('JoomDOCAccessHelper', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/helper.php');
JLoader::register('JoomDOCHelper', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/utilities/helper.php');
JLoader::register('JoomDOCAccessFileSystem', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/filesystem.php');
JLoader::register('JoomDOCAccess', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/joomdoc.php');
JLoader::register('JoomDOCAccessDocument', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access/document.php');
JLoader::register('JHTMLJoomDOC', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php');
JLoader::register('JoomDOCRequest', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/environment/request.php');

// Paths to load JoomDOC core classes (model, tables) sets.
JModelLegacy::addIncludePath(JOOMDOC_MODELS);
JModelLegacy::addIncludePath(JOOMDOC_SITE_MODELS);
JTable::addIncludePath(JOOMDOC_TABLES);

$document = JFactory::getDocument();
/* @var $document JDocument */
$document->addStyleSheet(JOOMDOC_ASSETS . 'css/general.css?' . JOOMDOC_VERSION_ALIAS);

// Module and JoomDOC comfiguration.
$moduleConfig = JoomDOCModuleExplorerConfig::getInstance($params, $module->id);
$globalConfig = JoomDOCConfig::getInstance();

$modelDocuments = JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_SITE_PREFIX);
/* @var $modelDocuments JoomDOCSiteModelDocuments */

// Only folders are loaded.
$parent = $moduleConfig->parent ? JoomDOCFileSystem::getFullPath($moduleConfig->parent) : $globalConfig->docroot;

if (!JFolder::exists($parent)) // invalid or expired module config
	$parent = ''; // reset to root

$path = JoomDOCRequest::getPath();

// on file detail get parent folder path
if (JFile::exists(JoomDOCFileSystem::getFullPath($path)) && JFolder::exists(JoomDOCFileSystem::getFullPath(JoomDOCFileSystem::getParentPath($path))))
	$path = JoomDOCFileSystem::getParentPath($path);

$root = JoomDOCFileSystem::getFolderContent($parent, '', 1, true, false, $path);
// Model searchs in database for founded paths.
$modelDocuments->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_PATHS), $root->getPaths());
// Filesystem extends items informations from documents.
$root->setDocuments($modelDocuments->getItems());
// Filesystem reordes items for given setting.
$root->reorder(null, JOOMDOC_ORDER_PATH, JOOMDOC_ORDER_ASC, 0, PHP_INT_MAX);

// Active module template displayed.
require(JModuleHelper::getLayoutPath('mod_joomdoc_explorer', $moduleConfig->layout));
?>