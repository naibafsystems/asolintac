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

//J3: JUI icons list
//http://jui.kyleledbetter.com/icons

/* @var $this JoomDOCViewDocuments */

JHtml::_('behavior.modal');

// component configuration
$config = JoomDOCConfig::getInstance();
/* @var $config JoomDOCConfig */
$document = JFactory::getDocument();
/* @var $document JDocumentHTML */

// list order criteria from user state
$listOrder = $this->escape($this->state->get(JOOMDOC_FILTER_ORDERING));
$listDirn  = $this->escape($this->state->get(JOOMDOC_FILTER_DIRECTION));

// browse list allow set items ordering
$ordering = $listOrder == JOOMDOC_ORDER_ORDERING;

$taskOrderUp   = JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_ORDERUP);
$taskOrderDown = JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_ORDERDOWN);

if ($config->dropAndDrag) {
    JHtml::_('joomdoc.dropanddrag', $this->access->relativePath);
}

$user = JFactory::getUser();
/* @var $user JUser logged user */

// favorite states
$states[0]['task'] = 'favorite';
$states[1]['task'] = 'unfavorite';

$states[0]['text'] = 'JOOMDOC_SET_FAVORITE';
$states[1]['text'] = 'JOOMDOC_SET_UNFAVORITE';

$states[0]['icon'] = JURI::base(true) . '/images/publish_x.png';
$states[1]['icon'] = JURI::base(true) . '/images/tick.png';

$states[0]['active_title'] = $states[1]['inactive_title'] = 'JOOMDOC_STANDARD_TTL';
$states[1]['active_title'] = $states[0]['inactive_title'] = 'JOOMDOC_FAVORITE_TTL';

$states[0]['active_class'] = $states[0]['inactive_class'] = 'notdefault icon-unfeatured';
$states[1]['active_class'] = $states[1]['inactive_class'] = 'default icon-featured';

$states[0]['tip'] = $states[1]['tip'] = true;

$files = $folders = array();

echo '<form action="' . JRoute::_(JoomDOCRoute::viewDocuments($this->access->relativePath)) . '" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">';

//Joomla 3.0 display sidemenu manually. because we put filters in it.
if (JOOMDOC_ISJ3){
	JHtml::_('formbehavior.chosen', 'select');
	//add state filter to sidebar
	//currently it supports only select filters
	$options = array();
	$options[0] = JText::_('JOOMDOC_FILE_PUBLISHED_TRASHED');
	$options[JOOMDOC_STATE_PUBLISHED] = JText::_('JOOMDOC_FILE_PUBLISHED');
	$options[JOOMDOC_STATE_TRASHED] = JText::_('JOOMDOC_FILE_TRASHED');

	JHtmlSidebar::addFilter( JText::_('JOPTION_SELECT_PUBLISHED'), 'state', JHtml::_('select.options', $options, 'value', 'text', $this->state->get(JOOMDOC_FILTER_STATE), true));
	
	echo '<div id="j-sidebar-nav" class="span2">';
	
	//render sidebar
	echo JHtmlSidebar::render();
	
	//add MooTree to sidebar
	if ($config->useExplorer) {
		echo '<h4 class="page-header">'.str_replace('doc', 'DOC', ucwords(strtolower(JText::_('JOOMDOC_EXPLORER')))).'</h4>'; //in case of no translation..
		echo JHtml::_('joomdoc.mootree');	
	}
	
	echo '</div>';
	echo '<div id="joomdoc" class="span10">';
}
else 
	echo '<div id="joomdoc">';


	//http://twitter.github.io/bootstrap/base-css.html#forms

	echo '<fieldset class="btn-toolbar filter-bar">';
            echo '<fieldset id="filt" class="pull-left" style="margin-bottom:10px">'; //be explicit, J3.0 has margin-bottom 0
                echo '<legend style="font-size: 15.5px;'.(JOOMDOC_ISJ3 ? 'margin-bottom:10px;' : '').'" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_FILTER') . '" class="hasTip" >Filter</legend>';

                echo '<div class="btn-group pull-left input-append">';
                echo '<input style="width: 130px; margin-bottom: 0px;" type="text" name="filter" id="filter" value="' . $this->escape($this->filter) . '" />';
                //echo '</div>';
                
                if (!JOOMDOC_ISJ3){ //in J3, state select is on left sidebar 
	                //echo '<div class="btn-group pull-left">';
	                $options = array();
	                $options[] = JHtml::_('select.option', 0, JText::_('JOOMDOC_FILE_PUBLISHED_TRASHED'));
	                $options[] = JHtml::_('select.option', JOOMDOC_STATE_PUBLISHED, JText::_('JOOMDOC_FILE_PUBLISHED'));
	                $options[] = JHtml::_('select.option', JOOMDOC_STATE_TRASHED, JText::_('JOOMDOC_FILE_TRASHED'));
	                echo JHtml::_('select.genericlist', $options, 'state', 'onchange="this.form.submit()"', 'value', 'text', $this->state->get(JOOMDOC_FILTER_STATE));
                }
                
                //echo '<div class="btn-group pull-left input-prepend">';
                    echo '<button type="submit" class="btn" title="'.JText::_('JSEARCH_FILTER_SUBMIT').'"><i class="icon-search"></i>'.(JOOMDOC_ISJ3 ? '' : JText::_('JSEARCH_FILTER_SUBMIT')).'</button>';
                    echo '<button type="button" class="btn" title="'.JText::_('JSEARCH_FILTER_CLEAR').'" onclick="var f=this.form;f.filter.value=\'\';f.state.value=1;f.submit();"><i class="icon-remove"></i>'.(JOOMDOC_ISJ3 ? '' : JText::_('JSEARCH_FILTER_CLEAR')).'</button>';
                echo '</div>';
                
            echo '</fieldset>';

            echo '<fieldset id="fastTools" class="pull-right form-inline" style="margin-bottom:10px">'; //be explicit, J3.0 margin-bottom 0
                echo '<legend style="font-size: 15.5px;'.(JOOMDOC_ISJ3 ? 'margin-bottom:10px;' : '').'">'.JText::_('JOOMDOC_DOCUMENTS_FAST_TOOLS').'</legend>';

            if ($this->access->canUpload) {

                if (!$config->dropAndDrag) { // single upload
                    echo '<span class="input-prepend input-append">';

                    if (JOOMDOC_ISJ3) {
                        echo '<span class="btn" style="vertical-align: middle; line-height: 18px; height: 22px; padding: 1px 14px 3px;">';
                    }
                
                    echo '<label for="upload" style="margin:0px;position:relative;top:2px;margin-right:5px" class="hasTip" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_UPLOADFILE') . '">' . JText::_('JOOMDOC_DOCUMENTS_UPLOADFILE') . ': </label>';
                	
                    echo '<input type="file" name="upload" id="upload" style="height:auto;line-height:normal" />';
                
                    if (JOOMDOC_ISJ3) {
                        echo '</span>';
                    }

                    echo '<label style="padding-left:14px;padding-right:14px;clear:none;" class="hasTip checkbox add-on" for="iszip" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_UNPACK_ZIP') . '">' ;
                    echo '<input type="checkbox" name="iszip" id="iszip" value="1"  />';
                    echo  JText::_('JOOMDOC_DOCUMENTS_UNPACK_ZIP') ;
                    echo '</label>';
                    
                    echo '<button type="submit" class="btn" onclick="return JoomDOC.upload(this)">' . JText::_('JOOMDOC_UPLOAD') . '</button>';
                
                    echo '</span>';
                } else { // multiple upload
                    echo '<label class="btn button input-xlarge" id="upload">';
                    echo '<span>' . JText::_('JOOMDOC_UPLOAD_DRAG_FILES_HERE') . '</span>';
                    echo '<img id="preloader" src="' . JOOMDOC_IMAGES . 'preloader.gif" />';
                    echo '</label>';
                    echo '<span id="preview"></span>';                            
                }
            }

            if ($this->access->canCreateFolder) {

                $method = 'return JoomDOC.mkdir(this, ' . ($this->access->canCreate ? 'true' : 'false') . ')';

                echo '<span class="input-append" style="margin-left:20px">';
                echo '<input style="width: 130px;'.(JOOMDOC_ISJ3 ? '' : 'margin-left:20px;').'" class="hasTip" type="text" name="newfolder" id="newfolder" placeholder="'. JText::_('JOOMDOC_DOCUMENTS_NEW_FOLDER').'"
                		value="' . $this->escape(JRequest::getString('newfolder')) . '" onchange="' . $method . '"  title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_NEW_FOLDER') . '" />';

                echo '<button type="submit" class="btn  " onclick="' . $method . '">' . JText::_('JOOMDOC_CREATE') . '</button>';
               	echo '</span>';
            }

        echo '</fieldset>';

echo '</fieldset>';
echo '<div class="clearfix"></div>';
echo '<div id="pathway">';

    $separator      = '';
    $separatorValue = JText::_('JOOMDOC_PATHWAY_SEPARATOR');
    $breadCrumbs    = JoomDOCFileSystem::getPathBreadCrumbs($this->access->relativePath);

    foreach ( $breadCrumbs as $breadCrumb ) {
        echo '<span class="item">';
            echo $separator;
            echo '<a href="' . JRoute::_(JoomDOCRoute::viewDocuments($breadCrumb->path)) . '" class="hasTip" title="' . $this->getTooltip($breadCrumb->path, 'JOOMDOC_DOCUMENTS_OPEN_FOLDER') . '">' . $breadCrumb->name . '</a>';
        echo '</span>';
        $separator = $separatorValue;
    }

    $count = count($breadCrumbs);

    if (!$this->access->inRoot && $count > 1) {
        $breadCrumb = $breadCrumbs[$count - 2];
        echo '<a class="back" href="' . JRoute::_(JoomDOCRoute::viewDocuments($breadCrumb->path)) . '" title="">' . JText::_('JOOMDOC_BACK') . '</a>';
    }
echo '</div>';
echo '<div class="clearfix"></div>';

if ($config->useExplorer AND !JOOMDOC_ISJ3) //in J3, its inserted to sidebar (see before)
	echo JHtml::_('joomdoc.mootree', null, '', true); //true: expandable

if ($config->docLayout == 0) {

echo '<table class="adminlist table table-striped" cellspacing="1">';
    echo '<thead>';
        echo '<tr>';
            echo '<th width="1%" class="center"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>';
            echo '<th width="7%">' . JHtml::_('grid.sort', 'JOOMDOC_DOCUMENTS_ITEM', JOOMDOC_ORDER_PATH, $listDirn, $listOrder) . '</th>';
            echo '<th width="5%">Tools</th>';
            	                    echo '<th width="8%">' . JHtml::_('grid.sort', 'JOOMDOC_DOCUMENT', JOOMDOC_ORDER_TITLE, $listDirn, $listOrder) . '</th>';
            echo '<th width="1%">' . JText::_('JOOMDOC_PUBLISHED') . '</th>';
            echo '<th width="1%">' . JText::_('JOOMDOC_FAVORITE') . '</th>';
            echo '<th width="3%">';
            echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', JOOMDOC_ORDER_ORDERING, $listDirn, $listOrder);
            $total = $this->root->getItemsCount();

        if ($this->access->canEditState && $ordering) {
            // create a fake array of browse list items
            for ($i = 0; $i < $total; $i++)
                $fake[] = $i;
            echo JHtml::_('grid.order', isset($fake) ? $fake : array(), 'filesave.png', JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_SAVEORDER));
        }
            echo '</th>';
            echo '<th width="1%">' . JText::_('JOOMDOC_ACCESS') . '</th>';
            //echo '<th width="1%">' . JText::_('JOOMDOC_LICENSE') . '</th>';
            echo '<th width="1%">' . JText::_('JOOMDOC_ID') . '</th>';
            echo '<th width="1%">' . JHtml::_('grid.sort', 'JOOMDOC_HITS', JOOMDOC_ORDER_HITS, $listDirn, $listOrder) . '</th>';
        echo '</tr>';
    echo '</thead>';
echo '<tfoot><tr><td colspan="20">' . $this->pagination->getListFooter() .(JOOMDOC_ISJ3 ? $this->pagination->getLimitBox() : ''). '</td></tr></tfoot>';
echo '<tbody id="documents">';
$this->root->initIteration();
$i = 0;

while ($this->root->hasNext()) {
    // previous item
    $prevItemDocid = JoomDOCHelper::getDocumentID($this->root->getNext(JOOMDOC_ORDER_PREV));
    // next item
    $nextItemDocid = JoomDOCHelper::getDocumentID($this->root->getNext(JOOMDOC_ORDER_NEXT));
    // current item
    $item = $this->root->getNext();
    // access rules
    $access = new JoomDOCAccessHelper($item);
    // save files/folders names for next using
    $access->isFile ? $files[] = $access->name : $folders[] = $access->name;
    
    echo '<tr class="row' . ($i % 2) . '">';
    echo '<td class="center">';

    if ($access->docid && $access->isChecked) {
        echo JHtml::_('jgrid.checkedout', $i, $item->document->editor, $item->document->checked_out_time, 'documents.', (JoomDOCAccessDocument::manage($item->document->checked_out) && JoomDOCAccess::manage()));
    }

    if (!$access->isTrashed) {
        echo '<input type="checkbox" name="paths[]" id="cbb' . $i . '" value="' . $this->escape($access->relativePath) . '" class="blind" />';
    }
    if (!$access->isChecked && !$access->isLocked && !$access->isTrashed) {
        echo '<input type="checkbox" name="cid[]" id="cb' . $i . '" value="' . $access->docid . '" onclick="Joomla.isChecked(this.checked);JoomDOC.check(this,' . $i . ')" />';
    }
    echo '</td>';
    echo '<td class="filepath">';
    
    $name = $access->isTrashed ? JText::sprintf('JOOMDOC_TRASHED_ITEM', $access->name) : $access->name;
    
    $class = JoomDOCHelper::getFileIconClass($access->isFile ? $access->relativePath : 'folder', $config->iconThemeBackend, 16);
    
    
    if ($access->isFolder) {
        $size = $access->isFolder ? '-' : $item->getFileSize();
        $update = isset($item->upload) ? JoomDOCHelper::uploaded($item->upload, false) : '';
        $of = JText::_('JOOMDOC_DOCUMENTS_OPEN_FOLDER');
        if ($access->canEnterFolder && !$access->isTrashed) {
            echo '<a class="hasTip folder '.$class.'" href="' . JRoute::_(JoomDOCRoute::viewDocuments($access->relativePath)) . '" title="' . $this->getTooltip($access->relativePath, $of.'<br />'.$update.'/'.$size) . '">' . $access->name . '</a>';
        } else {
            echo '<a class="folder noLink '.$class.'" href="javascript:void(0)" title="">' . $name . '</a>';
        }
    } else {
        if ($access->canDownload && !$access->isTrashed) {
            $size = $access->isFolder ? '-' : $item->getFileSize();
            $update = isset($item->upload) ? JoomDOCHelper::uploaded($item->upload, false) : '';
            $of = JText::_('JOOMDOC_DOWNLOAD_FILE');     
            echo '<a href="' . JRoute::_(JoomDOCRoute::download($access->relativePath)) . '" class="hasTip file '.$class.'" title="' . $this->getTooltip($access->relativePath, $of.'<br />'.$update.'/'.$size) . '">' . $access->name . '</a>';
        } else {
            echo '<a href="javascript:void(0)" class="file noLink '.$class.'" title="">' . $name . '</a>';
        }
    }
    if ($access->canRename && !$access->isTrashed) {
        echo '<div class="rename blind" style="width: 305px; padding: 5px 5px 0px;">';
        
        echo '<div class="btn-group pull-left">';
        echo '<input class="pull-left" style="position: relative; width: 165px;" type="text" name="rename" value="' . $this->escape($access->name) . '" />';
        echo '</div>';
        
        echo '<div class="btn-group pull-left">';
        echo '<button class="btn" style="position: relative;" onclick="return JoomDOC.rename(this, \'' . addslashes(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_RENAME)) . '\', \'' . addslashes($access->name) . '\', \'' . addslashes($access->relativePath) . '\',\'' . JText::_('JOOMDOC_RENAME_SAME_NAME', true) . '\',\'' . JText::_('JOOMDOC_RENAME_EMPTY_NAME', true) . '\', \'' . JText::_('JOOMDOC_RENAME_FILE_EXISTS', true) . '\', \'' . JText::_('JOOMDOC_RENAME_DIR_EXISTS', true) . '\')">' . JText::_('JOOMDOC_RENAME_SAVE') . '</button>';
        echo '<button class="btn" style="position: relative;" onclick="return JoomDOC.closeRename(this, \'' . addslashes($access->name) . '\')">' . JText::_('JOOMDOC_RENAME_CLOSE') . '</button>';
        echo '</div>';
        
        echo '</div>';
    }
    
    if ($access->isTrashed && $access->canUntrash) {
        echo '<a href="' . JRoute::_(JoomDOCRoute::untrash($access->relativePath)) . '" class="untrash hasTip" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_UNTRASH_FILE') . '"></a>';
    }
        echo '</td>';

    echo '<td class="rename">';
    echo '<div class="btn-group pull-left">';
        if ($access->canRename && !$access->isTrashed) {
        	
        	if (JOOMDOC_ISJ3) //jui button
        		echo '<a href="javascript:void(0)" class="btn btn-smaller hasTip" id="openRename' . $i . '" onclick="JoomDOC.openRename(' . $i . ')" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_RENAME') . '" style="margin-left: 4px;"><i class="icon-pencil-2"> </i></a>';
        	else
           		echo '<a href="javascript:void(0)" class="rename hasTip" id="openRename' . $i . '" onclick="JoomDOC.openRename(' . $i . ')" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_RENAME') . '"></a>';
        }

            echo '</div>';
    echo '</td>';
            if ($access->docid && !$access->isTrashed) {

        echo '<td>';

        if ($access->canEdit) {
            echo '<a href="' . JRoute::_(JoomDOCRoute::editDocument($access->docid)) . '" title="' . $this->getTooltip($item->document->title, 'JOOMDOC_EDIT_DOCUMENT') . '" class="hasTip">' . $this->escape($item->document->title) . '</a>';
        } else {
            echo $this->escape($item->document->title);
        }

        echo '</td>';

        echo '<td class="center" align="center">';

        echo JHtml::_('jgrid.published', $item->document->state, $i, 'documents.', $access->canEditState, 'cb', $item->document->publish_up, $item->document->publish_down);
        echo '</td>';

        echo '<td class="center" align="center">';
        echo JHtml::_('jgrid.state', $states, $item->document->favorite, $i, 'documents.', $access->canEditState, true);
        echo '</td>';

        echo '<td class="order">';
        if ($access->canEditState) {
            if ($ordering) {
                echo '<div class="pull-right">';
                if ($listDirn == 'asc') {
                    echo '<span>' . $this->pagination->orderUpIcon($i, $prevItemDocid, $taskOrderUp, 'JLIB_HTML_MOVE_UP', $ordering) . '</span>';
                    echo '<br>';
                    echo '<span>' . $this->pagination->orderDownIcon($i, $this->pagination->total, $nextItemDocid, $taskOrderDown, 'JLIB_HTML_MOVE_DOWN', $ordering) . '</span>';
                } elseif ($listDirn == 'desc') {
                    echo '<span>' . $this->pagination->orderUpIcon($i, $prevItemDocid, $taskOrderDown, 'JLIB_HTML_MOVE_UP', $ordering) . '</span>';
                    echo '<br>';
                    echo '<span>' . $this->pagination->orderDownIcon($i, $this->pagination->total, $nextItemDocid, $taskOrderUp, 'JLIB_HTML_MOVE_DOWN', $ordering) . '</span>';
                }
                echo '</div>';
            }
            echo '<input type="text" name="order[' . $access->docid . ']" size="5" value="' . $item->document->ordering . '" ' . (!$ordering ? 'disabled="disabled"' : '') . ' class="text-area-order input-mini" />';
        } else {
            echo $item->document->ordering;
        }
        echo '</td>';

        echo '<td class="center">' . $this->escape($item->document->access_title) . '</td>';
       
        echo '<td class="center">' . JoomDOCHelper::number($item->document->id) . '</td>';

    } else {
        echo '<td colspan="6">';
        if ($access->canCreate && !$access->isTrashed) {
        	
        	if (JOOMDOC_ISJ3) //jui button
        		echo '<a href="' . JRoute::_(JoomDOCRoute::addDocument($access->relativePath)) . '" class="hasTip " style="display:block; width: 100%; text-align: center;" title="' . $this->getTooltip('JOOMDOC_ADD_DOCUMENT') . '">
        				<span class="btn btn-smaller" style="float:left"><i class="icon-plus-2"></i></span>
        				' . JText::_('JOOMDOC_NO_METADATA') . '</a>';
        	else
           		echo '<a href="' . JRoute::_(JoomDOCRoute::addDocument($access->relativePath)) . '" class="hasTip addDocument" style="padding-left: 23px; padding-right: 0px; width: 100%; text-align: center;" title="' . $this->getTooltip('JOOMDOC_ADD_DOCUMENT') . '">' . JText::_('JOOMDOC_NO_METADATA') . '</a>';
        }
        echo '</td>';
    }
    echo '<td class="center">' . JoomDOCHelper::number($item->hits) . '</td>';
    echo '</tr>';
    $i++;
}

if (empty($files) && empty($folders)) {
    
    echo '<tr><td colspan="20">' . JText::_('JOOMDOC_EMPTY_FOLDER') . '</td></tr>';
    
}

echo '</tbody>';
echo '</table>';
} elseif ( $config->docLayout == 1 ) { // Simple list layout.
    
    
    
} elseif ( $config->docLayout == 2 ) { // Icon layout
    
    $this->root->initIteration();
    $i = 0;

    while ($this->root->hasNext()) {
        // previous item
        $prevItemDocid = JoomDOCHelper::getDocumentID($this->root->getNext(JOOMDOC_ORDER_PREV));
        // next item
        $nextItemDocid = JoomDOCHelper::getDocumentID($this->root->getNext(JOOMDOC_ORDER_NEXT));
        // current item
        $item = $this->root->getNext();
        //var_dump($item);
        // access rules
        $access = new JoomDOCAccessHelper($item);
        // save files/folders names for next using
        $access->isFile ? $files[] = $access->name : $folders[] = $access->name;

        echo '<div class="object" style="float: left; width: 140px; height: 110px; margin-right: 10px;">';   
        
        if ($access->docid && $access->isChecked) {
            echo JHtml::_('jgrid.checkedout', $i, $item->document->editor, $item->document->checked_out_time, 'documents.', (JoomDOCAccessDocument::manage($item->document->checked_out) && JoomDOCAccess::manage()));
        }

        if (!$access->isTrashed) {
            echo '<input type="checkbox" name="paths[]" id="cbb' . $i . '" value="' . $this->escape($access->relativePath) . '" class="blind" />';
        }
        
        if (!$access->isChecked && !$access->isLocked && !$access->isTrashed) {
            echo '<input type="checkbox" name="cid[]" id="cb' . $i . '" value="' . $access->docid . '" onclick="Joomla.isChecked(this.checked);JoomDOC.check(this,' . $i . ')" />';
        }
        
        /*if ($access->canRename && !$access->isTrashed) {
            echo '<a href="javascript:void(0)" class="rename hasTip" id="openRename' . $i . '" onclick="JoomDOC.openRename(' . $i . ')" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_RENAME') . '"></a>';
        }*/
        
        echo '<script type="text/javascript">
        		
        		 $$(".dblc").addEvent("click", function() { return false;}).addEvent("dblclick", function() { 
        			window.location = this.href;
                    return false;})

        		 $("MooTree").setStyle("margin-right", "30px");

             </script>';
        
        $name = $access->isTrashed ? JText::sprintf('JOOMDOC_TRASHED_ITEM', $access->name) : $access->name;
        if ($access->isFolder) {
            $size = $access->isFolder ? '-' : $item->getFileSize();
            $update = isset($item->upload) ? JoomDOCHelper::uploaded($item->upload, false) : '';
            $of = JText::_('JOOMDOC_DOCUMENTS_OPEN_FOLDER');
            if ($access->canEnterFolder && !$access->isTrashed) {
                echo '<a onclick="JoomDOC.checkCheckBox(cb' . $i . ');JoomDOC.check(cb' . $i . ',' . $i . ');" style="display: block; width: 140px; height: 90px; text-align: center;" class="hasTip folder-big dblc" href="' . JRoute::_(JoomDOCRoute::viewDocuments($access->relativePath)) . '" title="' . $this->getTooltip($access->relativePath, $of.'<br />'.$update.'/'.$size) . '"><span style="float: left; clear: both; display: inline-block; margin-top: 53px; width: 100%;">' . $access->name . '</span></a>';
            } else {
                echo '<a class="folder noLink" href="javascript:void(0)" title="">' . $name . '</a>';
            }
        } else {
            if ($access->canDownload && !$access->isTrashed) {
                $size = $access->isFolder ? '-' : $item->getFileSize();
                $update = isset($item->upload) ? JoomDOCHelper::uploaded($item->upload, false) : '';
                $of = JText::_('JOOMDOC_DOWNLOAD_FILE');     
                echo '<a onclick="JoomDOC.checkCheckBox(cb' . $i . ');JoomDOC.check(cb' . $i . ',' . $i . ');" style="display: block; width: 140px; height: 90px; padding: 0px; text-align: center;" href="' . JRoute::_(JoomDOCRoute::download($access->relativePath)) . '" class="hasTip dblc file ico-big-'.$access->fileType.'" title="' . $this->getTooltip($access->relativePath, $of.'<br />'.$update.'/'.$size) . '"><span style="float: left; clear: both; display: inline-block; margin-top: 53px; width: 100%;">' . $access->name . '</span></a>';
            } else {
                echo '<a href="javascript:void(0)" class="file noLink" title="">' . $name . '</a>';
            }
        }
        /*if ($access->canRename && !$access->isTrashed) {
            echo '<div class="rename blind" style="width: 302px; padding: 5px 5px 0px; background: none repeat scroll 0px 0px rgb(255, 255, 255);">';
            echo '<input class="pull-left" style="position: relative; width: 165px;" type="text" name="rename" value="' . $this->escape($access->name) . '" />';
            //echo '<div class="btn-group pull-left">';
            echo '<button class="btn" style="position: relative;" onclick="return JoomDOC.rename(this, \'' . addslashes(JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_RENAME)) . '\', \'' . addslashes($access->name) . '\', \'' . addslashes($access->relativePath) . '\',\'' . JText::_('JOOMDOC_RENAME_SAME_NAME', true) . '\',\'' . JText::_('JOOMDOC_RENAME_EMPTY_NAME', true) . '\', \'' . JText::_('JOOMDOC_RENAME_FILE_EXISTS', true) . '\', \'' . JText::_('JOOMDOC_RENAME_DIR_EXISTS', true) . '\')">' . JText::_('JOOMDOC_RENAME_SAVE') . '</button>';
            echo '<button class="btn" style="position: relative;" onclick="return JoomDOC.closeRename(this, \'' . addslashes($access->name) . '\')">' . JText::_('JOOMDOC_RENAME_CLOSE') . '</button>';
            //echo '</div>';
            echo '</div>';
        }*/

        /*if ($access->isTrashed && $access->canUntrash) {

            echo '<a href="' . JRoute::_(JoomDOCRoute::untrash($access->relativePath)) . '" class="untrash hasTip" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_UNTRASH_FILE') . '"></a>';

        }

        if ($item->isSymLink) {

            echo '<span class="hasTip symlink" title="' . $this->escape(JText::_('JOOMDOC_SYMLINK')) . '::' . $this->escape($access->relativePath) . '">' . $access->relativePath . '</span>';

        }

        
        if ($access->canRename && !$access->isTrashed) {
            echo '<a href="javascript:void(0)" class="rename hasTip" id="openRename' . $i . '" onclick="JoomDOC.openRename(' . $i . ')" title="' . $this->getTooltip($access->relativePath, 'JOOMDOC_RENAME') . '"></a>';
        }

        echo '<a href="#modal-symlink-'.$i.'" title="Add symlink." role="button" class="btn hasTip" data-toggle="modal" style="margin-left: 4px;height: 16px; width: 20px; padding: 2px;"><i class="icon-out-2"> </i></a>';
        if ($item->isSymLink) {
            echo '<a onclick="JoomDOC.checkCheckBox(cb' . $i . ');JoomDOC.check(cb' . $i . ',' . $i . ');Joomla.submitbutton(\'symlinks.delete\');" href="#" title="Delete symlink." role="button" class="btn hasTip" data-toggle="modal" style="margin-left: 4px;height: 16px; width: 20px; padding: 2px;"><i class="icon-delete"> </i></a>';
        }

        echo '<div class="modal hide fade" id="modal-symlink-'.$i.'">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3>Add symlink</h3>
                </div>
                <div id="modal-symlink-container-'.$i.'"></div>
              </div>
              <script>
                jQuery(\'#modal-symlink-'.$i.'\').on(\'show\', function () {

                    document.getElementById(\'modal-symlink-container-'.$i.'\').innerHTML = \'<div class="modal-body"><iframe class="iframe" src="'.JUri::root().'administrator/index.php?option=com_joomdoc&view=documents&layout=modal&tmpl=component&addSymLink=source&symLinkSource='.$this->escape($access->relativePath).'" height="480" width="640"></iframe></div>\';

                });
              </script>';*/
        
        echo '</div>';
        $i++;
    }
    
}
echo '<input type="hidden" name="task" value="" />';
echo '<input type="hidden" name="boxchecked" value="" />';
echo '<input type="hidden" name="filter_order" value="' . $listOrder . '" />';
echo '<input type="hidden" name="filter_order_Dir" value="' . $listDirn . '" />';
echo '<input type="hidden" name="renamePath" value="" />';
echo '<input type="hidden" name="newName" value="" />';
echo '<input type="hidden" name="doccreate" value="0" />';
echo JHtml::_('form.token');
echo '</form>';
echo '</div>';

//css fix. if error messages container is before component code (see isis template), left .span2 have margin-left 15, because it is not first-child
//so move mesages container inside right span10
if (JOOMDOC_ISJ3)
	echo '<script type="text/javascript">if ($("system-message-container")) $("system-message-container").inject($("joomdoc"), "top")</script>';
	
JoomDOCHelper::jsArray('joomDOCFiles', $files);
JoomDOCHelper::jsArray('joomDOCFolders', $folders);
if ($this->access->canWebDav) {
    JoomDOCWebDav::add();
}