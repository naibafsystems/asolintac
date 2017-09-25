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

class JoomDOCControllerDocuments extends JoomDOCControllerAdmin {

    public function __construct ($config = array ()) {
        parent::__construct($config);
        $this->registerTask('trash', 'trash');
    }
    /**
     * Create new subfolder in current parent folder.
     *
     * @return void
     */
    public function newFolder () {
        $app = JFactory::getApplication();
        $success = JoomDOCFileSystem::newFolder();
        if ($success && JRequest::getInt('doccreate')) {
            $this->setRedirect(JoomDOCRoute::addDocument($app->getUserState('com_joomdoc.new.folder')));
        } else {
            $this->setRedirect(JoomDOCRoute::viewDocuments());
        }
    }

    /**
     * Delete selected folders/files.
     *
     * @return void
     */
    public function deleteFile () {
        JoomDOCFileSystem::delete();
    }

    /**
     * Upload file from request in current folder.
     *
     * @return void
     */
    public function uploadFile () {
        JoomDOCFileSystem::upload();
    }

    /**
     * Proxy for getModel.
     *
     * @param string $name The name of the model.
     * @param string $prefix The prefix for the PHP class name.
     * @return JModel
     */
    public function getModel ($name = JOOMDOC_DOCUMENT, $prefix = JOOMDOC_MODEL_PREFIX, $config = array ('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }

    public function favorite () {
        $this->setFavorite(JOOMDOC_FAVORITE, 'JOOMDOC_DOCUMENTS_FAVORITE');
    }

    public function unfavorite () {
        $this->setFavorite(JOOMDOC_STANDARD, 'JOOMDOC_DOCUMENTS_UNFAVORITE');
    }

    /**
     * Set documents as favorite/unfavorite and redirect to documents list.
     *
     * @param int    $value use constants JOOMDOC_FAVORITE/JOOMDOC_STANDARD
     * @param string $msg   message on end, in message is by JText::sprintf puted num of affected rows
     * @return void
     */
    public function setFavorite ($value, $msg) {
        if (count(($allow = $this->allowToSetState())))
            JFactory::getApplication()->enqueueMessage(JText::sprintf($msg, $this->getModel(JOOMDOC_DOCUMENTS)->setFavorite($allow, $value)));
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }

    /**
     * Method to save the submitted ordering values for records.
     *
     * @since	1.6
     */
    public function saveorder () {
        // Check for request forgeries.
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the input
        $order = JRequest::getVar('order', null, 'post', 'array');
        $pks = array_keys($order);
        $order = array_merge($order);

        // Sanitize the input
        JArrayHelper::toInteger($pks);
        JArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return === false) {
            // Reorder failed
            $message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
            return false;
        } else {
            // Reorder succeeded.
            $this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
            return true;
        }
    }
    /**
     * Method to publish a list of taxa.
     *
     * @since	1.6
     */
    public function publish () {
        if (count($this->allowToSetState()))
            return parent::publish();
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }

    public function allowToSetState () {
        foreach (JRequest::getVar('cid', array(), '', 'array') as $key => $id) {
            if (!($record = $this->getModel()->getItem($id)))
                continue;
            JoomDOCAccessDocument::editState($id, $record->checked_out) ? $allow[$key] = $id : $notAllow[$key] = $id;
        }
        if (isset($notAllow))
            JFactory::getApplication()->enqueueMessage(JText::sprintf('JOOMDOC_PUBLISH_NOT_ALLOW', implode(', ', $notAllow)), 'notice');
        if (isset($allow)) {
            JRequest::setVar('cid', $allow);
            return $allow;
        }
        return array();
    }
    /**
     * Delete all trashed item's.
     */
    public function trash () {
        $model = $this->getModel();
        /* @var $model JoomDOCModelDocument */
        $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::sprintf('JOOMDOC_TRASH_EMPTY', $model->emptytrash()));
    }

    public function refresh () {
        $this->setRedirect(JoomDOCRoute::viewDocuments(), JText::sprintf('JOOMDOC_REFRESHED', JoomDOCFileSystem::refresh()));
    }
        
    /**
	 * Expand tree node via Ajax.
	 */
    public function updatemootree()
    {
    	$parent = JoomDOCRequest::getPath();
    	JRequest::setVar('ajax', true);
    	JHtml::_('joomdoc.mootree', null, $parent, !JOOMDOC_ISJ3, true);
    }
    
    public function flat() {
    	JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat();
    	$this->setRedirect(JoomDOCRoute::viewDocuments(), JText::_('JOOMDOC_REFLATED'));
    }
}
?>