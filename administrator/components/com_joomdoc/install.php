<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class com_joomdocInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public static function install($parent, $installation = true)
	{
	    /* Load Joomla framework */
	    $db = JFactory::getDbo();
	    /* @var $db JDatabase */
	    $document = JFactory::getDocument();
	    /* @var $document JDocument */
	    $language = JFactory::getLanguage();
	    /* @var $language JLanguage */
	    $mainframe = JFactory::getApplication();
	    /* @var $mainframe JApplication */
	    $man = JApplicationHelper::parseXMLInstallFile(JPath::clean(dirname(__FILE__) . '/joomdoc.xml'));
	    // Add style it this way because in Joomla 1.7.2 doesn't work placing CSS into page head during component installation
	    echo '<style type="text/css">';
	    echo '<!--';
	    echo '#JoomDOCInstall img{float: left;margin: 0 10px 0 0;}';
	    echo '-->';
	    echo '</style>';
	    // load component language
	    $language->load('com_joomdoc', JPATH_ADMINISTRATOR);
	    // install component extensions
	    if ($installation) {
	        // register JoomDOC installer to extend Joomla installer
	        JLoader::register('AInstaller', JPATH_ADMINISTRATOR . '/components/com_joomdoc/libraries/joomdoc/installer/installer.php');
	        AInstaller::install(JPATH_ADMINISTRATOR . '/components/com_joomdoc/extensions');
	    }
	    // current database prefix
	    $pr = $mainframe->getCfg('dbprefix');
	    // all tables in Joomla database
	    $joomla = $db->getTableList();
	    // all JoomDOC2 tables
	    $joomdoc = array($pr . 'joomdoc', $pr . 'joomdoc_groups', $pr . 'joomdoc_history', $pr . 'joomdoc_licenses', $pr . 'joomdoc_log');
	    // all JoomDOC2 tables in backup form
	    $joomdocBckp = array($pr . 'joomdoc2', $pr . 'joomdoc2_groups', $pr . 'joomdoc2_history', $pr . 'joomdoc2_licenses', $pr . 'joomdoc2_log');
	    // all DocMAN tables
	    $docman = array($pr . 'docman', $pr . 'docman_groups', $pr . 'docman_history', $pr . 'docman_licenses', $pr . 'docman_log');
	    echo '<div id="JoomDOCInstall">';
	    if ($installation) {
	        // introduction about JoomDOC3
	        echo '<h1>JoomDOC ' . $man['version'] . '</h1>';	        
	        echo '<p>';
            echo '<img src="' . JURI::root(true) . '/components/com_joomdoc/assets/images/icon-48-joomdoc.png" alt="" />';
            echo JText::_('JOOMDOC_DESC') . JText::_('JOOMDOC_INSTALL_INFO');
            echo '</p>';
	        echo '<h3><a href="' . JRoute::_('index.php?option=com_joomdoc') . '" title="">' . JText::_('JOOMDOC_OPEN') . '</a></h3>';            
	    }
	    $nothingToMigrate = true;
	    if (array_intersect($joomdoc, $joomla) == $joomdoc || array_intersect($joomdocBckp, $joomla) == $joomdocBckp) {
	        // JoomDOC2 was installed, prompt user to migrate data from JoomDOC2 into JoomDOC3
	        echo '<h2>' . JText::sprintf('JOOMDOC_JOOMDOC2_FOUND', $man['version']) . '</h2>';
	        echo '<img src="' . JURI::root(true) . '/components/com_joomdoc/assets/images/icon-48-joomdoc2.png" alt="" />';
	        echo JText::sprintf('JOOMDOC_JOOMDOC2_MIGRATION_INFO', $man['version'], $man['version'], $man['version']);
	        echo '<form id="joomdoc2import" name="joomdoc2import" method="post" action="index.php?option=com_joomdoc&amp;task=migration.run&amp;component=joomdoc">';
	        echo '<label for="docbase">' . JText::_('JOOMDOC_JOOMDOC2_MIGRATION_LABEL') . '</label>';
            echo '<div class="input-append">';
	        echo '<input type="text" name="docbase" id="docbase" value="" size="50" class="input-xxlarge" />';
	        echo '<button class="btn" onclick="if(this.form.docbase.value.trim()==\'\'){alert(\'' . JText::_('JOOMDOC_MIGRATION_ADD_DOCBASE', true) . '\');return false;}else{this.form.submit();}">' . JText::_('JOOMDOC_JOOMDOC2_MIGRATION_BUTTON') . '</button>';
            echo '</div>';
	        echo '</form>';
	        if ($installation) {
	            // rename JoomDOC2 tables because there is collision with JoomDOC3 - only during installation
	            $db->setQuery('RENAME TABLE #__joomdoc TO #__joomdoc2');
	            $db->query();
	            $db->setQuery('RENAME TABLE #__joomdoc_groups TO #__joomdoc2_groups');
	            $db->query();
	            $db->setQuery('RENAME TABLE #__joomdoc_history TO #__joomdoc2_history');
	            $db->query();
	            $db->setQuery('RENAME TABLE #__joomdoc_licenses TO #__joomdoc2_licenses');
	            $db->query();
	            $db->setQuery('RENAME TABLE #__joomdoc_log TO #__joomdoc2_log');
	            $db->query();
	            // run JoomDOC3 database installation again because some tables for JoomDOC3 are missing after renaming
	            $queries = JFile::read(JPATH_ADMINISTRATOR . '/components/com_joomdoc/sql/install.mysql.utf8.sql');
	            $queries = $db->splitSql($queries);
	            foreach ($queries as $query) {
	                $db->setQuery($query);
	                $db->query();
	            }
	        }
	        $nothingToMigrate = false;
	    }
	    if (array_intersect($docman, $joomla) == $docman) {
	        // DocMAN was installed, prompt user to migrate data from JoomDOC2 into JoomDOC3
	    	$dmcfg = JPath::clean(JPATH_ROOT . '/administrator/components/com_docman/docman.config.php');
			$dmpath = $dmver = '';
			if (JFile::exists($dmcfg)) {
				require_once $dmcfg;
				$dmcfg = new dmConfig();
				if (JString::strpos($dmcfg->dmpath, JPATH_ROOT) === 0)
					$dmpath = JString::substr($dmcfg->dmpath, JString::strlen(JPATH_ROOT) + 1);
				$dmver = $dmcfg->docman_version;
			}    	
	        echo '<h2>' . JText::sprintf('JOOMDOC_DOCMAN_FOUND', $dmver, $man['version']) . '</h2>';
	        echo '<img src="' . JURI::root(true) . '/components/com_joomdoc/assets/images/icon-48-docman.png" alt="" />';
	        echo JText::sprintf('JOOMDOC_DOCMAN_MIGRATION_INFO', $dmver, $man['version'], $dmver, $man['version'], $dmver, $man['version']);
	        echo '<form id="docmanimport" name="docmanimport" method="post" action="index.php?option=com_joomdoc&amp;task=migration.run&amp;component=docman">';
	        echo '<label for="docbase2">' . JText::sprintf('JOOMDOC_DOCMAN_MIGRATION_LABEL', $dmver) . '</label>';
            echo '<div class="input-append">';
	        echo '<input type="text" class="input-xxlarge" name="docbase" id="docbase2" value="' . htmlspecialchars($dmpath) . '" size="50" />';	        
            echo '<button class="btn" onclick="if(this.form.docbase.value.trim()==\'\'){alert(\'' . JText::_('JOOMDOC_MIGRATION_ADD_DOCBASE', true) . '\');return false;}else{this.form.submit();}">' . JText::_('JOOMDOC_DOCMAN_MIGRATION_BUTTON') . '</button>';
	        echo '</div>';
            echo '</form>';
	        $nothingToMigrate = false;
	    }
	    if (!$installation && $nothingToMigrate) {
	        $mainframe->enqueueMessage(JText::sprintf('JOOMDOC_MIGRATION_NOTHING_TO_DO', implode(', ', $docman), implode(', ', $joomdocBckp)), 'notice');
	    }
	    echo '</div>';
	}
	
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	public static function uninstall($parent)
	{
	    JLoader::register('AInstaller', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . 'installer.php');
	    AInstaller::uninstall(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'extensions');	
	}
	
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	public static function update($parent){}
	
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	public static function preflight($type, $parent)
	{

	}
	
    /**
     * Events after complete installation.
     * @param string $action
     * @param JInstallerComponent $installer
     */
	public static function postflight($action, $installer)
	{
		$db = JFactory::getDbo();
		// allow basic rules for each group
		$rule = array();
		$rule[] = 'core.enterfolder'; // show folder
		$rule[] = 'core.viewfileinfo'; // show file
		$rule[] = 'core.download'; // download file
		
		// load existing rules
		$query = $db->getQuery(true);
		$query->select('rules')->from('#__assets')->where("name = 'com_joomdoc'");
		$db->setQuery($query);
		$rules = $db->loadResult();
		
		$rules = json_decode($rules);
		
		// initialize with fresh installation
		if (!is_object($rules))
			$rules = new stdClass();
		
		foreach ($rule as $r)
			if (!isset($rules->$r))
				$rules->$r = new stdClass();
		
		// load all usergroups
		$query = $db->getQuery(true);
		$query->select('id')->from('#__usergroups');
		$db->setQuery($query);
		$userGroups = $db->loadColumn();
		
		// allow all rules for each usergroup
		foreach ($userGroups as $userGroup)
			foreach ($rule as $r)
				$rules->$r->$userGroup = 1;
		
		// save rules back
		$rules = json_encode($rules);
		$rules = $db->quote($rules);
		
		$query = $db->getQuery(true);
		$query->update('#__assets')->set("rules = $rules")->where("name = 'com_joomdoc'");
		$db->setQuery($query);
		$db->query();
	}
}
?>