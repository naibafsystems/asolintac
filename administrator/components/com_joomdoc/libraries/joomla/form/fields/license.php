<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_joomdoc
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldLicense extends JFormFieldList {
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    public $type = 'License';

    /**
     * Method to get the field input markup.
     *
     * @return  string   The field input markup.
     * @since   11.1
     */
    protected function getInput () {
        $db = JFactory::getDbo();
        /* @var $db JDatabaseMySQL */
        $query = 'SELECT `id`, `title` FROM `#__joomdoc_license` ORDER BY `title` ASC';
        $db->setQuery($query);
        $licenses = $db->loadObjectList();
        $option = JHtml::_('select.option', 0, ' - ', 'id', 'title');
        /* @var $option JObject empty option */
        array_unshift($licenses, $option);
        $attr = '';
        $attr .= $this->element['class'] ? ' class="' . $this->element['class'] . '" ' : '';
        $attr .= $this->element['disabled'] == 'true' ? ' disabled="disabled" ' : '';
        $attr .= $this->element['size'] ? ' size="' . $this->element['size'] . '" ' : '';
        $attr .= $this->multiple ? ' multiple="multiple" ' : '';
        $attr .= $this->element['onchange'] ? ' onchange="' . $this->element['onchange'] . '" ' : '';
        $select = JHtml::_('select.genericlist', $licenses, $this->name, $attr, 'id', 'title', $this->value, $this->id);
        return $select;
    }
}
?>