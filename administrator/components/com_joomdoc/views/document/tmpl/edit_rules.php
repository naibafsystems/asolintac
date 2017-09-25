<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/* @var $this JoomDOCViewDocument */

echo JHtmlJoomDOC::addTab('JOOMDOC_FIELDSET_RULES', 'rules', 'tabone');

echo '<fieldset class="panelform">';
echo $this->form->getLabel('rules');
echo $this->form->getInput('rules');
echo '</fieldset>';

echo JHtmlJoomDOC::endTab();
