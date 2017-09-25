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

echo '<tr><td class="key">' . $this->form->getLabel('id') . '</td>';
echo '<td>' . $this->form->getInput('id') . '</td></tr>';

echo '<tr><td class="key">' . $this->form->getLabel('path') . '</td>';
echo '<td class="input-append">';
echo $this->form->getInput('path');
echo '<button onclick="return JoomDOC.copyPath(\'' . $this->escape(JFile::getName($this->form->getValue('path'))) . '\')" title="' . $this->escape(JText::_('JOOMDOC_COPY_DESC')) . '" class="btn"><span class="icon-copy"></span>' . JText::_('JOOMDOC_COPY') . '</button>';
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

echo '<tr><td class="key" colspan="2">' . $this->form->getLabel('description') . '</td></tr>';
echo '</table>';
echo '<div>' . $this->form->getInput('description') . '</div>';

echo JHtmlJoomDOC::endTab();