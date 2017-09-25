<?php

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author   	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class JoomdocControllerField extends JControllerForm {

    public function suggest() {
        $term = JString::strtolower(JString::trim(JRequest::getString('term')));
        $field = JRequest::getInt('field');
        $model = $this->getModel('field');
        $suggestions = $model->getSuggest($term, $field);
        die(json_encode($suggestions));
    }

}
