<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JoomDOCModuleExplorerConfig {
    var $parent = null;
    var $layout = null;
    var $moduleclass_sfx = null;

    /**
     * Get static filled instance.
     *
     * @param JParameter $params
     * @return JoomDOCModuleExplorerConfig
     */
    public static function getInstance ($params, $id) {
        static $instances;
        if (! isset($instances[$id])) {
            $instance = new JoomDOCModuleExplorerConfig();
            $instance->init($params);
            $instances[$id] = $instance;
        }
        return $instances[$id];
    }

    /**
     * Load setting.
     *
     * @param JParameter $params
     */
    private function init ($params) {
        $this->parent = JString::trim($params->get('parent', ''));
        $this->layout = JString::trim($params->get('layout', 'default'));
        $this->moduleclass_sfx = JString::trim($params->get('moduleclass_sfx', ''));
    }
}
?>