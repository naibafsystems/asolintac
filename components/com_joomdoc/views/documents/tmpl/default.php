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

/* @var $this JoomDOCViewDocuments */

JHtml::_('behavior.modal');
if (JOOMDOC_ISJ3)
	JHtml::_('formbehavior.chosen', 'select');

$config = JoomDOCConfig::getInstance();

if ($config->dropAndDrag) {
    JHtml::_('joomdoc.dropanddrag', $this->access->relativePath);
}


echo '<form action="' . JRoute::_(JoomDOCRoute::viewDocuments($this->access->relativePath, $this->access->alias)) . '" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">';
echo '<div id="documents" class="' . $this->pageclassSfx . '">';

// if path is not root display back link
if (!$this->access->inRoot && $this->access->relativePath) {
    $parentAlias = $this->root->parent ? $this->root->parent->full_alias : null;
    echo '<a class="back" href="' . JRoute::_(JoomDOCRoute::viewDocuments(JoomDOCFileSystem::getParentPath($this->access->relativePath), $parentAlias)) . '" title="">' . JText::_('JOOMDOC_BACK') . '</a>';
}

if ($this->pageHeading) {
    // default page heading of menu item
    echo '<h1>' . $this->pageHeading . '</h1>';
} elseif ($this->access->inRoot && !$this->access->docid && $config->defaultTitle) {
    // in root display default title if root hasn't document
    echo '<h1>' . $config->defaultTitle . '</h1>';
} else {
    // otherwise display document title or path as title. display title only if is published. but owner will see it always.
    echo '<h1>' . (($this->access->docid AND $this->root->document->published==JOOMDOC_STATE_PUBLISHED) ? $this->root->document->title : $this->access->relativePath) . '</h1>';
}

if ($this->access->inRoot && !$this->access->docid && $config->defaultDescription) {
    // in root display default description if root hasn't document
    echo $config->defaultDescription;
} elseif (!empty($this->root->document->description) && ($description = JString::trim($this->root->document->description))) {
    // otherwise display document description if available
    echo $description;
}

if ($config->useSearch) {
    echo '<div id="search"><h2>' . JText::_('JOOMDOC_SEARCH') . '</h2>';
    // tools to unpack/pack search form
    echo '<a href="javascript:JoomDOC.setSearchTools(\'1\')" id="openSearch" class="openSearch" title="">' . JText::_('JOOMDOC_SEARCH_OPEN') . '</a>';
    echo '<a href="javascript:JoomDOC.setSearchTools(\'0\')" id="closeSearch" class="closeSearch" title="">' . JText::_('JOOMDOC_SEARCH_CLOSE') . '</a>';
    echo '<table id="searchBox">';
    if ($config->searchKeyword == 1) { 
        // keywords field
        echo '<tr><td><label for="joomdoc_keywords">' . JText::_('JOOMDOC_SEARCH_KEYWORDS') . '</label></td>';
        echo '<td><input type="text" name="joomdoc_keywords" id="joomdoc_keywords" value="' . $this->escape($this->search->keywords) . '" /></td></tr>';    
    } elseif ($config->searchKeyword == 2) { 
        if ($config->searchShowTitle) {
            echo '<tr><td><label for="joomdoc_keywords_title">' . JText::_('JOOMDOC_SEARCH_AREA_TITLE') . '</label></td>';
            echo '<td><input type="text" name="joomdoc_keywords_title" id="joomdoc_keywords_title" value="' . $this->escape($this->search->keywords_title) . '" /></td></tr>';
        }
        if ($config->searchShowText) {
            echo '<tr><td><label for="joomdoc_keywords_text">' . JText::_('JOOMDOC_SEARCH_AREA_TEXT') . '</label></td>';
            echo '<td><input type="text" name="joomdoc_keywords_text" id="joomdoc_keywords_text" value="' . $this->escape($this->search->keywords_text) . '" /></td></tr>';
        }        
        if ($config->searchShowMetadata) {
            echo '<tr><td><label for="joomdoc_keywords_meta">' . JText::_('JOOMDOC_SEARCH_AREA_META') . '</label></td>';
            echo '<td><input type="text" name="joomdoc_keywords_meta" id="joomdoc_keywords_meta" value="' . $this->escape($this->search->keywords_meta) . '" /></td></tr>';
        }                
        if ($config->searchShowFulltext) {
            echo '<tr><td><label for="joomdoc_keywords_full">' . JText::_('JOOMDOC_SEARCH_AREA_FULL') . '</label></td>';
            echo '<td><input type="text" name="joomdoc_keywords_full" id="joomdoc_keywords_full" value="' . $this->escape($this->search->keywords_full) . '" /></td></tr>';
        }                        
    }
    
    foreach ($this->searchablefields as $field)
    	if ($field->type == JOOMDOC_FIELD_DATE) {
    		echo '<tr><td><label for="'.$field->name.'">' . $field->title . '</label></td><td>';
    		echo JHtml::calendar($this->search->fields[$field->id]['value'], $field->name, $field->name);
    		echo '</td></tr>';
    	} elseif ($field->type == JOOMDOC_FIELD_SELECT) {
    		echo '<tr><td><label for="'.$field->name.'">' . $field->title . '</label></td><td>';
    		echo JHtml::_('select.genericlist', array_merge(array('' => ''), $field->options), $field->name, '', 'value', 'label', $this->search->fields[$field->id]['value']);
    		echo '</td></tr>'; 
    	} elseif ($field->type == JOOMDOC_FIELD_MULTI_SELECT || $field->type == JOOMDOC_FIELD_SUGGEST) {
    		echo '<tr><td><label for="'.$field->name.'">' . $field->title . '</label></td><td>';
    		echo '<input type="hidden" name="'.$field->name.'[]" value="" />';
    		echo JHtml::_('select.genericlist', $field->options, $field->name.'[]', 'multiple="multiple"' . ($field->params->get('size') ? ' size="'.$this->escape($field->params->get('size')).'"' : ''), 'value', 'label', $this->search->fields[$field->id]['value']);
            JHtml::_('joomdoc.chosen', $field->name, false);
    		echo '</td></tr>'; 
    	} elseif ($field->type == JOOMDOC_FIELD_RADIO) {
    		echo '<tr><td><label for="'.$field->name.'">' . $field->title . '</label></td><td>';
    		echo '<fieldset class="radio btn-group">';
    		echo '<input type="hidden" name="'.$field->name.'" value="" />';
    		echo '<input type="radio" name="'.$field->name.'" id="'.$field->name.'_0" value="0" '.($this->search->fields[$field->id]['value'] == '0' ? 'checked="checked"' : '').' class="checkbox"/>';
    		echo '<label for="'.$field->name.'_0" class="inline">'.JText::_('JNO').'</label>';
    		echo '<input type="radio" name="'.$field->name.'" id="'.$field->name.'_1" value="1" '.($this->search->fields[$field->id]['value'] == '1' ? 'checked="checked"' : '').' class="checkbox"/>';
    		echo '<label for="'.$field->name.'_1" class="inline">'.JText::_('JYES').'</label>';
    		echo '</fieldset>';
    		echo '</td></tr>';
    	} elseif ($field->type == JOOMDOC_FIELD_CHECKBOX) {
    		echo '<tr><td><label for="'.$field->name.'">' . $field->title . '</label></td><td>';
    		foreach ($field->options as $i => $option) {
    			echo '<input type="hidden" name="'.$field->name.'['.$i.']" value="" />';
    			echo '<input type="checkbox" name="'.$field->name.'['.$i.']" id="'.$field->name.'_'.$i.'" value="'.$option->value.'" '.(JArrayHelper::getValue($this->search->fields[$field->id]['value'], $i) == $option->value ? 'checked="checked"' : '').' class="checkbox"/>';
    			echo '<label class="inline" for="'.$field->name.'_'.$i.'">'.$option->label.'</label>';
    		}
    		echo '</td></tr>';
    	} elseif ($field->type == JOOMDOC_FIELD_TEXT || $field->type == JOOMDOC_FIELD_TEXTAREA || $field->type == JOOMDOC_FIELD_EDITOR) {
            if ($config->searchKeyword == 2) { 
                echo '<tr><td><label for="joomdoc_keywords_field' . $field->id . '">' . $field->title . '</label></td>';
                echo '<td><input type="text" name="joomdoc_keywords_field' . $field->id . '" id="joomdoc_keywords_field' . $field->id . '" value="' . $this->escape($this->search->get(('keywords_field' . $field->id))) . '" /></td></tr>';
            }
        }
    
    // parents field
    if ($config->searchShowParent) {
    	echo '<tr><td><label for="joomdoc_parent">' . JText::_('JOOMDOC_SEARCH_PARENTS') . '</label></td><td>';
    	echo JHtml::_('joomdoc.parents', $this->search->parent, 'path', 'joomdoc_parent', true);
    	echo '</td></tr>';
    }
    // searching areas
    if ($config->searchKeyword == 1) {
        $customAreas = 0;
        foreach ($this->searchablefields as $field)
            if ($field->type == JOOMDOC_FIELD_TEXT || $field->type == JOOMDOC_FIELD_TEXTAREA || $field->type == JOOMDOC_FIELD_EDITOR) 
                $customAreas ++;
        if ($config->searchShowTitle || $config->searchShowText || $config->searchShowMetadata || $config->searchShowFulltext || $customAreas) {
            echo '<tr><td><label>' . JText::_('JOOMDOC_SEARCH_AREAS') . '</label></td>';
            echo '<td>';
            // area title
            if ($config->searchShowTitle) {
                echo '<span class="nowrap">';
                echo '<input type="hidden" name="joomdoc_area_title" value="0" />';
                echo '<input type="checkbox" class="checkbox"  name="joomdoc_area_title" id="joomdoc_area_title" value="1" ' . ($this->search->areaTitle ? 'checked="checked"' : '') . ' />';
                echo '<label class="inline" for="joomdoc_area_title">' . JText::_('JOOMDOC_SEARCH_AREA_TITLE') . '</label>';
                echo '</span>';
            }
            // area text
            if ($config->searchShowText) {
                echo '<span class="nowrap">';
                echo '<input type="hidden" name="joomdoc_area_text" value="0" />';
                echo '<input type="checkbox" class="checkbox"  name="joomdoc_area_text" id="joomdoc_area_text" value="1" ' . ($this->search->areaText ? 'checked="checked"' : '') . ' />';
                echo '<label class="inline" for="joomdoc_area_text">' . JText::_('JOOMDOC_SEARCH_AREA_TEXT') . '</label>';
                echo '</span>';
            }
            // area meta data
            if ($config->searchShowMetadata) {
                echo '<span class="nowrap">';
                echo '<input type="hidden" name="joomdoc_area_meta" value="0" />';
                echo '<input type="checkbox" class="checkbox"  name="joomdoc_area_meta" id="joomdoc_area_meta" value="1" ' . ($this->search->areaMeta ? 'checked="checked"' : '') . ' />';
                echo '<label class="inline" for="joomdoc_area_meta">' . JText::_('JOOMDOC_SEARCH_AREA_META') . '</label>';
                echo '</span>';
            }
            // area full text
            if ($config->searchShowFulltext) {
                echo '<span class="nowrap">';
                echo '<input type="hidden" name="joomdoc_area_full" value="0" />';
                echo '<input type="checkbox" class="checkbox"  name="joomdoc_area_full" id="joomdoc_area_full" value="1" ' . ($this->search->areaFull ? 'checked="checked"' : '') . ' />';
                echo '<label class="inline" for="joomdoc_area_full">' . JText::_('JOOMDOC_SEARCH_AREA_FULL') . '</label>';
                echo '</span>';
            }
            // area of custom searchable text fields
            foreach ($this->searchablefields as $field)
                if ($field->type == JOOMDOC_FIELD_TEXT || $field->type == JOOMDOC_FIELD_TEXTAREA || $field->type == JOOMDOC_FIELD_EDITOR) {
                    echo '<span class="nowrap">';
                    echo '<input type="hidden" name="' . $field->name . '" value="0" />';
                    echo '<input type="checkbox" class="checkbox"  name="' . $field->name . '" id="' . $field->name . '" value="1" ' . ($this->search->fields[$field->id]['value'] ? 'checked="checked"' : '') . ' />';
                    echo '<label class="inline" for="' . $field->name . '">' . $field->title . '</label>';
                    echo '</span>';
                }
            echo '</td></tr>';
        }
    }
    // searching keyword type
    if ($config->searchShowType) {
	    $options = array();
	    if ($config->searchTypeAnykey)
	    	$options[] = JHtml::_('select.option', JOOMDOC_SEARCH_ANYKEY, JText::_('JOOMDOC_SEARCH_ANYKEY'));
	    if ($config->searchTypeAllkey)
	    	$options[] = JHtml::_('select.option', JOOMDOC_SEARCH_ALLKEY, JText::_('JOOMDOC_SEARCH_ALLKEY'));
	    if ($config->searchTypePhrase)
	    	$options[] = JHtml::_('select.option', JOOMDOC_SEARCH_PHRASE, JText::_('JOOMDOC_SEARCH_PHRASE'));
	    if ($config->searchTypeRegexp)
	    	$options[] = JHtml::_('select.option', JOOMDOC_SEARCH_REGEXP, JText::_('JOOMDOC_SEARCH_REGEXP'));
	    if (count($options) > 1) {
	    	echo '<tr><td><label for="joomdoc_type">' . JText::_('JOOMDOC_SEARCH_TYPE') . '</label></td><td>';
	    	echo JHtml::_('select.genericlist', $options, 'joomdoc_type', '', 'value', 'text', $this->search->type);
	    	echo '</td></tr>';
	    }
    }
    // searching ordering
    if ($config->searchShowOrder) {
	    $options = array();
	    if ($config->searchOrderNewest)
	    	$options[] = JHtml::_('select.option', JOOMDOC_ORDER_NEWEST, JText::_('JOOMDOC_SEARCH_NEWEST'));
	    if ($config->searchOrderOldest)
	    	$options[] = JHtml::_('select.option', JOOMDOC_ORDER_OLDEST, JText::_('JOOMDOC_SEARCH_OLDEST'));
	    if ($config->searchOrderHits)
	    	$options[] = JHtml::_('select.option', JOOMDOC_ORDER_HITS, JText::_('JOOMDOC_SEARCH_POPULAR'));
	    if ($config->searchOrderTitle)
	    	$options[] = JHtml::_('select.option', JOOMDOC_ORDER_TITLE, JText::_('JOOMDOC_SEARCH_ALPHA'));
	    if (count($options) > 1) {
	    	echo '<tr><td><label for="joomdoc_ordering">' . JText::_('JOOMDOC_SEARCH_ORDERING') . '</label></td><td>';
	    	echo JHtml::_('select.genericlist', $options, 'joomdoc_ordering', '', 'value', 'text', $this->search->ordering);
	    	echo '</td></tr>';
	    }
    }
    echo '<tr><td></td><td class="btn-group"><button class="btn btn-primary" onclick="return JoomDOC.searchSubmit()">' . JText::_('JOOMDOC_SEARCH_SUBMIT') . '</button><button class="btn btn-danger" onclick="JoomDOC.resetSubmit()">' . JText::_('JOOMDOC_SEARCH_RESET') . '</button></td></tr>';
    echo '</table>';
    echo '</div>';
}

if ($this->access->canUpload || $this->access->canCreateFolder || $this->access->canCopyMove) {
    echo JHtmlJoomDOC::startTabs('tabone', 'com_joomdoc_upload');
}

if ($this->access->canUpload) {
	
    echo JHtmlJoomDOC::addTab('JOOMDOC_UPLOAD', 'com_joomdoc_upload', 'tabone');
    
    if (!$config->dropAndDrag) { // single upload
        if (JOOMDOC_ISJ3){ //to match J3 design and not break old
            echo '<div class="upload" style="">';
            echo '<div class="btn-group pull-left">';
            echo '<label class="btn">';
            echo '<input type="file" name="upload" id="upload" />';
            echo '</label>';
            echo '<button type="submit" class="btn" onclick="return JoomDOC.upload(this)">' . JText::_('JOOMDOC_UPLOAD') . '</button>';
            echo '</div>';
            echo '<label class="hasTip filter-search-lbl pull-left" for="iszip" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_UNPACK_ZIP') . '">' . JText::_('JOOMDOC_DOCUMENTS_UNPACK_ZIP') . '';
            echo '<input type="checkbox" name="iszip" id="iszip" value="1" class="pull-left" /></label>';
            echo '<div class="clearfix"></div>';
            echo '</div>';
        } else {
            echo '<div class="upload">';
            echo '<div class="btn-group pull-left">';
            echo '<div class="myfileupload-buttonbar pull-left">';
            echo '<label class="myui-button">';
            echo '<input type="file" name="upload" id="upload" class="btn" />';
            echo '</label>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-info" onclick="return JoomDOC.upload(this)">' . JText::_('JOOMDOC_UPLOAD') . '</button>';
            echo '</div>';
            echo '<input type="checkbox" name="iszip" id="iszip" value="1" class="pull-left" />';
            echo '<label class="hasTip filter-search-lbl pull-left" for="iszip" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_UNPACK_ZIP') . '">' . JText::_('JOOMDOC_DOCUMENTS_UNPACK_ZIP') . '</label>';
            echo '<div class="clr"></div>';
            echo '</div>';
        }
    } else { // multiple upload
        echo '<div class="upload" id="upload">';
        echo '<span>';
        echo JText::_('JOOMDOC_UPLOAD_DRAG_FILES_HERE'); 
        echo '</span>';
        echo '<img id="preloader" src="' . JOOMDOC_IMAGES . 'preloader.gif" />';
        echo '</div>';
        echo '<span id="preview"></span>';           
    }
    
    echo JHtmlJoomDOC::endTab();
}

if ($this->access->canCreateFolder) {
    echo JHtmlJoomDOC::addTab('JOOMDOC_DOCUMENTS_NEW_FOLDER', 'com_joomdoc_new_folder', 'tabone');
    
    $method = 'return JoomDOC.mkdir(this, ' . ($this->access->canCreate ? 'true' : 'false') . ')';

    echo '<span class="input-append">';
    echo '<input class="hasTip" type="text" name="newfolder" id="newfolder" placeholder="'. JText::_('JOOMDOC_DOCUMENTS_NEW_FOLDER').'" value="' . $this->escape(JRequest::getString('newfolder')) . '" onchange="' . $method . '"  title="' . $this->getTooltip('JOOMDOC_DOCUMENTS_NEW_FOLDER') . '" />';

    echo '<button type="submit" class="btn  " onclick="' . $method . '">' . JText::_('JOOMDOC_CREATE') . '</button>';
    echo '</span>';
    
    echo JHtmlJoomDOC::endTab();
}

if ($this->access->canCopyMove) {
    echo JHtmlJoomDOC::addTab('JOOMDOC_COPY_MOVE', 'com_joomdoc_edit', 'tabone');
    
    echo '<div class="btn-group">';   
	if (!JoomDOCFileSystem::haveOperation()) {
        echo '<button class="btn btn-small" onclick="if (document.adminForm.boxchecked.value==0){alert(\''.JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST', true).'\');}else{ Joomla.submitbutton(\''.JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_COPY).'\')};return false;">';
        echo '<span class="icon-copy"></span>';
        echo JText::_('JTOOLBAR_COPY');
        echo '</button>';
    
        echo '<button class="btn btn-small" onclick="if (document.adminForm.boxchecked.value==0){alert(\''.JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST', true).'\');}else{ Joomla.submitbutton(\''.JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_MOVE).'\')};return false;">';
        echo '<span class="icon-move"></span>';
        echo JText::_('JTOOLBAR_MOVE');
        echo '</button>';
    } else {
        echo '<button class="btn btn-small" onclick="Joomla.submitbutton(\''.JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_PASTE).'\')">';
        echo '<span class="icon-save"></span>';
        echo JText::_('JTOOLBAR_PASTE');
        echo '</button>';

        echo '<button class="btn btn-small" onclick="Joomla.submitbutton(\''.JoomDOCHelper::getTask(JOOMDOC_DOCUMENT, JOOMDOC_TASK_RESET).'\')">';
        echo '<span class="icon-remove"></span>';
        echo JText::_('JTOOLBAR_RESET');
        echo '</button>';
    }
    
    echo '</div>';
    
    echo '<label class="checkall-toggle">';
    echo '<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />';
    echo "\n".JText::_('JOOMDOC_CHECK_ALL');
    echo '</label>';
    
    echo JHtmlJoomDOC::endTab();
}

if ($this->access->canUpload || $this->access->canCreateFolder || $this->access->canCopyMove) {
    echo JHtmlJoomDOC::endTabs();
}

$this->root->initIteration();

// check if document allow display files without documents
$filesWithouDoc = false;
if ($this->access->docid) {
    $params = new JRegistry($this->root->document->params);
    $filesWithouDoc = (int) $params->get('files_without_doc', 1);
}
$filesWithouDoc = $config->filesWithoutDoc || $filesWithouDoc;

$class = null;

if (!$this->root->hasNext()) {
    // folder is empty
    echo '<p class="empty">' . JText::_('JOOMDOC_EMPTY_FOLDER') . '</p>';
}

$files = $folders = array();

$i = -1;
while ($this->root->hasNext()) {
    $i++;
    $item = $this->root->getNext();
    $access = new JoomDOCAccessHelper($item);
    
    if ($access->docid)
        $item->document->description = JoomDOCHelper::applyContentPlugins($item->document->description, ($access->isFile ? $access->relativePath : null), $config->edocsList);

    // save files/folders names for next using
    $access->isFile ? $files[] = $item->getFileName() : $folders[] = $item->getFileName();

    // no subfolders
    if ($access->isFolder && !$config->showSubfolders)
        continue;

    // no display files without doc	
    if (!$filesWithouDoc && !$access->docid && $access->isFile)
        continue;

    // document is unpublish and user cannot edit document or document is trashed
    if ($access->docid && $item->document->published == JOOMDOC_STATE_UNPUBLISHED && !$access->canAnyEditOp)
        continue;

    if ($config->showFileicon)
    	$class = JoomDOCHelper::getFileIconClass($access->isFile ? $access->relativePath : 'folder');
    
    // url to open item detail
    $viewDocuments = JRoute::_(JoomDOCRoute::viewDocuments($access->relativePath, $access->alias));

    echo '<div class="document' . ($access->isFavorite ? ' favorite' : '') . '">';
    echo '<h2 ' . ($class ? 'class="icon ' . $class . '"' : '') . '>';
    if ($access->canOpenFile || $access->canOpenFolder) {
        // link to open file/subfolder
        echo '<a href="' . $viewDocuments . '" title="">';
    }
    // as item title use document title or file name. use title only if published. (that means that owner will see it even if unpublished)
    echo ($access->docid AND $item->document->published==JOOMDOC_STATE_PUBLISHED) ? $item->document->title : $item->getFileName();
    if ($access->canOpenFile || $access->canOpenFolder) {
        echo '</a>';
    }
    if ($this->access->canCopyMove) {
        echo '<input type="checkbox" name="paths[]" id="cbb' . $i . '" value="' . $this->escape($access->relativePath) . '" class="blind" />';
        echo '<input type="checkbox" name="cid[]" id="cb' . $i . '" value="' . $access->docid . '" onclick="Joomla.isChecked(this.checked);JoomDOC.check(this,' . $i . ')" class="pull-right" />';
    }    
    echo '</h2>';

    if ($access->canViewFileInfo && (($access->docid && $this->access->canShowFileDates) || (!$access->isFolder && $this->access->canShowFileInfo) || $access->isFavorite)) {
        echo '<div class="info">';
        if ($access->isFavorite) {
            echo '<span class="favorite">' . JText::_('JOOMDOC_FAVORITE') . '</span>';
        }
        if ($config->showFilesize && !$access->isFolder) {
            echo '<span class="filesize">' . JText::sprintf('JOOMDOC_FILESIZE', JoomDOCFileSystem::getFileSize($access->absolutePath)) . '</span>';
        }
        if ($access->docid) {
            if ($config->showCreated) {
                echo '<span class="created">' . JText::sprintf('JOOMDOC_CREATED', JHtml::date($item->document->created, JText::_('JOOMDOC_UPLOADED_DATE_J16'))) . '</span>';
            }
            if ($config->showModified && JoomDOCHelper::canViewModified($item->document->created, $item->document->modified)) {
                echo '<span class="modified">' . JText::sprintf('JOOMDOC_MODIFIED', JHtml::date($item->document->modified, JText::_('JOOMDOC_UPLOADED_DATE_J16'))) . '</span>';

            }
        }
        if ($config->showHits && !$access->isFolder) {
            echo '<span class="hits">' . JText::sprintf('JOOMDOC_HITS_INFO', JoomDOCHelper::number($item->hits)) . '</span>';
        }
        foreach ($this->listfields as $field) {
        	if ($value = JHtml::_('joomdoc.showfield', $field, $item->document))
        		echo '<span class="field">' . $field->title . ': ' . $value . '</span>';
    	}
        echo '<div class="clr"></div>';
        echo '</div>';
    }
    if ($access->docid && ($description = JString::trim($item->document->description)) && ($this->access->canShowAllDesc || ($access->isFolder && $config->showFolderDesc) || ($access->isFile && $config->showFileDesc))) {
        echo '<p>' . JoomDOCString::crop($description, 200) . '</p>';
    }

    if ($config->showLicense && $access->licenseID && $access->isFile) {
        echo '<a class="modal license" rel="{handler: \'iframe\', size: {x: 800, y: 600}, onClose: function() {}}" href="' . JoomDOCRoute::viewLicense($access->licenseID, $access->licenseAlias) . '">' . JText::sprintf('JOOMDOC_ITEM_LICENSE', $access->licenseTitle) . '</a>';
    }

    if ($access->canOpenFolder || $access->canOpenFile || $access->canDeleteDoc || $access->canEdit || $access->canCreate || $access->canDeleteFile || $access->canDownload) {
        echo '<div class="toolbar">';
        if ($config->showOpenFolder && $access->isFolder) {
            echo '<a class="open" href="' . $viewDocuments . '" title="">' . JText::_('JOOMDOC_DISPLAY_FOLDER') . '</a>';
        }
        if ($config->showOpenFile && $access->isFile) {
            echo '<a class="open" href="' . $viewDocuments . '" title="">' . JText::_('JOOMDOC_DISPLAY_FILE') . '</a>';
        }
        if ($config->showDownloadFile && $access->canDownload) {
            if ($access->licenseID) {
                echo '<a class="modal download" rel="{handler: \'iframe\', size: {x: ' . JOOMDOC_LIGHTBOX_WIDTH . ', y: ' . JOOMDOC_LIGHTBOX_HEIGHT . '}, onClose: function() {}}" href="' . JoomDOCRoute::viewLicense($access->licenseID, $access->licenseAlias, $access->relativePath, $access->alias) . '">' . JText::_('JOOMDOC_DOWNLOAD_FILE') . '</a>';
            } else {
                echo '<a class="download" href="' . JRoute::_(JoomDOCRoute::download($access->relativePath, $access->alias)) . '" title="">' . JText::_('JOOMDOC_DOWNLOAD_FILE') . '</a>';
            }
        }
        if ($access->canCreate) {
            echo '<a class="add" href="' . JRoute::_(JoomDOCRoute::add($access->relativePath, $access->alias)) . '" title="">' . JText::_('JOOMDOC_ADD_DOCUMENT') . '</a>';
        }
        if ($access->canEdit) {
            echo '<a class="edit" href="' . JRoute::_(JoomDOCRoute::edit($access->relativePath, $access->alias)) . '" title="">' . JText::_('JOOMDOC_EDIT_DOC') . '</a>';
        }
        if ($access->canEditState) {
            if ($item->document->state == JOOMDOC_STATE_UNPUBLISHED) {
                echo '<a class="publish" href="' . JRoute::_(JoomDOCRoute::publish($access->relativePath, $access->alias)) . '" title="">' . JText::_('JOOMDOC_PUBLISH') . '</a>';
            } elseif ($item->document->state == JOOMDOC_STATE_PUBLISHED) {
                echo '<a class="unpublish" href="' . JRoute::_(JoomDOCRoute::unpublish($access->relativePath, $access->alias)) . '" title="">' . JText::_('JOOMDOC_UNPUBLISH') . '</a>';
            }
        }
        if ($access->canDeleteFile) {
            echo '<a class="delete" href="javascript:void(0)" onclick="JoomDOC.confirm(\'' . addslashes(JRoute::_(JoomDOCRoute::deletefile($access->relativePath, $access->alias))) . '\')" title="">' . JText::_('JOOMDOC_DELETE_ITEM') . '</a>';
        }
        if ($access->canDeleteDoc) {
            echo '<a class="deleteDocument" href="javascript:void(0)" onclick="JoomDOC.confirm(\'' . addslashes(JRoute::_(JoomDOCRoute::delete($access->relativePath, $access->alias))) . '\')" title="">' . JText::_('JOOMDOC_DELETE_DOCUMENT') . '</a>';
        }
        
                
        echo '<div class="clr"></div>';
        echo '</div>';
    }
    echo '</div>';
}
echo '<div class="pagination">' . $this->pagination->getListFooter();
if (JOOMDOC_ISJ3 && $this->pagination->total > 5) { 
    echo '<label for="limit">'.JText::_('JGLOBAL_DISPLAY_NUM').'</label>'.$this->pagination->getLimitBox();
}
echo '</div>';
echo '</div>';
echo '<input type="hidden" name="task" value="" />';
if (isset($this->search)) {
    echo '<input type="hidden" name="joomdoc_search" id="joomdoc_search" value="' . $this->search->search . '" />';
}
echo '<input type="hidden" id="joomdocToken" name="' . JSession::getFormToken() . '" value="1" />';
echo '<input type="hidden" name="boxchecked" value="" />';
echo '<input type="hidden" name="doccreate" value="0" />';
echo '</form>';
JoomDOCHelper::jsArray('joomDOCFiles', $files);
JoomDOCHelper::jsArray('joomDOCFolders', $folders);

if ($this->access->canWebDav) {
    JoomDOCWebDav::add();
}
?>