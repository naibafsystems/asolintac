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
echo  '<table class="admintable">';
foreach ($this->form->getFieldsets('params') as $name => $fieldSet) {
    foreach ($this->form->getFieldset($name) as $field) {
        /* @var $field JFormField */
        if ($this->form->getFieldAttribute($field->fieldname, 'disabled', null, 'params') != 'true') {
            echo  '<tr>';
            echo  '<td class="key">' . $field->label . '</td>';
            echo  '<td>' . $field->input . '</td>';
            echo  '</tr>';
            $show = true;
        }
    }
}
echo  '</table>';
echo JHtmlJoomDOC::endTab();
