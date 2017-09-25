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

class JoomdocViewFields extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null) {
		if (!JFactory::getUser()->authorise('core.managafields', 'com_joomdoc'))
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		JoomDOCHelper::setSubmenu(JOOMDOC_FIELDS);
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolBarHelper::title(JText::_('JOOMDOC_CUSTOM_FIELDS'), 'field');
		JToolBarHelper::addNew('field.add');
		JToolBarHelper::editList('field.edit');
		JToolBarHelper::divider();
		JToolBarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_EMPTY_TRASH');
		JToolBarHelper::trash('fields.trash');
        if (JoomDOCAccess::admin()) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
        }
	}
}