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

class JoomDOCControllerFile extends JoomDOCControllerForm {
    public function __construct ($config = array ()) {
        parent::__construct($config);
        $this->registerTask('trash', 'trash');
    }
    /**
     * Get File Model.
     *
     * @return JoomDOCModelFile
     */
    public function getModel () {
        return JModelLegacy::getInstance(JOOMDOC_FILE, JOOMDOC_MODEL_PREFIX);
    }
    /**
     * Trash selected File's Version's.
     *
     * @return void
     */
    public function trash () {
        $this->setStates(JOOMDOC_TASK_TRASH);
    }
    /**
     * Untrash selected File. Search for latest version and publish it.
     * Others versions leave trashed.
     *
     * @return void
     */
    public function untrash () {
        $path = JoomDOCRequest::getPath();
        if (JoomDOCAccessFileSystem::untrash(null, $path)) {
            $model = $this->getModel();
            $result = $model->untrash($path);
            if ($result)
                $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::sprintf('JOOMDOC_SUCCESS_UNTRASH', $path));
            else
                $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::sprintf('JOOMDOC_UNSUCCESS_UNTRASH', $path), 'error');
        } else
            $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::sprintf('JOOMDOC_UNTRASH_DISALLOW', $path), 'notice');
    }

    /**
     * Restore selected File's Version's.
     *
     * @return void
     */
    public function restore () {
        $this->setStates(JOOMDOC_TASK_RESTORE);
    }

    /**
     * Set selected File's Version's State.
     *
     * @param string $state Name of new State (trash, restore)
     * @return void
     */
    public function setStates ($state) {
        $path = JoomDOCRequest::getPath();
        $count = 0;
        if (JoomDOCAccessFileSystem::manageVersions(false, $path)) {
            JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
            $cid = JRequest::getVar('cid', array(), 'default', 'array');
            $model = $this->getModel();
            $count = call_user_func(array($model, $state), $cid, $path);
        }
        $this->setRedirect(JoomDOCRoute::viewFileInfo($path), JText::sprintf('JOOMDOC_ITEMS_AFFECTED', $count));
    }

    /**
     * Revert selected Version as last Version.
     *
     * @return void
     */
    public function revert () {
        $path = JoomDOCRequest::getPath();
        $result = false;
        if (JoomDOCAccessFileSystem::manageVersions(false, $path)) {
            JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
            $cid = JRequest::getVar('cid', array(), 'default', 'array');
            $id = reset($cid);
            $model = $this->getModel();
            $result = $model->revert($id, $path);
        }
        $msg = $result ? JText::sprintf('JOOMDOC_REVERT_SUCCESS', $result->revertVersion, $result->newLastVersion, $result->oldLastVersion) : JText::_('JOOMDOC_REVERT_UNSUCCESS');
        $type = $result ? 'message' : 'error';
        $this->setRedirect(JoomDOCRoute::viewFileInfo($path), $msg, $type);
    }
}
?>