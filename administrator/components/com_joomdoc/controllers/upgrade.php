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

jimport('joomla.application.component.controlleradmin');

class JoomDOCControllerUpgrade extends JControllerAdmin
{
    
    /**
     * Main model
     * 
     * @var JoomDOCModelUpgrade
     */
    var $_model;

    function __construct($config = array())
    {
        parent::__construct($config);
        $this->_model = &$this->getModel('upgrade');
    }

    /**
     * Start component upgrade from remote server.
     */
    function run()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        $result = $this->_model->upgrade();
        $this->_model->setState('result', $result);
        JFactory::getApplication()->enqueueMessage($this->_model->getState('message'));
        JFactory::getApplication()->redirect(sprintf('index.php?option=%s&view=upgrade&layout=message', JOOMDOC_OPTION));
    }
}

?>