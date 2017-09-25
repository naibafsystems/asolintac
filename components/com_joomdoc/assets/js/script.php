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

$document = JFactory::getDocument();
/* @var $document JDocumentHTML */
$config = JoomDOCConfig::getInstance();

$params['joomDOCTaskUploadFile'] = JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_UPLOADFILE);
$params['joomDOCmsgEmpty'] = JText::_('JOOMDOC_UPLOAD_EMPTY');

 $params['joomDOCmsgOverwrite'] = JText::_('JOOMDOC_UPLOAD_OVERWRITE');

$params['joomDOCcfgConfirmOverwite'] = (int) $config->confirmOverwrite;
$params['joomDOCmsgDirExists'] = JText::_('JOOMDOC_UPLOAD_DIR_EXISTS');
$params['joomDOCmsgAreYouSure'] = JText::_('JOOMDOC_ARE_YOU_SURE');
$params['joomDOCmsgAddKeywords'] = JText::_('JOOMDOC_SEARCH_ADD_KEYWORDS');
$params['joomDOCmsgAddArea'] = JText::_('JOOMDOC_SEARCH_ADD_WHERE');

$params['joomDOCmsgMkdirEmpty'] = JText::_('JOOMDOC_MKDIR_EMPTY');
$params['joomDOCmsgMkdirFileExists'] = JText::_('JOOMDOC_MKDIR_FILE_EXISTS');
$params['joomDOCmsgMkdirDirExists'] = JText::_('JOOMDOC_MKDIR_DIR_EXISTS');
$params['joomDOCmsgMkdirCreateDocument'] = JText::_('JOOMDOC_MKDIR_CREATE_DOCUMENT');
$params['joomDOCmsgMkdirTask'] = "'".addslashes(JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_NEWFOLDER))."'";

$params['joomDOCsearchDefaultType'] = $config->searchDefaultType;
$params['joomDOCsearchDefaultOrder'] = $config->searchDefaultOrder;

foreach ($params as $param => $value)
    $document->addScriptDeclaration('var ' . $param . ' = "' . addslashes($value) . '";');
?>


