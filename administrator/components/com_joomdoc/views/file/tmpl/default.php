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

/* @var $this JoomDOCViewFile */

jimport('joomla.html.pagination');
JHtml::_('behavior.modal');

$config = JoomDOCConfig::getInstance();

$showDownload = JoomDOCAccessFileSystem::download($this->document ? $this->document->id : null, $this->filter->path);

// texts translations
$download = JText::_('JOOMDOC_DOWNLOAD_FILE');
$content = JText::_('JOOMDOC_FILE_CONTENT');
$trashed = JText::_('JTRASHED');
//var_dump($this->filter->state);
echo '<form name="adminForm" id="adminForm" action="' . JURI::getInstance()->toString() . '" method="post">';

//Joomla 3.0 display sidemenu manually
if (JOOMDOC_ISJ3){
	
	
	$options = array();
	$options[0] = JText::_('JOOMDOC_FILE_PUBLISHED_TRASHED');
	$options[JOOMDOC_STATE_PUBLISHED] = JText::_('JOOMDOC_FILE_PUBLISHED');
	$options[JOOMDOC_STATE_TRASHED] = JText::_('JOOMDOC_FILE_TRASHED');
	
	JHtmlSidebar::addFilter( JText::_('JOPTION_SELECT_PUBLISHED'), 'state', JHtml::_('select.options', $options, 'value', 'text', $this->filter->state, true));

	echo '<div id="j-sidebar-nav" class="span2">';
	
	//render sidebar
	echo JHtmlSidebar::render();
	
	echo '</div>';
	echo '<div id="joomdoc" class="span10">';
}

    echo '<fieldset class="autoHeight btn-toolbar filter-bar">';
        //echo '<table class="fullWidth">';
           // echo '<tr>';
              //  echo '<td width="1%" nowrap="nowrap" style="padding-right: 5px;">';
              

                    echo '<label class="filter-search-lbl edit element-invisible" for="uploader" style="display:none">' . JText::_('JOOMDOC_UPLOADER') . '</label>'; //J2.5 does niot have element-invisible

                    //  echo '</td>';
              //  echo '</td>';
              //  echo '<td width="1%" nowrap="nowrap">';
                    echo '<div class="btn-group pull-left">';
                    echo '<input style="margin-bottom: 9px;" type="text" name="uploader" id="uploader" placeholder="'.JText::_('JOOMDOC_UPLOADER').'" title="'.JText::_('JOOMDOC_UPLOADER').'" value="' . $this->escape($this->filter->uploader) . '" onchange="this.form.submit()" />';
                    echo '</div>';
                    //  echo '</td>';
                if (!JOOMDOC_ISJ3){ //in J3, displayed on sidemenu
	             //   echo '<td width="1%" nowrap="nowrap" style="padding-bottom: 2px;">';
	                    // file state filter
	                    $options[] = JHtml::_('select.option', 0, JText::_('JOOMDOC_FILE_PUBLISHED_TRASHED'));
	                    $options[] = JHtml::_('select.option', JOOMDOC_STATE_PUBLISHED, JText::_('JOOMDOC_FILE_PUBLISHED'));
	                    $options[] = JHtml::_('select.option', JOOMDOC_STATE_TRASHED, JText::_('JOOMDOC_FILE_TRASHED'));
	                    echo JHtml::_('select.genericlist', $options, 'state', 'onchange="this.form.submit()"', 'value', 'text', $this->filter->state);
	              //  echo '</td>';
                }
                //echo '<td>';
                	echo '<div class="btn-group pull-left">';
                    echo '<button style="margin-bottom: 9px;" type="submit" class="btn">' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';
                    echo '<button style="margin-bottom: 9px;" type="button" class="btn" onclick="this.form.uploader.value=\'\';this.form.submit();">' . JText::_('JSEARCH_FILTER_CLEAR') . '</button>';
                	echo '</div>';
                //echo '</td>';
            //echo '</tr>';
        //echo '</table>';
    echo '</fieldset>';
    
    echo '<table class="adminlist table table-striped">';
        echo '<thead>';
            echo '<tr>';
                if ($this->access->canManageVersions) {
                    echo '<th width="1%" class="center"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>';
                }
                echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_VERSION', 'version', $this->filter->listDirn, $this->filter->listOrder) . '</th>';
                if ($showDownload) {
                    echo '<th>' . JText::_('JOOMDOC_FILE') . '</th>';
                }
                echo '<th>' . JText::_('JOOMDOC_SIZE') . '</th>';
                echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_UPLOADED', 'upload', $this->filter->listDirn, $this->filter->listOrder) . '</th>';
                echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_UPLOADER', 'name', $this->filter->listDirn, $this->filter->listOrder) . '</th>';
                echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_HITS', 'hits', $this->filter->listDirn, $this->filter->listOrder) . '</th>';
            echo '</tr>';
        echo '</thead>';
        
        echo '<tbody>';
            foreach ($this->data as $i => $item) {
                echo '<tr class="row' . ($i % 2) . '">';
                if ($this->access->canManageVersions) {
                    //echo $i.' | '.$item->id.' | '.$item->version.' | '.$this->maxVersion;
                    echo '<td>' . JHtml::_('grid.id', ++$i, $item->id, $item->version == $this->maxVersion) . '</td>';
                }
                echo '<td>' . $item->version . '</td>';
                if ($showDownload) {
                    echo '<td>';
                    if ($item->state == JOOMDOC_STATE_PUBLISHED) {
                        echo '<a href="' . JRoute::_(JoomDOCRoute::download($item->path, null, $item->version)) . '" title="" target="_blank">';
                        echo $download;
                        echo '</a>';
                                            } elseif ($item->state == JOOMDOC_STATE_TRASHED) {
                        echo $trashed;
                    }
                    echo '</td>';
                }
                echo '<td nowrap="nowrap">' . JoomDOCFileSystem::getFileSize(JoomDOCFileSystem::getFullPath($item->path)) . '</td>';
                echo '<td nowrap="nowrap">' . JoomDOCHelper::uploaded($item->upload, false) . '</td>';
                echo '<td nowrap="nowrap">' . $item->name . '</td>';
                echo '<td class="center" nowrap="nowrap">' . JoomDOCHelper::number($item->hits) . '</td>';
                echo '</tr>';
            }
        echo '</tbody>';
        
        echo '<tfoot>';
            echo '<tr>';
                $pagination = new JPagination($this->filter->total, $this->filter->offset, $this->filter->limit);
                echo '<td colspan="20">' . $pagination->getListFooter() . '</td>';
            echo '</tr>';
        echo '</tfoot>';
    echo '</table>';
    
    echo JHtml::_('form.token');
    echo '<input type="hidden" name="task" value="" />';
    echo '<input type="hidden" name="boxchecked" value="" />';
    echo '<input type="hidden" name="filter_order" value="' . $this->filter->listOrder . '" />';
    echo '<input type="hidden" name="filter_order_Dir" value="' . $this->filter->listDirn . '" />';
    
    
echo '</form>';

//css fix. if error messages container is before component code (see isis template), left .span2 have margin-left 15, because it is not first-child
//so move mesages container inside right span10
if (JOOMDOC_ISJ3){
	echo '</div>'; //end #joomdoc div
	echo '<script type="text/javascript">if ($("system-message-container")) $("system-message-container").inject($("joomdoc"), "top")</script>';
}
?>