<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class JoomDOCViewUpgrade extends JoomDOCView
{

    /**
     * Display page with upgrade setting.
     * 
     * @param $tpl used template
     * @return void
     */
    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('Online Upgrade'), 'upgrade');
        if ($this->getLayout() == 'message') {
            $url = sprintf('index.php?option=%s&view=upgrade', ARTIO_UPGRADE_OPTION);
            $redir = JRequest::getVar('redirto', null, 'post');
            if (! is_null($redir))
                $url = sprintf('index.php?option=%s&%s', ARTIO_UPGRADE_OPTION, $redir);
            JToolBarHelper::back('Continue', $url);
            $this->assign('url', $url);
        }
        $language = JFactory::getLanguage();
        /* @var $language JLanguage */
        $language->load(sprintf('%s.upgrade', ARTIO_UPGRADE_OPTION), JPATH_ADMINISTRATOR);
        JoomDOCHelper::setSubmenu(JOOMDOC_UPGRADE);
        if (JoomDOCAccess::admin()) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
        }
        parent::display($tpl);
    }
}

?>