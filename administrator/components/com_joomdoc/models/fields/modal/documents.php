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

include_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'defines.php');
include_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'route.php');

class JFormFieldModal_Documents extends JFormField
{
    /**
     * The form field type.
     *
     * @var		string
     * @since	1.6
     */
    protected $type = 'Modal_Documents';

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     */
    protected function getInput()
    {
        JHtml::_('behavior.modal', 'a.modal');
        
        $script[] = '	function jSelectJoomdocDocument(id, title) {';
        $script[] = '		document.id("' . $this->id . '_id").value = id.replace(/{ds}/g,"'.addslashes(DIRECTORY_SEPARATOR).'");';
        $script[] = '		document.id("' . $this->id . '_name").value = title;';
        $script[] = '		SqueezeBox.close();';
        $script[] = '	}';
        $script[] = '	function jResetJoomdocDocument() {';
        $script[] = '		document.id("' . $this->id . '_id").value = "";';
        $script[] = '		document.id("' . $this->id . '_name").value = "";';
        $script[] = '	}';
        
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        
        $link = JoomDOCRoute::modalDocuments();
        
        $db = JFactory::getDBO();
        /* @var $db = JDatabaseMySQL */
        $db->setQuery('SELECT `title` FROM `#__joomdoc`' . ' WHERE `path` = ' . $db->quote($this->value));
        $title = $db->loadResult();
        
        if (empty($title))
            $title = $this->value;
        
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        if (!JOOMDOC_ISJ3)
        	$html[] = '<div class="fltlft">';
        else
        	$html[] = '  <span class="input-append">';
        $html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
        if (!JOOMDOC_ISJ3)
        	$html[] = '</div>';
        
        if (!JOOMDOC_ISJ3) {
        	$html[] = '<div class="button2-left">';
        	$html[] = '  <div class="blank">';
        }
        
        $html[] = '	<a class="btn btn-primary modal" title="' . JText::_('JOOMDOC_CHANGE_DOCUMENT') . '"  href="' . JRoute::_($link) . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('JOOMDOC_CHANGE_DOCUMENT_BUTTON') . '</a>';
        if (!JOOMDOC_ISJ3) {
        	$html[] = '  </div>';
	        $html[] = '</div>';
        }

        if (!JOOMDOC_ISJ3) {
        	$html[] = '<div class="button2-left">';
        	$html[] = '  <div class="blank">';
        }
        $html[] = '     <a class="btn" href="javascript:jResetJoomdocDocument()" title="">' . JText::_('JOOMDOC_RESET') . '</a>';
        if (!JOOMDOC_ISJ3) {
        	$html[] = '  </div>';
        	$html[] = '</div>';
        } else
        	$html[] = '  </span>';
        
        $class = $this->required ? ' class="required modal-value"' : '';
        
        $html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $this->value . '" />';
        
        return implode("\n", $html);
    }
}