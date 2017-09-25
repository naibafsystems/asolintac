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

JLoader::register('JButton', JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'toolbar' . DIRECTORY_SEPARATOR . 'button.php');

class JButtonDisabled extends JButton {
    private $_count;
    function __construct ($parent = null) {
        $this->_name = 'Disabled';
        $this->_count = 0;
        parent::__construct($parent);
    }

    public function fetchButton ($type = 'Disabled', $name = '', $text = '') {
        return '<span class="icon-disabled"><span class="' . $this->fetchIconClass($name) . '"> </span>' . JText::_($text) . '</span>';
    }

    public function fetchId () {
        return $this->_name . (++$this->_count);
    }
}
?>