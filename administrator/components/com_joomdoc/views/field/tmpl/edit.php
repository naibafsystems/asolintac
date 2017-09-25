<?php

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author   	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/* @var $this JoomdocViewField */

require_once(JPATH_COMPONENT_ADMINISTRATOR.'/libraries/joomdoc/html/joomdoc.php');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.framework', true);

?>
<script type="text/javascript">
	// <![CDATA[
	Joomla.submitbutton = function(task)
	{
		if (task == 'field.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
	window.addEvent('domready', function() {
		/**
		 * Event after change field type
		 */
		$('jform_type').addEvent('change', function() {
			setFieldParams();
			setFieldOptions();
		});
		/**
		 * Initial event after load page
		 */
		setFieldParams();
		setFieldOptions();
	});

	/**
	 * Hide or show params according to selected form type.
	 */
	function setFieldParams() {
		<?php foreach ($this->getFieldParams() as $name => $params) { ?>
			[<?php echo $params; ?>].contains($('jform_type').value) ? $('<?php echo $name; ?>').show() : $('<?php echo $name; ?>').hide();
		<?php } ?>
	}
	
	/**
	 * Hide or show options according to selected form type.
	 */
	function setFieldOptions() {
		[<?php echo $this->getFieldOptions(); ?>].contains($('jform_type').value) ? $('field_options').show() : $('field_options').hide();
	}
    // ]]>                 
</script>

<form action="<?php echo JRoute::_('index.php?option=com_joomdoc&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo JHtmlJoomDOC::startTabs('myTab', 'details');
	echo JHtmlJoomDOC::addTab('JDETAILS', 'details', 'myTab'); ?>
	<div style="width: 45%; float: left">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JDETAILS'); ?></legend>
			<table class="admintable">
				<tr><td class="key"><?php echo $this->form->getLabel('title'); ?></td><td>
				<?php echo $this->form->getInput('title'); ?></td></tr>
				
				<tr><td class="key"><?php echo $this->form->getLabel('type'); ?></td><td>
				<?php echo $this->form->getInput('type'); ?></td></tr>
				
				<tr id="field_options"><td class="key"><?php echo $this->form->getLabel('options'); ?></td><td>
				<?php echo $this->form->getInput('options'); ?></td></tr>				
				
				<tr><td class="key"><?php echo $this->form->getLabel('published'); ?></td><td>
				<?php echo $this->form->getInput('published'); ?></td></tr>

				<tr><td class="key"><?php echo $this->form->getLabel('ordering'); ?></td><td>
				<?php echo $this->form->getInput('ordering'); ?></td></tr>

				<tr><td class="key"><?php echo $this->form->getLabel('id'); ?></td><td>
				<?php echo $this->form->getInput('id'); ?></td></tr>
			</table>
		</fieldset>
		<div class="clr"></div>
	</div>
	<div style="width: 45%; float: left">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JOOMDOC_FIELD_PARAMS'); ?></legend>
			<table class="admintable">
				<?php foreach ($this->form->getFieldset('html') as $field) { ?>
					<tr id="field_param_<?php echo $field->fieldname; ?>"><td class="key"><?php echo $field->label; ?></td><td>
					<?php echo $field->input; ?></td></tr>
				<?php } ?>
			</table>
		</fieldset>
		<div class="clr"></div>
	</div>
	<div class="clr"></div>
	<?php echo JHtmlJoomDOC::endTab();
    echo JHtmlJoomDOC::addTab('JCONFIG_PERMISSIONS_LABEL', 'permissions', 'myTab'); ?>
	<fieldset>
		<?php echo $this->form->getInput('rules'); ?>
	</fieldset>
	<?php echo JHtmlJoomDOC::endTab();
    echo JHtmlJoomDOC::endTabs(); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->form->getInput('asset_id'); ?>
	<div class="clr"></div>	
</form>