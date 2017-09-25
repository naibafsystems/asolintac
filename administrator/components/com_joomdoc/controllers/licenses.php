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

class JoomDOCControllerLicenses extends JoomDOCControllerAdmin {

    /**
     * Create object and set task's map.
     *
     * @param array $config
     * @return void
     */
    public function __construct ($config = array ()) {
        parent::__construct($config);
        $this->registerTask('trash', 'trash');
    }

    /**
     * Proxy for getModel.
     *
     * @return JoomDOCModelLicense
     */
    public function getModel($name = JOOMDOC_LICENSE, $prefix = JOOMDOC_MODEL_PREFIX, $config = array('ignore_request' => true)) {
        return JModelLegacy::getInstance($name, $prefix, $config);
    }

    /**
     * Set license's publish state.
     *
     * @return void
     */
    public function publish () {
        if (JoomDOCAccess::licenses())
            parent::publish();
    }

    /**
     * Set selected license as default or non default.
     *
     * @param int $value default value (default/non default)
     * @return void
     */
    public function defaults ($value = JOOMDOC_STATE_DEFAULT) {
        if (JoomDOCAccess::licenses()) {
            $model = $this->getModel();
            $cid = JRequest::getVar('cid', array(0), 'default', 'array');
            $id = reset($cid);
            $success = $model->defaults($id, $value);
            if ($success)
                $msg = $value == JOOMDOC_STATE_DEFAULT ? 'JOOMDOC_SUCCESS_DEFAULT' : 'JOOMDOC_SUCCESS_UNDEFAULT';
            else
                $msg = 'JOOMDOC_UNSUCCESS_DEFAULT';
            $type = $success ? 'message' : 'error';
            $this->setRedirect(JoomDOCRoute::viewLicenses(), JText::_($msg), $type);
        }
    }

    /**
     * Set selected license as non default.
     *
     * @return void
     */
    public function undefaults () {
        $this->defaults(JOOMDOC_STATE_UNDEFAULT);
    }

    /**
     * Trash selected license's.
     *
     * @return void
     */
    public function delete () {
        if (JoomDOCAccess::licenses()) {
            $model = $this->getModel();
            $cid = JRequest::getVar('cid', array(0), 'default', 'array');
            $count = $model->delete($cid);
            $this->setRedirect(JoomDOCRoute::viewLicenses(), JText::sprintf('JOOMDOC_LICENSES_TRASHED', $count));
        }
    }

    /**
     * Delete trashed license's.
     *
     * @return void
     */
    public function trash () {
        if (JoomDOCAccess::licenses()) {
            $model = $this->getModel();
            $count = $model->trash();
            $this->setRedirect(JoomDOCRoute::viewLicenses(), JText::sprintf('JOOMDOC_TRASH_EMPTY', $count));
        }
    }
}
?>