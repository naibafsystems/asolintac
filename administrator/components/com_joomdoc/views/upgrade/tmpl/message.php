<?php

/**
 * @version		$Id$
 * @package		ARTIO Booking 
 * @copyright	Copyright (C) 2010 ARTIO s.r.o.. All rights reserved.
 * @author 		ARTIO s.r.o., http://www.artio.net
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @link        http://www.artio.net Official website
 */

defined('_JEXEC') or die('Restricted access');

$message = JRequest::getString('message');

?>
<table class="adminform">
	<tr>
		<td align="left">
			<strong><?php echo $message; ?></strong>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			[&nbsp;<a href="<?php echo $this->url; ?>" style="font-size: 16px; font-weight: bold"><?php echo JText::_('Continue ...'); ?></a>&nbsp;]
		</td>
	</tr>
</table>