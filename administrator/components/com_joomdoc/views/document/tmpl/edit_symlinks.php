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

echo JHtmlJoomDOC::addTab('JOOMDOC_SYMLINKS', 'symlinks', 'tabone');
if ($this->document->id) {
    echo '<iframe src="' . JoomDOCRoute::viewSymlinks($this->document->path) . '" width="100%" height="600px" frameborder="0"></iframe>';
} else {
    echo JText::_('JOOMDOC_SYMLINKS_APPLY');
}
echo JHtmlJoomDOC::endTab();
echo JHtmlJoomDOC::endTabs();

