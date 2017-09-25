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

echo JHtmlJoomDOC::addTab('JOOMDOC_PARAMS', 'params', 'tabone');

$fieldSets = $this->form->getFieldsets('params');
echo '<fieldset class="panelform"><table class="admintable">';
foreach ($fieldSets as $name => $fieldSet) {
    foreach ($this->form->getFieldset($name) as $field) {
        /* @var $field JFormField */
        if ($this->form->getFieldAttribute($field->fieldname, 'disabled', null, 'params') != 'true') {
            echo '<tr><td class="key">' . $field->label . '</td><td>' . $field->input . '</td></tr>';
        }
    }
}
echo '</table></fieldset>';

echo JHtmlJoomDOC::endTab();
