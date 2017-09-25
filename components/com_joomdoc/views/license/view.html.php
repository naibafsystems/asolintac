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

class JoomDOCViewLicense extends JoomDOCView {
    /**
     * Selected License full Object.
     *
     * @var JObject
     */
    protected $license;
    /**
     * License view page.
     *
     * @param string $tpl used template name
     * @return void
     */
    public function display ($tpl = null) {
        $model = $this->getModel();
        /* @var $model JoomDOCModelLicense */

        $id = JRequest::getInt('id');

        $this->license = $model->getItem($id);

        if (!$this->license) {
            JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        if ($this->license->state != JOOMDOC_STATE_PUBLISHED) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        
        parent::display($tpl);
    }
}
?>