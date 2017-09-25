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

echo JHtmlJoomDOC::addTab('JOOMDOC_PUBLISHING', 'publishing', 'tabone');

echo '<fieldset class="panelform">';
echo '<table class="admintable">';

echo '<tr><td class="key">' . $this->form->getLabel('created_by') . '</td>';
echo '<td>' . $this->form->getInput('created_by') . '</td></tr>';

echo '<tr><td class="key">' . $this->form->getLabel('created') . '</td>';
echo '<td>' . $this->date('created') . '</td></tr>';

echo '<tr><td class="key">' . $this->form->getLabel('publish_up') . '</td>';
echo '<td>' . $this->date('publish_up') . '</td></tr>';

echo '<tr><td class="key">' . $this->form->getLabel('publish_down') . '</td>';
echo '<td>' . $this->date('publish_down') . '</td></tr>';

if ($this->document->modified_by) {
    echo '<tr><td class="key">' . $this->form->getLabel('modified_by') . '</td>';
    echo '<td>' . $this->form->getInput('modified_by') . '</td></tr>';
    echo '<tr><td class="key">' . $this->form->getLabel('modified') . '</td>';
    echo '<td>' . $this->date('modified') . '</td></tr>';
}

echo '</table>';
echo '</fieldset>';

echo JHtmlJoomDOC::endTab();

