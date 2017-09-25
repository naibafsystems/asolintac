<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/* @var $this JModuleHelper */

defined('_JEXEC') or die;

echo '<div id="joomdocExplorer" ' . ($moduleConfig->moduleclass_sfx ? 'class="' . htmlspecialchars($moduleConfig->moduleclass_sfx, ENT_QUOTES) . '"' : '') . '>';
$folders = JHtml::_('joomdoc.folders', $root, $parent);
echo JHtml::_('joomdoc.mootree', $folders, JoomDOCFileSystem::getRelativePath($parent));
echo '</div>';
?>