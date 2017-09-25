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

jimport('joomla.application.component.controllerform');

class JoomDOCControllerLicense extends JoomDOCControllerForm {
    /**
     * Allow add license operation.
     *
     * @return boolean
     */
    protected function allowAdd($data = array()) {
        return JoomDOCAccess::licenses();
    }
    /**
     * Allow edit license operation.
     *
     * @return boolean
     */
    protected function allowEdit($data = array(), $key = 'id') {
        return JoomDOCAccess::licenses();
    }
    /**
     * Allow save license operation.
     *
     * @return boolean
     */
    protected function allowSave($data, $key = 'id') {
        return JoomDOCAccess::licenses();
    }
    /**
     * Get license model.
     *
     * @return JoomDOCModelLicense
     */
    public function getModel($name = JOOMDOC_LICENSE, $prefix = JOOMDOC_MODEL_PREFIX, $config = array('ignore_request' => true)) {
        return JModelLegacy::getInstance($name, $prefix, $config);
    }
}
?>