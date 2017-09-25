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

// JoomDOC defines
require_once(JPATH_ADMINISTRATOR . '/components/com_joomdoc/defines.php');

// JoomDOC framework prepare to auto load
JLoader::register('JoomDOCRoute', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/route.php');
JLoader::register('JoomDOCMenu', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/menu.php');
JLoader::register('JoomDOCConfig', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/application/config.php');
JLoader::register('JoomDOCFileSystem', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/filesystem/filesystem.php');
JLoader::register('JoomDOCString', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/utilities/string.php');
JLoader::register('JoomDOCSearchConfig', JPATH_SITE . '/modules/mod_joomdoc_search/config.php');

// set paths to load JoomDOC core classes
JModelLegacy::addIncludePath(JOOMDOC_MODELS);
JTable::addIncludePath(JOOMDOC_TABLES);

$fields = (array) $params->get('searchfield', array('joomdoc_keywords'));

// display module active template
require(JModuleHelper::getLayoutPath('mod_joomdoc_search', $params->get('layout')));
