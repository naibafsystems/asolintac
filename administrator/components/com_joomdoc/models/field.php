<?php

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author   	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class JoomdocModelField extends JModelAdmin
{

	protected $text_prefix = JOOMDOC_OPTION;

	public function getTable($type = JOOMDOC_FIELD, $prefix = JOOMDOC_TABLE_PREFIX, $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		return $this->loadForm(JOOMDOC_OPTION.'.'.JOOMDOC_FIELD, JOOMDOC_FIELD, array('control' => 'jform', 'load_data' => $loadData));
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState(JOOMDOC_OPTION.'.edit.'.JOOMDOC_FIELD.'.data', array());
		if (empty($data))
			$data = $this->getItem();
		return $data;
	}

	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__joomdoc_field');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
    
    public function validate($form, $data, $group = null) {
        $app = JFactory::getApplication();
        $values = JRequest::getVar('joomdoc_option_value', array(), 'default', 'array');
        $labels = JRequest::getVar('joomdoc_option_label', array(), 'default', 'array');
        if (!empty($values) && !empty($labels)) {
            // check if field options values and labels are unique
            if (count(array_unique($values)) < count($values) || count(array_unique($labels)) < count($labels)) {
                $this->setError(JText::_('JOOMDOC_FIELD_OPTIONS_NONUNIQUE'));
                $options = array();
                foreach ($values as $id => $value) {
                    $option = new stdClass();
                    $option->id = $id;
                    $option->value = $value;
                    $option->label = $labels[$id];
                    $options[] = $option;
                }
                // save current option list into session
                $app->setUserState('com_joomdoc.field.options', $options);
                return false;
            }
        }
        // options are unique - cleanup session
        $app->setUserState('com_joomdoc.field.options', null);
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        // check if field title is unique
        $query->select('COUNT(*)')
                ->from('#__joomdoc_field')
                ->where('LOWER(TRIM(title)) = ' . $db->q(JString::strtolower(JString::trim($data['title']))))
                ->where('id <> ' . (int) $data['id']);
        if ($db->setQuery($query)->loadResult()) {
            $this->setError(JText::_('JOOMDOC_FIELD_NONUNIQUE'));
            return false;
        }
        return parent::validate($form, $data, $group);
    }
	
	public function save($data)
	{
		$success = parent::save($data);
		
		if ($success) {
			
			// request option list
			$values = JRequest::getVar('joomdoc_option_value', array(), 'default', 'array');
			$labels = JRequest::getVar('joomdoc_option_label', array(), 'default', 'array');

			// prepare database table
			$option = JTable::getInstance('Option', JOOMDOC_TABLE_PREFIX);
			/* @var $option JoomDOCTableOption */
			$option->field = $this->getState($this->getName() . '.id');
			$option->ordering = 0;

			$query = $this->getDbo()->getQuery(true);
			$query->select('id')->from('#__joomdoc_option')->where('field = ' . (int) $option->field);
			$ids = $this->getDbo()->setQuery($query)->loadColumn();
			$sid = array();
			
			foreach ($values as $id => $value) {
				$option->id = in_array($id, $ids) ? $id : null;
				$option->value = $value;
				$option->label = $labels[$id];
				$option->ordering ++;
				$option->store();
				$sid[] = (int) $option->id;
			}
			
            $query->clear()
                ->delete('#__joomdoc_option')
				->where('field = ' . (int) $option->field);
            if ($sid) {
                $query->where('id NOT IN (' . implode(', ', $sid) . ')');
            }
			$this->getDbo()->setQuery($query)->query();
		}
		
		return $success;
	}
    
    public function getSuggest($term, $fieldId) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('value, label')
                ->from('#__joomdoc_option')
                ->where('LOWER(label) LIKE ' . $db->q('%' . $term . '%'))
                ->where('field = ' . (int) $fieldId);
        return $db->setQuery($query)->loadObjectList();
    }
}