<?php

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author   	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';
?>

<form action="<?php echo JRoute::_(JoomDOCRoute::viewFields()); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar" class="btn-toolbar filter-bar">
		<div class="filter-search fltlft btn-group pull-left input-append">
			<?php if (!JOOMDOC_ISJ3) { ?>
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<?php } ?>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
			<?php if (!JOOMDOC_ISJ3) { ?>
				<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			<?php } else { ?>
                <button title="" class="btn hasTooltip" type="submit" data-original-title="Search"><i class="icon-search"></i></button>
				<button onclick="document.id('filter_search').value='';this.form.submit();" title="" class="btn hasTooltip" type="button" data-original-title="Clear"><i class="icon-remove"></i></button>
			<?php } ?>
        </div>
	</fieldset>
	<div class="clr clearfix"> </div>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'JOOMDOC_FIELD_TYPE', 'a.type', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					<?php if ($saveOrder) :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'fields.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter() . (JOOMDOC_ISJ3 ? $this->pagination->getLimitBox() : ''); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'unicre.', true); ?>
					<?php endif; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_joomdoc&task=field.edit&id='.(int) $item->id); ?>">
							<?php echo JFilterOutput::cleanText($item->title); ?></a>
				</td>
				<td>
					<?php switch ($item->type) { 
						case JOOMDOC_FIELD_TEXT: echo JText::_('JOOMDOC_FIELD_TEXT'); break;
						case JOOMDOC_FIELD_DATE: echo JText::_('JOOMDOC_FIELD_DATE'); break;
						case JOOMDOC_FIELD_RADIO: echo JText::_('JOOMDOC_FIELD_RADIO'); break;
						case JOOMDOC_FIELD_SELECT: echo JText::_('JOOMDOC_FIELD_SELECT'); break;
						case JOOMDOC_FIELD_CHECKBOX: echo JText::_('JOOMDOC_FIELD_CHECKBOX'); break;
						case JOOMDOC_FIELD_TEXTAREA: echo JText::_('JOOMDOC_FIELD_TEXTAREA'); break;
						case JOOMDOC_FIELD_EDITOR: echo JText::_('JOOMDOC_FIELD_EDITOR'); break;
						case JOOMDOC_FIELD_MULTI_SELECT: echo JText::_('JOOMDOC_FIELD_MULTI_SELECT'); break;
                        case JOOMDOC_FIELD_SUGGEST: echo JText::_('JOOMDOC_FIELD_SUGGEST'); break;
					}?>
				</td>
				<td class="order">                    
					<?php if ($saveOrder) :?>
                        <div class="pull-right">
							<?php if ($listDirn == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                <br>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'outcomes.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                <br>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fields.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
                        </div>
					<?php endif; ?>
					<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
                    <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order input-mini" />
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'fields.', true, 'cb'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>