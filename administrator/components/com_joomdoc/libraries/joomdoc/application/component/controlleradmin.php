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

jimport('joomla.application.component.controlleradmin');
jimport('joomla.utilities.arrayhelper');

class JoomDOCControllerAdmin extends JControllerAdmin {
    /**
     * Delete document's (move to trash).
     * Method expected array cid in request with document's id's.
     * On end redirect to document's list view.
     *
     * @return void
     */
    public function delete () {
    	//parent::delete(); why it was here?
        JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $cid = JRequest::getVar('cid', array(), '', 'array');
        if (!is_array($cid) || !count($cid)) {
            JError::raiseWarning(500, JText::_('COM_JOOMDOC_ERROR_NO_ITEMS_SELECTED'));
        } else {
            $model = $this->getModel();
            /* @var $model JoomDOCModelDocument */
            JArrayHelper::toInteger($cid);
            foreach ($cid as $id) {
                JoomDOCAccessDocument::delete($id) ? $canDelete[] = $id : $cannotDelete[] = $id;
            }
            if (isset($cannotDelete)) {
                $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_UNABLE_DELETE_DOCUMENTS', implode(',', $cannotDelete)), 'error');
            }
            if (isset($canDelete)) {
                $mainframe->enqueueMessage(JText::sprintf('COM_JOOMDOC_N_ITEMS_DELETED', $model->trash($canDelete)));
            }
        }
        $this->setRedirect(JoomDOCRoute::viewDocuments());
    }
}
?>