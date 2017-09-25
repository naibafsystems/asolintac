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

jimport('joomla.application.component.controlleradmin');

class JoomdocControllerFields extends JControllerAdmin
{

	public function getModel($name = JOOMDOC_FIELD, $prefix = JOOMDOC_MODEL_PREFIX, $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}