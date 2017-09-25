<?php

/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.1
 */
class JFormFieldSuggest extends JFormFieldList {

    public $type = 'Tag';

    const PREFIX = '#new#';

    /**
     * Method to get the field input for a tag field.
     *
     * @return  string  The field input.
     *
     * @since   3.1
     */
    protected function getInput() {
        JHtml::_('joomdoc.chosen', $this->id);

        JFactory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {

						var customTagPrefix = '" . self::PREFIX . "';

						// Method to add tags pressing enter
						$('#" . $this->id . "_chzn input').keyup(function(event) {

							// Tag is greater than the minimum required chars and enter pressed
							if (this.value && this.value.length >= " . 1 . " && (event.which === 13 || event.which === 188)) {

								// Search an highlighted result
								var highlighted = $('#" . $this->id . "_chzn').find('li.active-result.highlighted').first();

								// Add the highlighted option
								if (event.which === 13 && highlighted.text() !== '')
								{
									// Extra check. If we have added a custom tag with this text remove it
									var customOptionValue = customTagPrefix + highlighted.text();
									$('select#" . $this->id . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

									// Select the highlighted result
									var tagOption = $('select#" . $this->id . " option').filter(function () { return $(this).html() == highlighted.text(); });
									tagOption.attr('selected', 'selected');
								}
								// Add the custom tag option
								else
								{
									var customTag = this.value;

									// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
									var tagOption = $('select#" . $this->id . " option').filter(function () { return $(this).html() == customTag; });
									if (tagOption.text() !== '')
									{
										tagOption.attr('selected', 'selected');
									}
									else
									{
										var option = $('<option>');
										option.text(this.value).val(customTagPrefix + this.value);
										option.attr('selected','selected');

										// Append the option an repopulate the chosen field
										$('select#" . $this->id . "').append(option);
									}
								}

								this.value = '';
								$('select#" . $this->id . "').trigger('liszt:updated');
								event.preventDefault();

							}
						});
					});
				})(jQuery);
				"
        );

        return parent::getInput();
    }

    /**
     * Method to get a list of tags
     *
     * @return  array  The field option objects.
     *
     * @since   3.1
     */
    protected function getOptions() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
                ->select('value, label AS text')
                ->from('#__joomdoc_option AS a')
                ->where('field = ' . (int) $this->element['field_id']);

        // Get the options.
        $db->setQuery($query);

        $options = $db->loadObjectList();
        $options = array_merge(parent:: getOptions(), $options);

        return $options;
    }

}
