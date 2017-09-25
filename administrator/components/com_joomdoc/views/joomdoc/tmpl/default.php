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

/* @var $this JoomDOCViewJoomDOC */

$config = JoomDOCConfig::getInstance();

$data = array_change_key_case(JApplicationHelper::parseXMLInstallFile(JOOMDOC_MANIFEST), CASE_LOWER);
$xml = simplexml_load_file(JOOMDOC_MANIFEST); //all xml stuff is removed/deprecated in J3.0
echo '<div class="doc-width-60 fltlft">';
    echo '<div id="cpanel">';
    
    require_once JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html/joomdoc.php';
    echo JHtmlJoomDOC::startTabs('doc-cpanel-tabs', 'documents');
    echo JHtmlJoomDOC::addTab('COM_DOC_JOOMDOC_CONFIGURATION', 'documents', 'doc-cpanel-tabs');

        echo '<div class="icon-wrapper">';
            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_DOCUMENTS') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewDocuments()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-documents.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_DOCUMENTS') . '</span>';
                echo '</a>';
            echo '</div>';

            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_LICENSES') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewLicenses()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-license.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_LICENSES') . '</span>';
                echo '</a>';
            echo '</div>';
                        echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_CUSTOM_FIELDS') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewFields()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-field.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_CUSTOM_FIELDS') . '</span>';
                echo '</a>';
            echo '</div>';
        echo '</div>';
        
        echo JHtmlJoomDOC::endTab();
        echo JHtmlJoomDOC::addTab('COM_DOC_UPGR', 'upgrade', 'doc-cpanel-tabs');
        
        echo '<div class="icon-wrapper"  style="float: left">';
            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_UPGRADE') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewUpgrade()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-upgrade.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_UPGRADE') . '</span>';
                echo '</a>';
            echo '</div>';

            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_MIGRATION') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewMigration()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-migration.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_MIGRATION') . '</span>';
                echo '</a>';
            echo '</div>';
        echo '</div>';
        
        echo JHtmlJoomDOC::endTab();
        echo JHtmlJoomDOC::addTab('COM_DOC_HANDS', 'help', 'doc-cpanel-tabs');
        
        echo '<div class="icon-wrapper"  style="float: left">';
            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_CHANGELOG') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewChangelog()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-changelog.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_CHANGELOG') . '</span>';
                echo '</a>';
            echo '</div>';

            echo '<div class="hasTip icon" title="' . $this->getTooltip('JOOMDOC_SUPPORT') . '">';
                echo '<a href="' . JRoute::_(JoomDOCRoute::viewSupport()) . '" title="" >';
                    echo '<img src="' . JOOMDOC_IMAGES . 'icon-48-help_header.png" alt="" />';
                    echo '<span>' . JText::_('JOOMDOC_SUPPORT') . '</span>';
                echo '</a>';
            echo '</div>';
        echo '</div>';
        
        
        echo JHtmlJoomDOC::endTab();
        echo JHtmlJoomDOC::endTabs();
        
    echo '</div>';
echo '</div>';
?>

<div class="doc-width-40 fltrt">
    
	<?php
	echo JHtmlJoomDOC::startSliders('doc-info-pane', 'info-panel');
	echo JHtmlJoomDOC::addSlide('doc-info-pane', 'JOOMDOC', 'info-panel');
	?>
	
	<table class="adminlist table table-striped">
	   <tr>
			<th></td>
			<td>
                            <?php
                                echo '<a href="' . JRoute::_($data['authorurl']) . '" target="_blank" title="">';
                                echo '<img src="' . JOOMDOC_IMAGES . 'icon-120-joomdoc.png" alt="" />';
                            ?>
	        	</a>
			</td>
		</tr>
	   <tr>
	      <th width="120"></td>
	      <td><a href="<?php echo JRoute::_($data['authorurl']); ?>" target="_blank"><?php echo JText::_('JOOMDOC'); ?></td>
	   </tr>	
	   <tr>
	      <th><?php echo JText::_('JOOMDOC_VERSION'); ?>:</td>
	      <td><?php echo $data['version']; 
	      
	       echo '<br>'.JText::sprintf('JOOMDOC_EXTEND', JOOMDOC_URL_FEATURES, JOOMDOC_URL_ESHOP);
	      
	      ?></td>
	   </tr>
	   <tr>
	      <th><?php echo JText::_('JOOMDOC_DATE'); ?>:</td>
	      <td><?php echo JHTML::date($data['creationdate'], JText::_('DATE_FORMAT_LC4')); ?></td>
	   </tr>
	   <tr>
	      <th valign="top"><?php echo JText::_('JOOMDOC_COPYRIGHT'); ?>:</td>
	      <td>&copy; 2006 - <?php echo date('Y') . ', ' . $data['author']; ?></td>
	   </tr>
	   <tr>
	      <th><?php echo JText::_('JOOMDOC_AUTHOR'); ?>:</td>
	      <td><a href="<?php echo $data['authorurl']; ?>" target="_blank"><?php echo $data['author']; ?></a>,
	      <a href="mailto:<?php echo $data['authoremail']; ?>"><?php echo $data['authoremail']; ?></a></td>
	   </tr>
	   <tr>
	      <th valign="top"><?php echo JText::_('JOOMDOC_DESCRIPTION'); ?>:</td>
	      <td><?php echo JText::_('JOOMDOC_DESC'); ?></td>
	   </tr>
	   <tr>
	      <th><?php echo JText::_('JOOMDOC_LICENSE'); ?>:</td>
	      <td><?php echo '<a href="' . $xml->license . '" target="_blank" class="hasTip" title="' . $this->getTooltip('JOOMDOC_LICENSER') . '">' . $xml->licenser . '</a>'; ?></td>
	   </tr>
	</table>
    
	<?php
	echo JHtmlJoomDOC::endSlide();
	echo JHtmlJoomDOC::endSlides();
	?>
    	
</div>
<?php
echo '</div>';
?>