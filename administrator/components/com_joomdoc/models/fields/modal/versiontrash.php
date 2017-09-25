<?php 

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author    	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

jimport('joomla.form.formfield');

class JFormFieldModal_VersionTrash extends JFormField {

	protected $type = 'versiontrash';
	
	/**
	 * (non-PHPdoc)
	 * @see JFormField::getInput()
	 */
    protected function getInput() {
    	
    	JFactory::getDocument()->addStyleDeclaration('
body label.inline {
  clear: none;
  float: left;
  margin: 0;
  min-width: 0;
  padding: 4px 15px 0 5px;
  width: auto;
}		
body input.inline {
  float: left;
}	
    	');
    	$this->value = (array) $this->value;
    	return '
    			<input type="text" name="' . $this->name . '" value="' . abs(JArrayHelper::getValue($this->value, 0, 0, 'int')) . '" id="' . $this->id . '_days" size="1" class="input-mini inline"  />
    			<label for="' . $this->id . '_days" class="inline">' . JText::_('JOOMDOC_VERSIONS_TRASH_DAYS') . '</label>
    	
    			<input type="text" name="' . $this->name . '" value="' . abs(JArrayHelper::getValue($this->value, 1, 0, 'int')) . '" id="' . $this->id . '_months" size="1" class="input-mini inline" />
    			<label for="' . $this->id . '_months" class="hasTip inline" title="'.JText::_('JOOMDOC_VERSIONS_TRASH_MONTHS').'::'.JText::_('JOOMDOC_VERSIONS_TRASH_MONTHS_DESC').'">' 
    				. JText::_('JOOMDOC_VERSIONS_TRASH_MONTHS') . '</label>
    	
    			<input type="text" name="' . $this->name . '" value="' . abs(JArrayHelper::getValue($this->value, 2, 0, 'int')) . '" id="' . $this->id . '_years" size="1" class="input-mini inline" />
    			<label for="' . $this->id . '_years" class="inline">' . JText::_('JOOMDOC_VERSIONS_TRASH_YEARS') . '</label>
    	
    			<br/><br/>
    			
    			<label class="hasTip" title="'.JText::_('JOOMDOC_VERSIONS_TRASH_CRON') . '::'.JText::_('JOOMDOC_VERSIONS_TRASH_CRON_DESC') . '">' 
    					. JText::_('JOOMDOC_VERSIONS_TRASH_CRON').'</label>
    			<label>
    					<a href="' . JURI::root() . 'index.php?option=com_joomdoc&amp;task=documents.versiontrash&amp;secret=' . JArrayHelper::getValue($this->value, 3) . '" target="_blank">' 
    						. JURI::root() . 'index.php?option=com_joomdoc&amp;task=documents.versiontrash&amp;secret=' . JArrayHelper::getValue($this->value, 3) . '</a>
    			</label>
    							
    			<br/><br/>
    							
    			<label for="' . $this->id . '_secret" class="hasTip" title="' . JText::_('JOOMDOC_VERSIONS_TRASH_SECRET', true) . '::' . JText::_('JOOMDOC_VERSIONS_TRASH_SECRET_DESC', true) . '">' 
    				. JText::_('JOOMDOC_VERSIONS_TRASH_SECRET') . '</label>
    			<input type="text" name="' . $this->name . '" value="' . JArrayHelper::getValue($this->value, 3) . '" id="' . $this->id . '_secret" />
    	
    		';
    }
}