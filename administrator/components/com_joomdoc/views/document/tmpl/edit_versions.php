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

$juri = JURI::getInstance();
/* @var $juri JURI */

echo JHtmlJoomDOC::addTab('JOOMDOC_DOCUMENT_VERSIONS', 'versions', 'tabone');

    echo '<form name="versionForm" id="versionForm" action="' . JRoute::_($juri->current() . '?' . $juri->getQuery()) . '" method="post">';
        echo '<fieldset class="autoHeight filter-bar">';
            echo '<table class="fullWidth">';
                echo '<tr>';
                
                    echo '<td nowrap="nowrap">';
                        echo '<label style="float: left; margin-right: 10px;" class="filter-search-lbl edit" for="versionNote">' . JText::_('JOOMDOC_FIELD_VERSION_NOTE_LABEL') . '</label>';
                    echo '</td>';
                    echo '<td>';
                        echo '<input style="width: 200px;" type="text" name="versionNote" id="versionNote" value="' . $this->escape($this->versionsFilter->versionNote) . '" class="fullWidth" onchange="this.form.submit()" />';
                    echo '</td>';
                //echo '</tr>';
                //echo '<tr>';
                    echo '<td nowrap="nowrap">';
                        echo '<label style="float: left; margin-right: 10px;" class="filter-search-lbl edit" for="description">' . JText::_('JOOMDOC_DESCRIPTION') . '</label>';
                    echo '</td>';
                    echo '<td>';
                        echo '<input style="width: 200px;" type="text" name="description" id="description" value="' . $this->escape($this->versionsFilter->description) . '" class="fullWidth" onchange="this.form.submit()" />';
                    echo '</td>';
                //echo '</tr>';
                //echo '<tr>';
                    echo '<td  nowrap="nowrap">';
                        echo '<label style="float: left; margin-right: 10px;" class="filter-search-lbl edit" for="creatorName">' . JText::_('JOOMDOC_CREATOR') . '</label>';
                    echo '</td>';
                    echo '<td nowrap="nowrap">';
                        echo '<input style="width: 200px;" type="text" name="creatorName" id="creatorName" value="' . $this->escape($this->versionsFilter->creatorName) . '" onchange="this.form.submit()" />';
                    echo '</td>';
                    echo '<td nowrap="nowrap">';
                        echo '<label style="float: left; margin-right: 10px;" class="filter-search-lbl edit" for="modifierName">' . JText::_('JOOMDOC_MODIFIER') . '</label>';
                    echo '</td>';
                    echo '<td nowrap="nowrap">';
                        echo '<input style="width: 200px;" type="text" name="modifierName" id="modifierName" value="' . $this->escape($this->versionsFilter->modifierName) . '" onchange="this.form.submit()" />';
                    echo '</td>';
                    echo '<td>';
                        echo '<span class="btn-group">';
                        $js = 'this.form.' . implode('.value=\'\';this.form.', $this->versionsFilter->fullTextFilters) . '.value=\'\';this.form.submit();';
                        echo '<button style="margin-bottom: 9px;" type="submit" class="btn">' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';
                        echo '<button style="margin-bottom: 9px;" type="button" class="btn" onclick="' . $js . '">' . JText::_('JSEARCH_FILTER_CLEAR') . '</button>';
                        echo '</div>';
                        
                        echo '</td>';
                echo '</tr>';
            echo '</table>';
        echo '</fieldset>';
        
        echo '<table class="adminlist table table-striped">';
            echo '<thead>';
                echo '<tr>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_VERSION', 'version', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_TITLE', 'title', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_FIELD_VERSION_NOTE_LABEL', 'versionNote', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_DESCRIPTION', 'description', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_CREATOR', 'creatorName', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_TITLE_CREATED', 'created', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_MODIFIER', 'modifierName', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                    echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_TITLE_MODIFIED', 'modified', $this->versionsFilter->listDirn, $this->versionsFilter->listOrder) . '</th>';
                echo '</tr>';
            echo '</thead>';
            echo '<tfoot>';
                echo '<tr>';
                    $pagination = new JPagination($this->versionsFilter->total, $this->versionsFilter->offset, $this->versionsFilter->limit);
                    echo '<td colspan="8">' . str_replace(array('adminForm', 'Joomla.submitform();'), array('versionForm', 'document.versionForm.submit();'), $pagination->getListFooter()) . '</td>';
                echo '</tr>';
            echo '</tfoot>';
            echo '<tbody>';
    foreach ($this->versions as $i => $version) {
        echo '<tr class="row' . ($i % 2) . '">';
        echo '<td>' . $version->version . '</td>';
        echo '<td>' . $version->title . '</td>';
        echo '<td>' . $version->versionNote . '</td>';
        echo '<td>' . ($version->description ? JHtml::tooltip($version->description) . ' ' . JoomDOCHelper::crop($version->description, 100)  : ''). '</td>';
        echo '<td>' . $version->creatorName . '</td>';
        echo '<td>' . JoomDOCHelper::uploaded($version->created, false) . '</td>';
        echo '<td>' . $version->modifierName . '</td>';
        echo '<td>' . JoomDOCHelper::uploaded($version->modified, false) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '<!-- identify active bookmark -->';
    echo '<input type="hidden" name="bookmark" value="1" />';
    echo '<input type="hidden" name="filter_order" value="' . $this->versionsFilter->listOrder . '" />';
    echo '<input type="hidden" name="filter_order_Dir" value="' . $this->versionsFilter->listDirn . '" />';
    echo '</form>';

   echo JHtmlJoomDOC::endTab();
