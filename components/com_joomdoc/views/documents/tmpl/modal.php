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

$mainframe = JFactory::getApplication();
/* @var $mainframe JApplication */

/* @var $this JoomDOCViewDocuments */

$listOrder = $this->escape($this->state->get(JOOMDOC_FILTER_ORDERING));
$listDirn = $this->escape($this->state->get(JOOMDOC_FILTER_DIRECTION));

$useLinkType = JRequest::getInt('useLinkType');
$linkType = JOOMDOC_LINK_TYPE_DETAIL;

$addSymLink = JRequest::getString('addSymLink');
$symLinkSource = JRequest::getString('symLinkSource');

if ($addSymLink == 'target')
	echo '<p>' . JText::sprintf('JOOMDOC_SYMLINKS_ADD_TARGET', '<img src="' . JURI::root() . 'components/com_joomdoc/assets/images/icon-16-symlink.png" alt="" />') . '</p>';
elseif ($addSymLink == 'source')
	echo '<p>' . JText::sprintf('JOOMDOC_SYMLINKS_ADD_SOURCE', '<img src="' . JURI::root() . 'components/com_joomdoc/assets/images/icon-16-symlink.png" alt="" />') . '</p>';

$separator = JText::_('JOOMDOC_PATHWAY_SEPARATOR');
foreach (JoomDOCFileSystem::getPathBreadCrumbs($this->access->relativePath) as $i => $breadCrumb) {
    echo '<span>' . $separator . '<a href="' . JURI::root(true) . JoomDOCRoute::modalDocuments($breadCrumb->path, $useLinkType, $addSymLink, $symLinkSource) . '" class="hasTip" title="' . $this->getTooltip($breadCrumb->path, 'JOOMDOC_DOCUMENTS_OPEN_FOLDER') . '">' . $breadCrumb->name . '</a></span>';
}

echo '<form action="' . JURI::root(true) . JoomDOCRoute::modalDocuments($this->access->relativePath, $useLinkType, $addSymLink, $symLinkSource) . '" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">';
echo '<fieldset class="filter-bar">';
echo '<div>';
echo '<label for="filter" class="hasTip filter-search-lbl" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_FILTER') . '">' . JText::_('JOOMDOC_DOCUMENTS_FILTER') . ':</label>';
echo '<input type="text" name="filter" id="filter" value="' . $this->escape($this->filter) . '" />';
echo '<button type="submit" class="btn">' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';
echo '<button type="button" onclick="var f=this.form;f.filter.value=\'\';f.submit();">' . JText::_('JSEARCH_FILTER_CLEAR') . '</button>';

if ($useLinkType) {
    $linkType = JRequest::getString('linkType', JOOMDOC_LINK_TYPE_DETAIL);
    $options[] = JHtml::_('select.option', JOOMDOC_LINK_TYPE_DETAIL, JText::_('JOOMDOC_LINK_TYPE_DETAIL'));
    $options[] = JHtml::_('select.option', JOOMDOC_LINK_TYPE_DOWNLOAD, JText::_('JOOMDOC_LINK_TYPE_DOWNLOAD'));
    echo JHtml::_('select.genericlist', $options, 'linkType', 'onchange="this.form.submit()"', 'value', 'text', $linkType);
}
echo '</div>';
echo '</fieldset>';
echo '<table class="adminlist table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th style="padding: 5px;">' . JHtml::_('grid.sort', 'JOOMDOC_DOCUMENTS_ITEM', JOOMDOC_ORDER_PATH, $listDirn, $listOrder) . '</th>';
echo '<th style="padding: 5px;">' . JText::_('JOOMDOC_DOCUMENT') . '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$prefix = '/administrator/';
$prefixLength = JString::strlen($prefix);

$this->root->initIteration();
$i = 0;
while ($this->root->hasNext()) {
    $item = $this->root->getNext();
        $access = new JoomDOCAccessHelper($item);
    
    if ($access->docid && $item->document->published == JOOMDOC_STATE_UNPUBLISHED && !$access->canAnyEditOp)
        continue;
    
    echo '<tr class="row' . ($i % 2) . '">';
    echo '<td style="padding: 5px;">';

    // backspashes doing bad in js pass
    $id = str_replace(DIRECTORY_SEPARATOR, '/', $this->escape($access->relativePath)); 
    $title = $this->escape($access->docid ? $item->document->title : str_replace(DIRECTORY_SEPARATOR, '/', $access->relativePath));

    $url = JRoute::_($linkType == JOOMDOC_LINK_TYPE_DOWNLOAD && $access->isFile ? JoomDOCRoute::download($access->relativePath, $access->alias) : JoomDOCRoute::viewDocuments($access->relativePath, $access->alias));

    if (JString::strpos($url, $prefix) === 0) {
        $url = JString::substr($url, $prefixLength);
    }
        	echo '<a href="javascript:window.parent.jSelectJoomdocDocument(\'' . addslashes(JoomDOCString::dsEncode($id)) . '\', \'' . addslashes($title) . '\', \'' . addslashes($url) . '\')" class="hasTip addDocument" title="' . $this->getTooltip('JOOMDOC_SET_DOCUMENT') . '"></a>';
        if ($access->isFolder) {
        echo '<a class="hasTip folder" href="' . JURI::root(true) . JoomDOCRoute::modalDocuments($access->relativePath, $useLinkType, $addSymLink, $symLinkSource) . '" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_DOCUMENTS_OPEN_FOLDER') . '">' . $this->escape($access->name) . '</a>';
    } else {
        echo '<span class="file">' . $this->escape($access->name) . '</span>';
    }
    echo '</td>';
    echo '<td style="padding: 5px;">' . ($access->docid ? $this->escape($item->document->title) : '-') . '</td>';
    echo '</tr>';
    $i++;
}
echo '</tbody>';
echo '</table>';
echo '<input type="hidden" name="task" value="" />';
echo '<input type="hidden" name="boxchecked" value="" />';
echo '<input type="hidden" name="filter_order" value="' . $listOrder . '" />';
echo '<input type="hidden" name="filter_order_Dir" value="' . $listDirn . '" />';
echo JHtml::_('form.token');
echo '</form>';
?>