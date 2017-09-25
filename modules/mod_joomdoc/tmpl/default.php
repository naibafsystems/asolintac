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

echo '<div id="joomdocModule" ' . ($moduleConfig->moduleclass_sfx ? 'class="' . htmlspecialchars($moduleConfig->moduleclass_sfx, ENT_QUOTES) . '"' : '') . '><ul>';
$root->initIteration();
while ($root->hasNext()) {
    $item = $root->getNext();
    $access = new JoomDOCAccessHelper($item);
    if ($access->docid && $item->document->published == JOOMDOC_STATE_UNPUBLISHED) {
    	// Document is unpublished
        continue;
    }
    $class = '';
    if ($moduleConfig->show_icons) {
    	// File mime type icon.
        $class = JoomDOCHelper::getFileIconClass($access->isFile ? $access->relativePath : 'folder', null, 16);
    }
    echo '<li' . ($class ? ' class="icon ' . $class . '"' : '') . '>';
    if ($moduleConfig->link_type == 'detail') {
        $url = JoomDOCRoute::viewDocuments($access->relativePath, $access->alias);
    } elseif ($moduleConfig->link_type == 'download' && $access->canDownload) {
    	// download link is displayed only if file can be download in ACL setting
        $url = JoomDOCRoute::download($access->relativePath, $access->alias);
    } else {
        $url = null;
    }
    if ($url) {
        echo '<a href="' . JRoute::_($url) . '" title="">' . ($access->docid ? $item->document->title : $item->getFileName()) . '</a>';
    } else {
    	// Name is displayed as document title or file path.
        echo $access->docid ? $item->document->title : $item->getFileName();
    }
    if ($moduleConfig->show_filesize) {
        echo '<strong>' . JText::sprintf('JOOMDOC_MODULE_FILESIZE', JoomDOCFileSystem::getFileSize($access->absolutePath)) . '</strong>';
    }
    if ($moduleConfig->show_listfields) {
    	echo '<table class="fields"><tbody>';
    	foreach ($listFields as $field)
    		if ($value = JHtml::_('joomdoc.showfield', $field, $item->document))
    			echo '<tr><th>' . $field->title . ':</th><td>' . $value . '</td></tr>';
    	echo '</tbody></table>';
    }
    if ($moduleConfig->show_text && $access->docid && ($description = JString::trim($item->document->description))) {
        echo '<p>' . JoomDOCString::crop($description, $moduleConfig->crop_length) . '</p>';
    }
    echo '</li>';
}
echo '</ul></div>';
?>