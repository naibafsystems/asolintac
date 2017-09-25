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

JHtmlBehavior::tooltip();
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

jimport('joomla.html.pagination');

$mainframe = JFactory::getApplication();
$document = JFactory::getDocument();
/* @var $mainframe JApplication */
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

echo '<div id="document">';
echo '<div class="edittoolbar">';

if ($this->access->canCreate || $this->access->canEdit) {
    echo '<a href="javascript:Joomla.submitbutton(\'' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_APPLY) . '\')" class="apply" title="">' . JText::_('JTOOLBAR_APPLY') . '</a>';
    echo '<a href="javascript:Joomla.submitbutton(\'' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_SAVE) . '\')" class="save" title="">' . JText::_('JTOOLBAR_SAVE') . '</a>';
}
echo '<a href="javascript:Joomla.submitbutton(\'' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_CANCEL) . '\')" class="cancel" title="">' . JText::_('JTOOLBAR_CANCEL') . '</a>';

echo '<div class="clr"></div>';
echo '</div>';
echo '<div class="clr"></div>';

require_once JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php';

echo '<script type="text/javascript">';
echo '//<![CDATA[';
/**
 * Joomla 1.6.x
 */
echo 'Joomla.submitbutton = function(task) {';
echo 'if (task == \'' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_CANCEL) . '\' || document.formvalidator.isValid(document.getElementById(\'item-form\'))) {';
echo 'try {';
echo 'Joomla.submitform(task, document.getElementById(\'item-form\'));';
echo '} catch(e) {';
echo 'document.adminForm.task.value = task;';
echo 'document.adminForm.submit();';
echo '}';
echo '} else {';
echo 'alert(\'' . JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true) . '\');';
echo '}';
echo '}';
/**
 * Submit form operation.
 *
 * @param pressbutton taks value from toolbar button
 */
echo 'function submitform(pressbutton) {';
echo 'if (pressbutton) {';
// toolbar work with document edit form
echo 'if (! Joomla.submitbutton(pressbutton)) {';
echo 'return false;';
echo '}';
// set task operation into hidden field task (save, apply, cancel etc.)
echo 'document.adminForm.task.value = pressbutton;';
echo '}';
echo 'if (typeof document.adminForm.onsubmit == "function") {';
echo 'document.adminForm.onsubmit();';
echo '}';
echo 'if (pressbutton) {';
// toolbar work with document edit form
echo 'document.adminForm.submit();';
echo '}';
echo '}';
echo '//]]>';
echo '</script>';

echo '<form action="index.php" method="post" name="adminForm" id="item-form" class="form-validate">';

echo JHtmlJoomDOC::startTabs('tabone', 'details');

echo $this->loadTemplate('details');

echo $this->loadTemplate('publishing');

echo $this->loadTemplate('params');

echo $this->loadTemplate('rules');

echo $this->loadTemplate('symlinks');

echo JHtmlJoomDOC::endTabs();

echo '<input type="hidden" name="Itemid" value="' . JoomDOCMenu::getMenuItemID($this->form->getValue('path')) . '" />';
echo '<input type="hidden" name="task" value="" />';
echo '<input type="hidden" name="id" value="' . $this->document->id . '" />';
echo '<input type="hidden" name="option" value="' . JOOMDOC_OPTION . '" />';
echo '<input type="hidden" name="return" value="' . JRequest::getCmd('return') . '" />';
echo JHtml::_('form.token');
echo '</form>';

echo '</div>';