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

/* @var $this JoomDOCViewLicense */

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

jimport('joomla.html.pane');

$document = JFactory::getDocument();
/* @var $document JDocumentHTML */

$js[] = 'Joomla.submitbutton = function (task) {';
$js[] = '	var form = document.getElementById(\'item-form\');';
$js[] = '	if (task == \'' . JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_CANCEL) . '\' || document.formvalidator.isValid(form))';
$js[] = '		Joomla.submitform(task, form);';
$js[] = '	else alert(\'' . JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true) . '\');';
$js[] = '}';
$js[] = 'function submitform(pressbutton){Joomla.submitbutton(pressbutton);}';

$document->addScriptDeclaration(PHP_EOL . implode(PHP_EOL, $js) . PHP_EOL);

echo '<form action="' . JRoute::_(JoomDOCRoute::saveLicense($this->license->id)) . '" method="post" name="adminForm" id="item-form" class="form-validate">';

echo '<div class="fltlft col"><fieldset class="adminform"><legend>' . JText::_('JOOMDOC_DOCUMENT') . '</legend><table class="admintable">';
echo '<tr><td class="key">' . $this->form->getLabel('title') . '</td><td>' . $this->form->getInput('title') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('alias') . '</td><td>' . $this->form->getInput('alias') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('default') . '</td><td>' . $this->form->getInput('default') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('state') . '</td><td>' . $this->form->getInput('state') . '</td></tr>';
echo '</table><div class="clr"></div>';
echo $this->form->getLabel('text') . '<div class="clr"></div>';
echo JoomDOCAccess::licenses() ? $this->form->getInput('text') : $this->form->getValue('text');
echo '<div class="clr"></div></fieldset></div>';

if ($this->license->id) {
    echo '<div class="fltlft col">';
    require_once JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php';
    
    echo JHtmlJoomDOC::startSliders('license', 'publishing');
    echo JHtmlJoomDOC::addSlide('license', 'JOOMDOC_PUBLISHING', 'publishing');

    echo '<fieldset class="panelform"><table class="admintable">';
    echo '<tr><td class="key">' . $this->form->getLabel('created') . '</td><td>' . $this->date('created') . '</td></tr>';
    echo '<tr><td class="key">' . $this->form->getLabel('created_by') . '</td><td>' . $this->form->getInput('created_by') . '</td></tr>';
    if ($this->license->modified_by) {
        echo '<tr><td class="key">' . $this->form->getLabel('modified') . '</td><td>' . $this->date('modified') . '</td></tr>';
        echo '<tr><td class="key">' . $this->form->getLabel('modified_by') . '</td><td>' . $this->form->getInput('modified_by') . '</td></tr>';
    }
    echo '</table></fieldset>';

    echo JHtmlJoomDOC::endSlide();
    echo JHtmlJoomDOC::endSlides();

    echo '</div><div class="clr"></div>';
}

echo JHtml::_('form.token') . '<input type="hidden" name="task" value="" />';
echo '<input type="hidden" name="return" value="' . JRequest::getCmd('return') . '" /><div class="clr"></div></form>';
?>