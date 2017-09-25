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

$config = JoomDOCConfig::getInstance();

echo JHtmlJoomDOC::addTab('JOOMDOC_DOCUMENT_DETAILS', 'details', 'tabone');

echo '<fieldset class="adminform">';
echo '<legend>' . JText::_('JOOMDOC_DOCUMENT') . '</legend>';

echo '<table class="admintable">';

echo '<tr><td class="key">' . $this->form->getLabel('title') . '</td>';
echo '<td>' . $this->form->getInput('title') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('alias') . '</td>';
echo '<td>' . $this->form->getInput('alias') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('state') . '</td>';
echo '<td>' . $this->form->getInput('state') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('favorite') . '</td>';
echo '<td>' . $this->form->getInput('favorite') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('access') . '</td>';
echo '<td>' . $this->form->getInput('access') . '</td></tr>';
if ($this->form->getField('download')) {
    echo '<tr><td class="key">' . $this->form->getLabel('download') . '</td>';
    echo '<td>' . $this->form->getInput('download') . '</td></tr>';
}
echo '<tr><td class="key">' . $this->form->getLabel('license') . '</td>';
echo '<td>' . $this->form->getInput('license') . '</td></tr>';

/* <PAID> */
if ($config->versionDocument && $this->access->canViewVersions) {
    echo '<tr><td class="key">' . $this->form->getLabel('version') . '</td><td>' . $this->form->getInput('version') . '</td></tr>';
    echo '<tr><td class="key">' . $this->form->getLabel('versionNote') . '</td><td>' . $this->form->getInput('versionNote') . '</td></tr>';
}
/* </PAID> */

echo '<tr><td class="key">' . $this->form->getLabel('id') . '</td>';
echo '<td>' . $this->form->getInput('id') . '</td></tr>';
echo '<tr><td class="key">' . $this->form->getLabel('path') . '</td>';

echo '<td>';
echo '<span class="input-append">';
echo $this->form->getInput('path');
echo '<button onclick="return JoomDOC.copyPath(\'' . $this->escape(JFile::getName($this->form->getValue('path'))) . '\')" title="' . $this->escape(JText::_('JOOMDOC_COPY_DESC')) . '" class="btn">';
echo '<span class="icon-copy"></span>';
echo JText::_('JOOMDOC_COPY');
echo '</button>';
echo '</span>';
echo '</td></tr>';

// custom fields
$fields = $this->form->getFieldset();
foreach ($fields as $field) {
    /* @var $field JFormField */
    $fieldname = $field->__get('fieldname');
    if (JString::strpos($fieldname, 'field') === 0) {
        echo '<tr><td class="key">' . $this->form->getLabel($fieldname) . '</td>';
        echo '<td>' . $this->form->getInput($fieldname) . '</td></tr>';
    }
}

echo '</table>';

echo '<div class="clr"></div>';
echo $this->form->getLabel('description');
echo '<div class="clr"></div>';
echo $this->form->getInput('description');

echo '</fieldset>';

echo JHtmlJoomDOC::endTab();
