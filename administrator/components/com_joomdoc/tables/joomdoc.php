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

class JoomDOCTable extends JTable {

    public function store($updateNulls = false) {
        if (($success = parent::store($updateNulls))) {
            JPluginHelper::importPlugin('content');
            JEventDispatcher::getInstance()
                    ->trigger('onContentAfterSave', array(JOOMDOC_OPTION . '.' . $this->_getName(), $this->getProperties()));
        }
        return $success;
    }

    protected function _getName() {
        return str_replace(JOOMDOC_TABLE_PREFIX, '', get_class($this));
    }

}
