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

class JoomDOCModuleConfig {
    var $mod = null;
    var $parent = null;
    var $limit = null;
    var $layout = null;
    var $moduleclass_sfx = null;
    var $documentOrdering = null;
    var $fileOrdering = null;
    var $show_text = null;
    var $crop_length = null;
    var $show_icons = null;
    var $show_filesize = null;
    var $link_type = null;
    var $show_listfields = null;

    /**
     * Get static filled instance.
     *
     * @param JParameter $params
     * @return JoomDOCModuleConfig
     */
    public static function getInstance ($params, $id) {
        static $instances;
        if (! isset($instances[$id])) {
            $instance = new JoomDOCModuleConfig();
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
        $this->mod = (int) $params->get('mod', 1);
        $this->parent = JString::trim($params->get('parent', ''));
        $this->limit = (int) $params->get('limit', 5);
        $this->layout = JString::trim($params->get('layout', 'default'));
        $this->moduleclass_sfx = JString::trim($params->get('moduleclass_sfx', ''));
        // Documents are ordered by publish_up value in newest list. Documents haven't param for popular ordering.
        $this->documentOrdering = $this->mod == 1 ? JOOMDOC_ORDER_PUBLISH_UP : null;
        // Files are ordered by upload value in newest list and order by hits value in popular list.
        $this->fileOrdering = $this->mod == 1 ? JOOMDOC_ORDER_UPLOAD : JOOMDOC_ORDER_HITS;
        $this->show_text = (int) $params->get('show_text', 1);
        $this->crop_length = (int) $params->get('crop_length', 50);
        $this->show_icons = (int) $params->get('show_icons', 0);
        $this->show_filesize = (int) $params->get('show_filesize', 0);
        $this->link_type = JString::trim($params->get('link_type', 'detail'));
        $this->show_listfields = (int) $params->get('show_listfields', 1);
    }
}
?>