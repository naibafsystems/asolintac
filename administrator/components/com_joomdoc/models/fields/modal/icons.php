<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

jimport('joomla.form.formfield');

include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'defines.php');

$language = JFactory::getLanguage();
/* @var $language JLanguage */
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, 'en-GB');
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, $language->getTag(), true);

class JFormFieldModal_Icons extends JFormField {
	
    protected $type = 'Icons';

    protected function getInput () {

       	$control_name = null;
       	$field = JText::_('JOOMDOC_ICON_THEME_NOT_AVAILABLE');
       	if (JFolder::exists(JOOMDOC_PATH_ICONS)) {
       		$themes = JFolder::folders(JOOMDOC_PATH_ICONS, '.', false, false);
       		foreach ($themes as $theme)
       			$options[] = JHtml::_('select.option', $theme, ucwords($theme), 'id', 'title');
       		if (isset($options)) {
       			$fieldName = $control_name ? $control_name . '[' . $this->name . ']' : $this->name;
       			$field = JHtml::_('select.genericlist', $options, $fieldName, '', 'id', 'title', $this->value);
       		}
       	}
       	return $field;
    }
}
?>