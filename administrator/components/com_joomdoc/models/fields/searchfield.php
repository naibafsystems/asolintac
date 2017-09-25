<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage		JoomDOC
 * @author      	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright		Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('list');

class JFormFieldSearchfield extends JFormFieldList {

    protected $type = 'Searchfield';

    protected function getOptions() {
        require_once JPATH_ADMINISTRATOR . '/components/com_joomdoc/defines.php';
        JFactory::getLanguage()->load(JOOMDOC_OPTION);
        $options = parent::getOptions();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title')
                ->from('#__joomdoc_field')
                ->order('title')
                ->where('type IN(' . JOOMDOC_FIELD_TEXT . ',' . JOOMDOC_FIELD_TEXTAREA . ',' . JOOMDOC_FIELD_EDITOR . ',' . JOOMDOC_FIELD_DATE . ')');
        $fields = $db->setQuery($query)->loadObjectList();
        foreach ($fields as $field) {
            $options[] = JHtml::_('select.option', 'joomdoc_keywords_field' . $field->id, $field->title);
        }
        return $options;
    }

}
