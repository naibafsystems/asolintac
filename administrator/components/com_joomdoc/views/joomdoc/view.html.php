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

class JoomDOCViewJoomDOC extends JoomDOCView
{

    /**
     * Display page with component cpanel.
     * 
     * @param $tpl used template
     * @return void
     */
    public function display($tpl = null)
    {
        $this->addToolbar();
        
        JoomDOCHelper::setSubmenu(JOOMDOC_JOOMDOC);
        
        parent::display($tpl);
    }

    /**
     * Add page main toolbar.
     * 
     * @return void
     */
    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('JOOMDOC'), 'joomdoc');
        if (JoomDOCAccess::admin())
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
    }
}

?>