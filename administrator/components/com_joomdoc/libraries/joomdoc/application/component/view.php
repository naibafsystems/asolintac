<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.application.component.view');

if (!class_exists('JViewLegacy')) {
    
    class JViewLegacy extends JView { }
    
}

class JoomDOCView extends JViewLegacy {

    /**
     * Browse list pagination.
     *
     * @var JPagination
     */
    protected $pagination;
    /**
     * Request state.
     *
     * @var JObject
     */
    protected $state;
    /**
     * JForm object.
     *
     * @var JForm
     */
    protected $form;

    /**
     * Get tooltip format.
     *
     * @param string $title tooltip title
     * @param string $text tooltip text
     * @return string
     */
    public function getTooltip ($title, $text = null) {
        return $this->escape(JText::_($title)) . '::' . $this->escape(JText::_($text ? $text : $title . '_DESC'));
    }

    /**
     * Get property state value escaped.
     *
     * @param string $name property name
     * @return mized
     */
    public function getState ($name) {
        if (isset($this->state))
            return $this->escape($this->state->get($this->getStateName($name)));
        return null;
    }

    /**
     * Get name of property field.
     *
     * @param string $name property name
     * @return string
     */
    public static function getFieldName ($name) {
        return sprintf('filter_%s', $name);
    }

    /**
     * Get hash for store property in model state.
     *
     * @param string $name property name
     * @return string
     */
    public static function getStateName ($name = null) {
        if ($name)
            $name = sprintf('filter.%s', $name);
        return $name;
    }

    /**
     * Display date form field. If field value equal database null date reset to empty string.
     *
     * @param string $field form field name
     * @return string field form html
     */
    public function date ($field) {
        static $nullDate;
        if (empty($nullDate)) {
            $db = JFactory::getDbo();
            /* @var $db JDatabaseMySQL */
            $nullDate = $db->getNullDate();
        }
        if ($this->form->getValue($field) == $nullDate)
            $this->form->setValue($field, null, '');
        return $this->form->getInput($field);
    }
}
?>