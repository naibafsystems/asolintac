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

echo '<table class="admintable">';
$creatorID = $this->form->getValue('created_by');
if ($creatorID) {
    $creator = JFactory::getUser($creatorID);
    /* @var $creator JUser */
    if ($creator->id) {
        echo '<tr><td class="key">' . $this->form->getLabel('created_by') . '</td>';
        echo '<td>' . $creator->name . '</td></tr>';
    }
}
if ($this->document->created) {
    echo '<tr><td class="key">' . $this->form->getLabel('created') . '</td>';
    echo '<td>' . JHtml::date($this->form->getValue('created'), JText::_('DATE_FORMAT_LC2')) . '</td></tr>';
}
$modifierID = $this->form->getValue('modified_by');
if ($modifierID) {
    $modifier = JFactory::getUser($modifierID);
    /* @var $modifier JUser */
    if ($modifier->id) {
        echo '<tr><td class="key">' . $this->form->getLabel('modified_by') . '</td>';
        echo '<td>' . $modifier->name . '</td></tr>';
    }
}

if (JoomDOCHelper::canViewModified($this->document->created, $this->document->modified)) {
    echo '<tr><td class="key">' . $this->form->getLabel('modified') . '</td>';
    echo '<td>' . JHtml::date($this->form->getValue('modified'), JText::_('DATE_FORMAT_LC2')) . '</td></tr>';
}
if ($this->access->canEditState) {
    echo '<tr><td class="key">' . $this->form->getLabel('publish_up') . '</td><td>' . $this->date('publish_up') . '</td></tr>';
    echo '<tr><td class="key">' . $this->form->getLabel('publish_down') . '</td><td>' . $this->date('publish_down') . '</td></tr>';
}
echo '</table>';

echo JHtmlJoomDOC::endTab();

