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

/* @var $this JoomDOCViewLicenses */

$listOrder = $this->escape($this->state->get(JOOMDOC_FILTER_ORDERING));
$listDirn = $this->escape($this->state->get(JOOMDOC_FILTER_DIRECTION));
echo '<form action="' . JRoute::_(JoomDOCRoute::viewLicenses()) . '" method="post" name="adminForm" id="adminForm">';
echo '<fieldset class="btn-toolbar autoHeight filter-bar">';
    echo '<div>';
        // text filter
   		 echo '<label class="filter-search-lbl element-invisible" for="Filter" style="display:none">Filter</label>'; //J2.5 does niot have element-invisible    
            echo '<div class="btn-group pull-left input-append">';
           	 	echo '<input style="width: 130px; margin-bottom: 0px;" class="pull-left" type="text" name="filter" id="filter" title="Filter" placeholder="Filter" value="' . $this->escape($this->filter) . '" />';
	            echo '<button type="submit" class="btn" title="'.JText::_('JSEARCH_FILTER_SUBMIT').'"><i class="icon-search"></i>'.(JOOMDOC_ISJ3 ? '' : JText::_('JSEARCH_FILTER_SUBMIT')).'</button>';
	            echo '<button type="button" class="btn" title="'.JText::_('JSEARCH_FILTER_CLEAR').'" onclick="var f=this.form;f.filter.value=\'\'; f.submit();"><i class="icon-remove"></i>'.(JOOMDOC_ISJ3 ? '' : JText::_('JSEARCH_FILTER_CLEAR')).'</button>';
            echo '</div>';
    echo '</div>';
echo '</fieldset>';
echo '<div class="clearfix"></div>';
echo '<table class="adminlist table table-striped" cellspacing="1">';
echo '<thead><tr>';
echo '<th width="1%" class="center"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>';
echo '<th>' . JHtml::_('grid.sort', 'JOOMDOC_TITLE', 'lcs.title', $listDirn, $listOrder) . '</th>';
echo '<th width="5%">' . JHtml::_('grid.sort', 'JSTATUS', 'lcs.state', $listDirn, $listOrder) . '</th>';
echo '<th width="5%">' . JHtml::_('grid.sort', 'JOOMDOC_DEFAULT', 'lcs.default', $listDirn, $listOrder) . '</th>';
echo '<th width="8%">' . JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'lcs.created_by', $listDirn, $listOrder) . '</th>';
echo '<th width="7%">' . JHtml::_('grid.sort', 'JDATE', 'lcs..created', $listDirn, $listOrder) . '</th>';
echo '<th width="1%">' . JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'lcs.id', $listDirn, $listOrder) . '</th>';
echo '</tr></thead>';
echo '<tfoot><tr><td colspan="20">' . $this->pagination->getListFooter() . '</td></tr></tfoot><tbody>';
$canManage = JoomDOCAccess::licenses();
$dateFormat = JText::_('DATE_FORMAT_LC4');
foreach ($this->licenses as $i => $license) {
    echo '<tr class="row' . ($i % 2) . '">';
    echo '<td class="center">';
    echo $license->checked_out ? JHtml::_('jgrid.checkedout', $i, $license->editor, $license->checked_out_time, JOOMDOC_LICENSES . '.', $canManage) : JHtml::_('grid.id', $i, $license->id);
    echo '</td>';
    echo '<td><a href="' . JRoute::_(JoomDOCRoute::editLicense($license->id)) . '" title="">' . $license->title . '</a></td>';
    echo '<td class="center" align="center">';
    echo JHtml::_('jgrid.published', $license->state, $i, JOOMDOC_LICENSES . '.', $canManage, 'cb');
    echo '</td>';
    echo '<td class="center" align="center">' . JHtml::_('joomdoc.defaults', $license->default, $i, JOOMDOC_LICENSES, $canManage) . '</td>';
    echo '<td nowrap="nowrap">' . $license->creator . '</td>';
    echo '<td nowrap="nowrap">' . JHtml::_('date', $license->created, $dateFormat) . '</td>';
    echo '<td>' . $license->id . '</td>';
    echo '</tr>';
}
if (!$this->pagination->total)
    echo '<tr><td colspan="20">' . JText::_('JOOMDOC_NO_LICENSES') . '</td></tr>';
echo '</tbody></table>';
echo '<input type="hidden" name="task" value="" /><input type="hidden" name="boxchecked" value="" />';
echo '<input type="hidden" name="filter_order" value="' . $listOrder . '" /><input type="hidden" name="filter_order_Dir" value="' . $listDirn . '" />';
echo JHtml::_('form.token') . '</form>';
?>