<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_joomdoc
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die();

// import Joomla framework
jimport('joomla.application.component.model');
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.form.form');
jimport('joomla.error.log'); // J1.5!
jimport('joomla.log.log'); // J2.5!

$mainframe = JFactory::getApplication();
/* @var $mainframe JAdministrator */
$document = JFactory::getDocument();
/* @var $document JDocumentHTML */
$user = JFactory::getUser();
/* @var $user JUser */

if (method_exists('JLog', 'addLogger')) // only J2.5!
	JLog::addLogger(array('text_file' => 'com_joomdoc.php'), JLog::ALL, array('com_joomdoc')); // custom logger for JoomDOC

// import component defines constants
include_once(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'defines.php');

// add include path for tables and models to access this class from all parts of component and from frontend
JTable::addIncludePath(JOOMDOC_TABLES);
JModelLegacy::addIncludePath(JOOMDOC_MODELS);
JHtml::addIncludePath(JOOMDOC_HTML);
JForm::addFieldPath(JOOMDOC_FORMS);

// register main access class, only Joomla 1.6.x
JLoader::register(JOOMDOC_ACCESS_PREFIX, JOOMDOC_ACCESS . DIRECTORY_SEPARATOR . 'joomdoc.php');

// control component access, only Joomla 1.6.x
if ($mainframe->isAdmin() && !$user->authorise('core.manage', JOOMDOC_OPTION)) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// import access helpers	
foreach (JFolder::files(JOOMDOC_ACCESS, '.php', true, true) as $access) {
    JLoader::register(JOOMDOC_ACCESS_PREFIX . str_replace('.php', '', JFile::getName($access)), $access);
}

// import framework helpers
foreach (JFolder::files(JOOMDOC_HELPERS, '.php', true, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'access')) as $helper) {
    JLoader::register(JOOMDOC_HELPER_PREFIX . str_replace('.php', '', JFile::getName($helper)), $helper);
}

// import Joomla javascript frameworks
JHtml::_('joomdoc.behaviortooltip');
JHtml::_('behavior.framework');

// import backend language manualy because this file is used from frontend and language is only in backend
$language = JFactory::getLanguage();
/* @var $language JLanguage */
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, 'en-GB');
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, $language->getTag(), true);

// import CSS and JS assets
$document->addStyleSheet(JOOMDOC_ASSETS . 'css/general.css?' . JOOMDOC_VERSION_ALIAS);
$document->addScript(JOOMDOC_ASSETS . 'js/script.js?' . JOOMDOC_VERSION_ALIAS);

// Load the CSS
if (JOOMDOC_ISJ3) {
    $document->addStyleSheet(JOOMDOC_ASSETS . 'css/joomla3.css?'.JOOMDOC_VERSION_ALIAS);
}

//$document->addStyleSheet(JOOMDOC_ASSETS . 'css/joomla-ico.css');

// language constants for javascript
include_once(JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'script.php');

$config = JoomDOCConfig::getInstance();

define('ARTIO_UPGRADE_DOWNLOAD_ID', $config->downloadId);

if (class_exists('JToolBar')) {
    $bar = JToolBar::getInstance('toolbar');
    /* @var $bar JToolBar */
    $bar->addButtonPath(JOOMDOC_BUTTONS);
}

// start controller
$controller = JControllerLegacy::getInstance(JOOMDOC);

if (JOOMDOC_LOG)
    JFactory::getDbo()->debug(1);

$controller->execute(JFactory::getApplication()->input->get('task'));

if (JOOMDOC_LOG) {
    JFactory::getDbo()->debug(0);
    JoomDOCHelper::showLog();
}

$controller->redirect();

if ($config->displaySignature) {
    //echo '<p style="clear: both; padding-top: 20px;">' . JText::_('JOOMDOC_SIGNATURE') . '</p>';
}
?>