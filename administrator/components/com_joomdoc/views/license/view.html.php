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

class JoomDOCViewLicense extends JoomDOCView {
    /**
     * @var JForm
     */
    protected $form;
    /**
     * @var JObject
     */
    protected $license;
    
    /**
     * License edit page.
     *
     * @param string $tpl used template name
     * @return void
     */
    public function display ($tpl = null) {
        $this->form = $this->get('form');
        $this->license = $this->get('item');
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     * @return void
     */
    protected function addToolbar () {
        JRequest::setVar('hidemainmenu', true);
        JToolBarHelper::title(JText::_('JOOMDOC_LICENSE'), 'licenses');
        if (JoomDOCAccess::licenses()) {
            JToolBarHelper::apply(JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_APPLY), 'JTOOLBAR_APPLY');
            JToolBarHelper::save(JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_SAVE), 'JTOOLBAR_SAVE');
        } else {
            $bar = JToolBar::getInstance('toolbar');
            /* @var $bar JToolBar */
            $bar->appendButton('Standard', 'apply', 'JTOOLBAR_APPLY');
            $bar->appendButton('Standard', 'save', 'JTOOLBAR_SAVE');
        }
        JToolBarHelper::cancel(JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_CANCEL), 'JTOOLBAR_CLOSE');
    }
}
?>