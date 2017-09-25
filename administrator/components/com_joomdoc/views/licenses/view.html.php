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

jimport('joomla.html.pagination');

class JoomDOCViewLicenses extends JoomDOCView {
	/**
	 * Text filter
	 *
	 * @var string
	 */
	protected $filter;
    /**
     * Licneses list.
     *
     * @var array
     */
    public $licenses;
    /**
     * Pagination support.
     *
     * @var JPagination
     */
    public $pagination;
    /**
     * Browse list states.
     *
     * @var JObject
     */
    public $state;
    /**
     * Display page with licenses browse table.
     *
     * @param $tpl used template
     * @return void
     */
    public function display ($tpl = null) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $this->getModel()->setState(JOOMDOC_FILTER_KEYWORDS, $mainframe->getUserStateFromRequest('joomdoc_licenses_filter', 'filter', '', 'string'));
        $this->licenses = $this->get('items');
        $this->state = $this->get('state');
        $this->filter = $this->state->get('filter');
        $this->pagination = new JPagination($this->get('total'), $this->state->get(JOOMDOC_FILTER_START), $this->state->get(JOOMDOC_FILTER_LIMIT));
        $this->addToolbar();
        JoomDOCHelper::setSubmenu(JOOMDOC_LICENSES);
        parent::display($tpl);
    }

    public function addToolbar () {
        $bar = JToolBar::getInstance('toolbar');
        /* @var $bar JToolBar */
        JToolBarHelper::title(JText::_('JOOMDOC_LICENSES'), 'licenses');
        if (JoomDOCAccess::licenses()) {
            JToolBarHelper::addNew(JoomDOCHelper::getTask(JOOMDOC_LICENSE, JOOMDOC_TASK_ADD));
            JToolBarHelper::publishList(JoomDOCHelper::getTask(JOOMDOC_LICENSES, JOOMDOC_TASK_PUBLISH));
            JToolBarHelper::unpublishList(JoomDOCHelper::getTask(JOOMDOC_LICENSES, JOOMDOC_TASK_UNPUBLISH));
            JToolBarHelper::divider();
            JToolBarHelper::deleteList('JOOMDOC_ARE_YOU_SURE_DELETE_LICENSE', JoomDOCHelper::getTask(JOOMDOC_LICENSES, JOOMDOC_TASK_DELETE));
            $bar->appendButton('Confirm', 'JOOMDOC_ARE_YOU_SURE_EMPTY_TRASH', 'trash', 'JTOOLBAR_EMPTY_TRASH', JoomDOCHelper::getTask(JOOMDOC_LICENSES, JOOMDOC_TASK_TRASH), false);
        } else {
            $bar->appendButton('Standard', 'new', 'JTOOLBAR_NEW');
            $bar->appendButton('Standard', 'publish', 'JTOOLBAR_PUBLISH');
            $bar->appendButton('Standard', 'unpublish', 'JTOOLBAR_UNPUBLISH');
            JToolBarHelper::divider();
            $bar->appendButton('Standard', 'remove', 'JTOOLBAR_DELETE');
            $bar->appendButton('Standard', 'trash', 'JTOOLBAR_TRASH');
        }
        if (JoomDOCAccess::admin()) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
        }
    }
}
?>