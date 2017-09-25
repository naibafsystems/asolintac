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

class JoomdocViewField extends JViewLegacy
{
	/**
	 * @var JObject
	 */
	protected $state;
	/**
	 * @var JoomDOCTableField
	 */
	protected $item;
	/**
	 * @var JForm
	 */
	protected $form;

	public function display($tpl = null)
	{
		if (!JFactory::getUser()->authorise('core.managafields', 'com_joomdoc'))
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('JOOMDOC_CUSTOM_FIELD'), 'field');
		JToolBarHelper::apply('field.apply');
		JToolBarHelper::save('field.save');
		JToolBarHelper::save2new('field.save2new');
		JToolBarHelper::save2copy('field.save2copy');
		JToolBarHelper::cancel('field.cancel');
	}
	
	protected function getFieldParams()
	{
		$fieldParams['field_param_size'] = "'1','8'";
		$fieldParams['field_param_maxlength'] = "'1'";
		$fieldParams['field_param_rows'] = "'6'";
		$fieldParams['field_param_cols'] = "'6'";
		$fieldParams['field_param_required'] = "'1','2','3','4','5','6','7','8','9'";
		$fieldParams['field_param_default'] = "'1','2','3','4','5','6','7','8','9'";
		$fieldParams['field_param_class'] = "'1','2','3','4','5','6','7','8','9'";
		$fieldParams['field_param_buttons'] = "'7'";
		return $fieldParams;				
	}
	
	protected function getFieldOptions()
	{
		return "'4','5','8','9'";
	}
}