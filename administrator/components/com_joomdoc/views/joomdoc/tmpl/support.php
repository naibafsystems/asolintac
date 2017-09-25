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

/* @var $this JoomDOCViewJoomDOC */

JToolBarHelper::title(JText::_('JOOMDOC_SUPPORT'), 'help_header');

echo '<h2>' . JText::_('JOOMDOC_SUPPORT') . '</h2>';

echo '<div id="cpanel">';
echo '<div class="icon-wrapper">';

echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_OFFICIAL_PAGE') . '">';
echo '<a href="http://www.artio.net/joomla-extensions/document-management" target="_blank" title="" >';
echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-joomdoc.png" alt="" />';
echo '<span>' . JText::_('JOOMDOC_OFFICIAL_PAGE') . '</span>';
echo '</a>';
echo '</div>';

echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_FORUM') . '">';
echo '<a href="http://www.artio.net/support-forums/joomdoc-docman-2" target="_blank" title="" >';
echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-help-forum.png" alt="" />';
echo '<span>' . JText::_('JOOMDOC_FORUM') . '</span>';
echo '</a>';
echo '</div>';

echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_PAID_SUPPORT') . '">';
echo '<a href="http://www.artio.net/en/e-shop/support-services" target="_blank" title="" >';
echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-help-this.png" alt="" />';
echo '<span>' . JText::_('JOOMDOC_PAID_SUPPORT') . '</span>';
echo '</a>';
echo '</div>';

echo '</div>';
echo '<div class="clr"></div>';
echo '</div>';
?>