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

/* @var $this JoomDOCViewLicense */

// test if property realy exist in request
$path = JRequest::getVar('path', false) !== false ? JoomDOCRequest::getPath() : null;

echo '<h1>' . $this->license->title . '</h1>';
if ($path) {
    echo '<h2>' . JText::_('JOOMDOC_MUST_CONFIRM') . '</h2>';
}
echo JoomDOCHelper::applyContentPlugins($this->license->text);
if ($path) {
    echo '<div class="confirm">';
    echo '<input type="checkbox" name="confirm" id="confirm" value="1" onclick="JoomDOC.confirmLicense(this)" />';
    echo '<label for="confirm">' . JText::_('JOOMDOC_CONFIRM') . '</label>';
    echo '<a id="download" class="blind" href="' . JRoute::_(JoomDOCRoute::download($path)) . '" title="">' . JText::_('JOOMDOC_DOWNLOAD_FILE') . '</a>';
    echo '</div>';
}
?>