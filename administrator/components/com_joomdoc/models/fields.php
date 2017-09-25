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

jimport('joomla.application.component.modellist');

class JoomdocModelFields extends JModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'ordering', 'a.ordering',
			);
		}
		
		parent::__construct($config);
	}


	protected function populateState($ordering = null, $direction = null) {
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		parent::populateState('a.title', 'asc');
	}

	protected function getListQuery() {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.type, a.ordering, a.checked_out, a.checked_out_time, a.published')->from('#__joomdoc_field AS a');
		$query->select('u.name AS editor')->join('LEFT', '#__users AS u ON u.id = a.checked_out');

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('a.title LIKE '.$search);
		}

		$query->order($db->escape($this->state->get('list.ordering').' '.$this->state->get('list.direction')));

		return $query;
	}
	
	public function getAllowedFields($rule)
	{
		$user = JFactory::getUser();
		// load published fields
		$query = $this->getDbo()->getQuery(true);
		$query->select('id, title, type, params')
			->from('#__joomdoc_field')
			->where('published = 1')
			->order('ordering');
		$fields = $this->getDbo()->setQuery($query)->loadObjectList();
		
		$ids = array();
		// check fields rule
		foreach ($fields as $i => $field) {
			if (!$user->authorise($rule, 'com_joomdoc.field.' . $field->id))
				unset($fields[$i]);
			else {
				$ids[] = $field->id;
				$field->params = new JRegistry($field->params);
			}
		}
		 
		if ($ids) {
			// load fields options
			$query = $this->getDbo()->getQuery(true);
			$query->select('id, field, value, label')
				->from('#__joomdoc_option')
				->where('field IN (' . implode(', ', $ids) . ')')
				->order('ordering');
			$options = $this->getDbo()->setQuery($query)->loadObjectList();
		
			if ($options)
				foreach ($fields as $field)
					foreach ($options as $option)
						if ($field->id == $option->field)
							$field->options[] = $option;
		}
		
		return $fields;
	}
}