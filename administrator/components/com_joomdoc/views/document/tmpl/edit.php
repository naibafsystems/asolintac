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

/* @var $this JoomDOCViewDocument */

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

jimport('joomla.html.pagination');

$document = JFactory::getDocument();
/* @var $document JDocumentHTML */
$config = JoomDOCConfig::getInstance();
/* @var $config JoomDOCConfig */

$js[] = 'Joomla.submitbutton = function (task) {';
$js[] = '	var form = document.getElementById(\'item-form\');';
$js[] = '	if (task == \'' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_CANCEL) . '\' || document.formvalidator.isValid(form)) {';
$js[] = '		Joomla.submitform(task, form);';
$js[] = '	} else {';
$js[] = '		alert(\'' . JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true) . '\');';
$js[] = '	}';
$js[] = '}';
$js[] = 'function tableOrdering(order, dir, task) {';
$js[] = '	document.versionForm.filter_order.value = order;';
$js[] = '	document.versionForm.filter_order_Dir.value = dir;';
$js[] = '	document.versionForm.submit();';
$js[] = '}';
$js[] = 'function submitform(pressbutton) {';
$js[] = '	if (pressbutton) {';
$js[] = '		// toolbar work with document edit form';
$js[] = '		if (! Joomla.submitbutton(pressbutton)) {';
$js[] = '			return false;';
$js[] = '		}';
$js[] = '		// set task operation into hidden field task (save, apply, cancel etc.)';
$js[] = '		document.adminForm.task.value = pressbutton;';
$js[] = '	} else {';
$js[] = '		// others task are for version table';
$js[] = '		document.versionForm.submit();';
$js[] = '	}';
$js[] = '	if (typeof document.adminForm.onsubmit == "function") {';
$js[] = '		document.adminForm.onsubmit();';
$js[] = '	}';
$js[] = '	if (pressbutton) {';
$js[] = '		// toolbar work with document edit form';
$js[] = '		document.adminForm.submit();';
$js[] = '	}';
$js[] = '}';

$document->addScriptDeclaration(PHP_EOL . implode(PHP_EOL, $js) . PHP_EOL);

require_once JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php';

echo '<form action="' . JRoute::_(JoomDOCRoute::saveDocument($this->document->id)) . '" method="post" name="adminForm" id="item-form" class="form-validate">';

echo JHtmlJoomDOC::startTabs('tabone', 'details');

echo $this->loadTemplate('details');

echo $this->loadTemplate('publishing');

echo $this->loadTemplate('params');

if (JoomDOCAccess::admin()) {
    echo $this->loadTemplate('rules');
}

echo '<input type="hidden" name="task" value="" />';
echo '<input type="hidden" name="return" value="' . JRequest::getCmd('return') . '" />';
echo JHtml::_('form.token');

echo '</form>';

/* <PAID> */
if ($this->access->canViewVersions && $config->versionDocument) {
    echo $this->loadTemplate('versions');
}
/* </PAID> */
/* <ENTERPRISE> */
if ($config->useSymlinks) {
    echo $this->loadTemplate('symlinks');
}
/* </ENTERPRISE> */

echo JHtmlJoomDOC::endTabs();
