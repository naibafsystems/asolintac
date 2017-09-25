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

class JoomDOCViewMigration extends JoomDOCView
{

    /**
     * Display page with migration setting.
     *
     * @param $tpl used template
     * @return void
     */
    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('JOOMDOC_MIGRATION'), 'migration');
        JoomDOCHelper::setSubmenu(JOOMDOC_MIGRATION);
        // use the same dialog as during installation
        require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'install.php';
        com_joomdocInstallerScript::install(false, false);
        if (JoomDOCAccess::admin()) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences(JOOMDOC_OPTION, JOOMDOC_PARAMS_WINDOW_HEIGHT, JOOMDOC_PARAMS_WINDOW_WIDTH);
        }
    }
}
?>