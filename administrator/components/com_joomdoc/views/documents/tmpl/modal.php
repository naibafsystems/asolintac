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
$listOrder     = $this->escape($this->state->get(JOOMDOC_FILTER_ORDERING));
$listDirn      = $this->escape($this->state->get(JOOMDOC_FILTER_DIRECTION));
$useLinkType   = JRequest::getInt('useLinkType');
$linkType      = JOOMDOC_LINK_TYPE_DETAIL;
$addSymLink    = JRequest::getString('addSymLink');
$symLinkSource = JRequest::getString('symLinkSource');
$separator = JText::_('JOOMDOC_PATHWAY_SEPARATOR');

foreach (JoomDOCFileSystem::getPathBreadCrumbs($this->access->relativePath) as $i => $breadCrumb) {
    
    echo '<span>' . $separator . '<a href="' . JRoute::_(JoomDOCRoute::modalDocuments($breadCrumb->path, $useLinkType, $addSymLink, $symLinkSource)) . '" class="hasTip" title="' . $this->getTooltip($breadCrumb->path, 'JOOMDOC_DOCUMENTS_OPEN_FOLDER') . '">' . $breadCrumb->name . '</a></span>';
    
}

echo '<form action="' . JRoute::_(JoomDOCRoute::modalDocuments($this->access->relativePath, $useLinkType, $addSymLink, $symLinkSource)) . '" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">';
    echo '<fieldset style="height: 65px;" class="filter-bar">';
        echo '<div>';
            
      		echo '<label for="filter" class="hasTip filter-search-lbl" style="display:none" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_FILTER') . '">' . JText::_('JOOMDOC_DOCUMENTS_FILTER') . ':</label>';

            echo '<div class="btn-group pull-left">';
            echo '<input type="text" name="filter" id="filter" value="' . $this->escape($this->filter) . '" 
            		 title="' . JText::_('JOOMDOC_DOCUMENTS_FILTER') . '"  placeholder="' . JText::_('JOOMDOC_DOCUMENTS_FILTER') . '"/>';
            echo '</div>';
                       
            echo '<div class="btn-group pull-left">';
            echo '<button type="submit" class="btn">' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';
            echo '<button class="btn" type="button" onclick="var f=this.form;f.filter.value=\'\';f.submit();">' . JText::_('JSEARCH_FILTER_CLEAR') . '</button>';
            echo '</div>';
            
            if ( $useLinkType ) {
                
                $linkType  = JRequest::getString('linkType', JOOMDOC_LINK_TYPE_DETAIL);
                $options[] = JHtml::_('select.option', JOOMDOC_LINK_TYPE_DETAIL, JText::_('JOOMDOC_LINK_TYPE_DETAIL'));
                $options[] = JHtml::_('select.option', JOOMDOC_LINK_TYPE_DOWNLOAD, JText::_('JOOMDOC_LINK_TYPE_DOWNLOAD'));
                echo JHtml::_('select.genericlist', $options, 'linkType', 'onchange="this.form.submit()"', 'value', 'text', $linkType);
                
            }
        echo '</div>';
    echo '</fieldset>';
    
    echo '<table class="adminlist table table-striped">';
        echo '<thead>';
            echo '<tr>';
                echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_DOCUMENTS_ITEM', JOOMDOC_ORDER_PATH, $listDirn, $listOrder) . '</th>';
                echo '<th>' . JText::_('JOOMDOC_DOCUMENT') . '</th>';
            echo '</tr>';
        echo '</thead>';
        echo '<tfoot><tr><td colspan="20">' . $this->pagination->getListFooter() . '</td></tr></tfoot>';
        echo '<tbody>';

            $prefix       = JURI::base(true).'/'; // should remove path, not only '/administrator/'
            $prefixLength = JString::strlen($prefix);

            $this->root->initIteration();
            $i = 0;

            while ($this->root->hasNext()) {
                $item = $this->root->getNext();
                                $access = new JoomDOCAccessHelper($item);
                echo '<tr class="row' . ($i % 2) . '">';
                echo '<td>';

                // backspashes doing bad in js pass
                $id = str_replace(DIRECTORY_SEPARATOR, '/', $this->escape($access->relativePath)); 
                $title = $this->escape($access->docid ? $item->document->title :  str_replace(DIRECTORY_SEPARATOR, '/', $access->relativePath));

                $url = JRoute::_($linkType == JOOMDOC_LINK_TYPE_DOWNLOAD && $access->isFile ? JoomDOCRoute::download(str_replace(DIRECTORY_SEPARATOR, '/', $access->relativePath), $access->alias) : JoomDOCRoute::viewDocuments(str_replace(DIRECTORY_SEPARATOR, '/', $access->relativePath), $access->alias));

                if (JString::strpos($url, $prefix) === 0) {
                    $url = JString::substr($url, $prefixLength);
                }
                			    	echo '<a href="javascript:window.parent.jSelectJoomdocDocument(\'' . addslashes(JoomDOCString::dsEncode($id)) . '\', \'' . addslashes($title) . '\', \'' . addslashes($url) . '\')" class="hasTip addDocument" title="' . $this->getTooltip('JOOMDOC_SET_DOCUMENT') . '"></a>';
                                
                if ($access->isFolder) {
                    echo '<a class="hasTip folder" href="' . JRoute::_(JoomDOCRoute::modalDocuments($access->relativePath, $useLinkType, $addSymLink, $symLinkSource)) . '" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_DOCUMENTS_OPEN_FOLDER') . '">' . $this->escape($access->name) . '</a>';
                } else {
                    echo '<span class="file">' . $this->escape($access->name) . '</span>';
                }
                echo '</td>';
                echo '<td>' . ($access->docid ? $this->escape($item->document->title) : '-') . '</td>';
                echo '</tr>';
                $i++;
            }

            if ($i === 0) { echo '<tr><td>This folder contains no items.</td></tr>'; } else { echo ''; }
        
        echo '</tbody>';
    echo '</table>';
    
    echo '<input type="hidden" name="task" value="" />';
    echo '<input type="hidden" name="boxchecked" value="" />';
    echo '<input type="hidden" name="filter_order" value="' . $listOrder . '" />';
    echo '<input type="hidden" name="filter_order_Dir" value="' . $listDirn . '" />';
    echo JHtml::_('form.token');
echo '</form>';
?>