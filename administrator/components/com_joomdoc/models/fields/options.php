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

class JFormFieldOptions extends JFormField {
	
    protected $type = 'Options';

    protected function getInput () {
        $app = JFactory::getApplication();
    	$id = JRequest::getInt('id');
        $options = (array) $app->getUserState('com_joomdoc.field.options');
        if (empty($options)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id, value, label')
                ->from('#__joomdoc_option')
                ->where('field = ' . $id)
                ->order('ordering');
            $options = $db->setQuery($query)->loadObjectList();
        }
        $app->setUserState('com_joomdoc.field.options', null);
?>    	
		<div style="padding: 20px 0px">
	       	<button id="joomdocoptionadd" class="btn btn-small" type="button">
    	   		<span class="icon-save-new"></span>
       			<?php echo JText::_('JOOMDOC_FIELD_ADD_OPTION'); ?>
       		</button>
       		<button id="joomdocoptiondispose" class="btn btn-small" type="button">
		   		<span class="icon-cancel"></span>
		    	<?php echo JText::_('JOOMDOC_FIELD_OPTION_DELETE'); ?>
			</button>
			<div class="clr"></div>
	       	<table>
	       		<thead id="joomdocoptionshead">
	       			<tr>
	       				<th></th>
	       				<th><?php echo JText::_('JOOMDOC_FIELD_OPTION_VALUE'); ?></th>
	       				<th><?php echo JText::_('JOOMDOC_FIELD_OPTION_LABEL'); ?></th>
	       			</tr>
	       		</thead>
	       		<tbody id="joomdocoptions">
	       			<?php 
	       				foreach ($options as $option) { 
	       					JFilterOutput::objectHTMLSafe($option);
	       			?>
	       					<tr>
	       					   	<td>
	       							<input type="checkbox" name="joomdoc_option_cb[]" value="1" />
			       				</td>
	       						<td>
	       							<input type="text" name="joomdoc_option_value[<?php echo $option->id; ?>]" size="20" value="<?php echo $option->value; ?>" />
			       				</td>
			       				<td>
			       					<input type="text" name="joomdoc_option_label[<?php echo $option->id; ?>]" size="20" value="<?php echo $option->label; ?>" />
			       				</td>
			       			</tr>
	       			<?php } ?>
	       		</tbody>
	       	</table>
       	</div>
       	<script type="text/javascript">
	        // <![CDATA[
				window.addEvent('domready', function() {
					if ($('joomdocoptions').getElements('tr').length == 0)
						$('joomdocoptionshead').hide();

					$('joomdocoptionadd').addEvent('click', function(event) {
						if ($('jform_type').value != '<?php echo JOOMDOC_FIELD_SELECT; ?>' && $('jform_type').value != '<?php echo JOOMDOC_FIELD_CHECKBOX; ?>' && $('jform_type').value != '<?php echo JOOMDOC_FIELD_MULTI_SELECT; ?>' && $('jform_type').value != '<?php echo JOOMDOC_FIELD_SUGGEST; ?>') {
							alert("<?php echo JText::_('JOOMDOC_FIELD_OPTIONS_INVALID_TYPE', true); ?>");				
						} else { 
							Elements.from('<tr><td><input type="checkbox" name="joomdoc_option_cb[]" value="1" /></td><td><input type="text" name="joomdoc_option_value[]" size="20" /></td><td><input type="text" name="joomdoc_option_label[]" size="20" /></td></tr>').inject($('joomdocoptions'), 'inside');
							$('joomdocoptionshead').show();
						}
						event.stopPropagation();
					});

					$('joomdocoptiondispose').addEvent('click', function(event) {
						document.getElements('input[name^=joomdoc_option_cb]').each(function(e) {
							if (e.checked)
								e.getParent().getParent().dispose();
						});
						if ($('joomdocoptions').getElements('tr').length == 0)
							$('joomdocoptionshead').hide();
						event.stopPropagation();
					});
				});
    	    // ]]>                 
       	</script>
<?php 
    }
}
?>