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

class JoomDOCViewDocument extends JoomDOCView {
    /**
     * @var JForm
     */
    protected $form;
    /**
     * @var JObject
     */
    public $document;//protected $document;
    /**
     * @var JObject
     */
    protected $state;
    /**
     * @var JoomDOCAccessHelper
     */
    protected $access;
    /**
     * Document edit page.
     *
     * @param string $tpl used template name
     * @return void
     */
    public function display ($tpl = null) {
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JAdministrator */
        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */
        $model = $this->getModel();
        /* @var $model JoomDOCModelDocument */

        $this->form = $model->getForm();
        $this->document = $model->getItem();
        $this->state = $model->getState();
        $this->access = new JoomDOCAccessHelper($this->document);

        if (empty($this->access->docid)) {
            // new document
            $this->form->setValue('path', null, $mainframe->getUserState('path'));
            $this->form->setValue('title', null, JFile::getName($mainframe->getUserState('path')));
        }

        parent::display($tpl);
    }
    
}
?>
