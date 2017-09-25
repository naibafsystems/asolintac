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
?>
<div id="joomdocSearch" class="<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
    <form name="mod_joomdoc_search_form" method="post" action="<?php echo JRoute::_(JoomDOCRoute::viewDocuments()); ?>" onsubmit="JoomDOCSearch.submit()">
        <div class="input-append">
            <input type="text" name="mod_joomdoc_search" id="mod_joomdoc_search" value="" class="input-small" />
            <button class="btn btn-primary"><?php echo JText::_('JOOMDOC_SEARCH_SUBMIT'); ?></button>
        </div>
        <input type="hidden" name="joomdoc_search" value="1" />
        <?php foreach ($fields as $field) { ?>
            <input type="hidden" name="<?php echo $field; ?>" value="" id="mod_<?php echo $field; ?>" />
        <?php } ?>
    </form>
</div>
<script type="text/javascript">
    // <![CDATA[
    JoomDOCSearch = {
        submit: function() {
            var value = document.getElementById('mod_joomdoc_search').value;
            var fields = <?php echo json_encode($fields); ?>;
            var field;
            for (var i = 0; i < fields.length; i++) {
                field = document.getElementById('mod_' + fields[i]);
                if (field) {
                    field.value = value;
                }
            }
        }
    }
    // ]]>
</script>