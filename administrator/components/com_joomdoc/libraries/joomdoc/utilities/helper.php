<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

class JoomDOCHelper {

    /**
     * Get current logged user allowed actions.
     *
     * @return JObject
     */
    public static function getActions ($section) {
        $user = JFactory::getUser();
        /* @var $user JUser */
        $result = new JObject();
        foreach (get_defined_constants() as $key => $value)
            if (JString::strpos($key, 'JOOMDOC_CORE_') === 0)
                $result->set($value, $user->authorise($value, JoomDOCHelper::getAction($section)));
        return $result;
    }

    /**
     * Get task parameter format.
     *
     * @param string $entity entity name
     * @param string $task task name
     * @return string
     */
    public static function getTask ($entity, $task) {
        return $entity . '.' . $task;
    }

    /**
     * Get access action format.
     *
     * @param string $section access section name
     * @return string
     */
    public static function getAction ($section) {
        return JOOMDOC_OPTION . '.' . $section;
    }

    /**
     * Set component submenu.
     *
     * @param string $view opened view page
     * @return void
     */
    public static function setSubmenu ($view = JOOMDOC_JOOMDOC, $asJ3Sidebar = false) {
    	$config = JoomDOCConfig::getInstance();
    	if ($asJ3Sidebar AND JOOMDOC_ISJ3){ //J3: if we want to add own things to sidebar, we need to set up own html grid-12, so build own left submenu, which we will display
			$object = 'JHtmlSidebar';
    	}
    	else {
    		//else (if j3), its added to default submenu which is displayed as left sidebar automatically
    		$object = 'JSubMenuHelper';
    	}
    	
    	$object::addEntry(JText::_('JOOMDOC_CONTROL_PANEL'), JoomDOCRoute::viewJoomDOC(), $view == JOOMDOC_JOOMDOC);
    	$object::addEntry(JText::_('JOOMDOC_DOCUMENTS'), JoomDOCRoute::viewDocuments(), $view == JOOMDOC_DOCUMENTS);
    	$object::addEntry(JText::_('JOOMDOC_LICENSES'), JoomDOCRoute::viewLicenses(), $view == JOOMDOC_LICENSES);
    	if (JFactory::getUser()->authorise('core.managafields', 'com_joomdoc'))
    		$object::addEntry(JText::_('JOOMDOC_FIELDS'), JoomDOCRoute::viewFields(), $view == JOOMDOC_FIELDS);
    	$object::addEntry(JText::_('JOOMDOC_UPGRADE'), JoomDOCRoute::viewUpgrade(), $view == JOOMDOC_UPGRADE);
    	$object::addEntry(JText::_('JOOMDOC_MIGRATION'), JoomDOCRoute::viewMigration(), $view == JOOMDOC_MIGRATION);
    	    }

    /**
     * Display number in human readable format without wrap.
     *
     * @param int $number
     * @return string
     */
    public static function number ($number) {
        return JoomDOCHelper::nowrap(number_format((int) $number, 0, '', JText::_('JOOMDOC_THOUSANDS_SEPARATOR')));
    }

    /**
     * Display date uploade in human readable format without wrap.
     *
     * @param int $date unix timestamp
     * @return string
     */
    public static function uploaded ($date, $timestamp = true) {
        if (!$timestamp && $date == '0000-00-00 00:00:00')
            return '';
        return JoomDOCHelper::nowrap(JHtml::date($timestamp ? date('Y-m-d H:i:s', $date) : $date, JText::_('JOOMDOC_UPLOADED_DATE_J16')));
    }

    /**
     * Make text nowrap.
     *
     * @param string $text
     * @return string
     */
    public static function nowrap ($text) {
        return '<span class="nowrap">' . $text . '</span>';
    }

    /**
     * Get CSS class name for file extension icon.
     *
     * @param string $filename 	file name or 'folder'
     * @param string $theme 	icon theme. if not provided, frontend configuration is used
     * @param int $size 		icon size
     * 
     * @return string CSS class name for icon, empty string if icon is not available
     */
    public static function getFileIconClass ($filename, $theme = null, $size = 32) {
    	
    	if (!$theme){
    		$config = JoomDOCConfig::getInstance();
    		/* @var $config JoomDOCConfig */
    		$theme = $config->iconTheme;
    	}
    	
        static $cache = array();
        static $generals;
        static $declared = array();
        
        if (!isset($generals)){ //some icons can have "general name", for example "24-audio.png", use this array to map it to extensions (see Gion etc)
	        
	        $general = array(
	        	'archive' => 'tar,gzip,zip,rar', 
	        	'audio' => 'wav,ogg,mp3,flac',
	        	'video' => 'mpg,mpeg,avi,flv,3gp,mov',
	        	'image' => 'ai,eps,jpg,jpeg,gif,png,bmp',
	        	'doc' => 'doc,docx,rtf,odt,txt',
	        	'xls' => 'xls,xlsx,csv,ods',
	        	'htm' => 'htm,html',
	        	'txt' => 'txt,ini'
	        );
	        
	        $generals = array();
	        foreach ($general as $genName => $exts)
	        	foreach (explode(',',$exts) AS $ext)
	        		$generals[$ext] = $genName;
        }

        
        if (!isset($cache[$theme])){

            // EQ: /components/com_joomdoc/assets/images/icons/default
            $cache[$theme][$size] = array();
            $filesCache[$theme] = array();
            
            $iconsFolder = JOOMDOC_PATH_ICONS . DIRECTORY_SEPARATOR . $theme;
            if (JFolder::exists($iconsFolder)) {
                $icons = JFolder::files($iconsFolder, '\.(png|gif)$');
                foreach ($icons as $icon) {

                    /* Parse size and extensions from file name.
                    	 EQ: "16-ai-eps-jpg-gif-png.png", can be also "16-folder", "16-audio", etc. */

                    if (preg_match_all('/([0-9]+)|(\-([a-z0-9]+))/', $icon, $match)) {
                    	// Icon size. EQ: 16
                        $iconSize = $match[1][0];
 
                        // Extensions EQ: ai,eps,jpg,gif,png
                        $exts = array_splice($match[3], 1);

                        // Store to cache. we will create classname when really needed with optinal resize below.
                        $classname = implode('-', $exts);
                        foreach ($exts as $ext) 
                            $cache[$theme][$ext][$iconSize] = array($classname, JOOMDOC_ICONS . $theme . '/' . $icon);
                    }
                }
            }
        }
        
        $extension = $filename=='folder' ? 'folder' : JFile::getExt($filename);

        $pickedExtension = false;
        
        //pick icon for this extension
        if (isset($cache[$theme][$extension])) //by exact extension specified
        	$pickedExtension = $extension;

        if (!$pickedExtension AND isset($generals[$extension])){
        	
        	//ok, try "general" icons array
        	if (isset($cache[$theme][$generals[$extension]]))
        		$pickedExtension = $generals[$extension];

        	//still nothing, try to find similar icon based on general array (for example use "doc" icon for "txt" file)
        	if (!$pickedExtension)
        		foreach ($generals as $genExt => $genName)
        			if ($genName == $generals[$extension] AND isset($cache[$theme][$generals[$genExt]]))
        				$pickedExtension = $cache[$theme][$generals[$genExt]];
        }
        
        if (!$pickedExtension) //no icon for this filetype
        	return '';
        
        //ok, we have icon, now pick proper size
		if (!isset($cache[$theme][$pickedExtension][$size])){ //o-ou, this size missing
			$pickedSize = false;
			ksort($cache[$theme][$pickedExtension]); //sort from smallest
			foreach ($cache[$theme][$pickedExtension] as $iconSize => $icon)
				if ($iconSize > $size){ //pick closest bigger icon
					$pickedSize = $iconSize; break;}
			if (!$pickedSize){ //bigger not exists
				krsort($cache[$theme][$pickedExtension]);
				foreach ($cache[$theme][$pickedExtension] as $iconSize => $icon)
					if ($iconSize < $size){ //pick closest smaller icon
						$pickedSize = $iconSize; break;}
			}
		}
		else
			$pickedSize = $size;
		
		if (!$pickedSize) //no size, this is weird..
			return '';
		
		$classname = 'joomdoc-'.$pickedSize.'-'.$cache[$theme][$pickedExtension][$pickedSize][0];
		
		if (empty($declared[$classname])){
			$backgroundSize = $pickedSize != $size ? 'background-size: '.$size.'px '.$size.'px;' : ''; //add background-size property, if icon is different size (works in all browsers and IE>=9)
			$document = JFactory::getDocument();
			/* Icon CSS style declaration.
			 EQ: .joomdoc-16-ai-eps-jpg-gif-png { background-image: url("/components/com_joomdoc/assets/images/icons/default/16-ai-eps-jpg-gif-png.png"); } */
			$document->addStyleDeclaration('.' . $classname . "\t".' { background-image: url("' . $cache[$theme][$pickedExtension][$pickedSize][1] . '") !important; '.$backgroundSize.'}'."\r\n");
			$declared[$classname] = true;
		}
		
		return $classname;
    }

    /**
     * Crop text into set length. Before strip HTML tags. After croping add on end ender string (e.q. ...).
     * If text is shorter then legth return text without ender string. Crop end with number or letter. No by chars like: , . - _ etc.
     *
     * @param string $text   string to crop
     * @param int    $length crop length
     * @param string $ender  ender string, default ...
     * @return string
     */
    public static function crop ($text, $length, $ender = '...') {
        $text = strip_tags($text);
        $text = JString::trim($text);
        $strlen = JString::strlen($text);
        if ($strlen <= $length)
            return $text;
        $lastSpace = JString::strpos($text, ' ', $length);
        if ($lastSpace === false) {
            $crop = JString::substr($text, 0, $length);
        } else {
            $crop = JString::substr($text, 0, $lastSpace);
        }
        static $noLetters;
        if (is_null($noLetters)) {
            $noLetters = array('.', ',', '-', '_', '!', '?', '(', ')', '%', '', '@', '#', '$', '^', '&', '*', '+', '=', '"', '\'', '/', '\\', '§', '<', '>', ':', '{', '}', '[', ']');
        }
        do {
            $strlen = JString::strlen($crop);
            $lastChar = JString::substr($crop, ($strlen - 1), 1);
            if (in_array($lastChar, $noLetters)) {
                $crop = JString::substr($crop, 0, ($strlen - 1));
            } else {
                break;
            }
        } while (true);
        $crop = JString::trim($crop);
        if ($crop)
            $crop .= $ender;
        return $crop;
    }

    /**
     * Convert array to javascript array string and put into html head.
     *
     * @param string $name name of javascript array
     * @param array $items array items, are inserted as string safed with addslashes method
     * @return void
     */
    public static function jsArray ($name, $items) {
        $js = 'var ' . $name . ' = new Array(';
        if (count($items)) {
            $items = array_map('addslashes', $items);
            $js .= '"' . implode('", "', $items) . '"';
        }
        $js .= ');';
        $document = JFactory::getDocument();
        /* @var $document JDocumentHTML */
        $document->addScriptDeclaration($js);
    }

    /**
     * Get meta description text format. Clean text and crop to 150 characters.
     *
     * @param string $text
     * @return string
     */
    public static function getMetaDescriptions ($text) {
        $text = JFilterOutput::cleanText($text);
        $text = JString::trim($text);
        if (JString::strlen($text) <= 150) {
            return $text;
        }
        $text = JString::substr($text, 0, 150);
        $lastFullStop = JString::strrpos($text, '.');
        if ($lastFullStop !== false) {
            $text = JString::substr($text, 0, $lastFullStop + 1);
        }
        $text = JString::trim($text);
        return $text;
    }

    /**
     * Add after title sitename to complet page title.
     *
     * @param string $title
     * @return string
     */
    public static function getCompletTitle ($title) {
        $app = JFactory::getApplication();
        $spt = $app->getCfg('sitename_pagetitles');
        if ($spt == 1) {
            return JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($spt == 2) {
            return JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        return $title;
    }

    /**
     * Get first no empty item from array.
     *
     * @param array $array
     * @return mixed
     */
    public static function getFirstNoEmpty (&$array) {
        foreach ($array as $item) {
            if (!empty($item)) {
                return $item;
            }
        }
        return '';
    }

    /**
     * Cleanup array. Unset or empty items.
     *
     * @param array $array
     */
    public static function cleanArray (&$array) {
        foreach ($array as $key => $item) {
            if (empty($item)) {
                unset($array[$key]);
            }
        }
    }

    /**
     * Return boolean mark if is possible to display item modified date.
     * Modified cannot be emty and cannot be the same as created date.
     *
     * @param string $created created date in database format in GMT0
     * @param string $modified modified date in database format in GMT0
     * @return booelan
     */
    public static function canViewModified ($created, $modified) {
        switch (JString::strtoupper(JString::trim($modified))) {
            case JString::strtoupper(JString::trim($created)):
            case JFactory::getDbo()->getNullDate():
            case '':
                return false;
        }
        return true;
    }

    /**
     * Get document asset format.
     * For example document ID is 7:
     * return com_joomdoc.document.7
     *
     * @param int $docid document ID
     * @return string
     */
    public static function getDocumentAsset ($docid) {
        return sprintf('%s.%s.%d', JOOMDOC_OPTION, JOOMDOC_DOCUMENT, $docid);
    }

    /**
     * Test if given object has property named document with property id.
     *
     * @param stdClass $object
     * @return mixed document ID if found or null
     */
    public static function getDocumentID ($object) {
        if (isset($object->document))
            return $object->document->id;
        return null;
    }

    /**
     * Test if given object has property named document with property full_alias.
     *
     * @param stdClass $object
     * @return mixed document alias if found or null
     */
    public static function getDocumentAlias (&$object) {
        if (isset($object->document->full_alias))
            return $object->document->full_alias;
        elseif (isset($object->full_alias))
            return $object->full_alias;
        return null;
    }
    
    /**
     * Get file extension of given item
     * 
     * @param mixed $item file/folder instance
     * @return string extension name
     */
    public static function getFileType (&$item) {
   
        if( !preg_match('/\./', $item) ) return '';
        
	return preg_replace('/^.*\./', '', $item);
        
    }
    
    /**
     * Get maximum, published File Version by Path.
     *
     * @param string $path relativ Path
     * @return int
     */
    public static function getMaxVersion ($path) {
        
        $db = JFactory::getDbo();
        $query = 'SELECT MAX(`version`) FROM `#__joomdoc_file` WHERE `path` = ' . $db->quote($path) . ' AND `state` = ' . JOOMDOC_STATE_PUBLISHED . ' GROUP BY `path`';
        $db->setQuery($query);
        return (int) $db->loadResult();
    }
    
    /**
     * Check if item is checked.
     *
     * @param stdClass $object
     * @return boolean
     */
    public static function isChecked (&$object) {
        if (isset($object->document)) {
            $document =& $object->document;
        } else {
            $document =& $object;
        }
        if (isset($document->checked_out)) {
            return $document->checked_out != 0 && $document->checked_out == JFactory::getUser()->id;
        }
        return false;
    }

    public static function showLog () {
        $keywords = array('SELECT', 'MIN', '<br/>FROM', '<br/>WHERE', '<br/>GROUP BY', 'SUM', '<br/>LEFT JOIN', 'ASC', 'DESC', 'AS', 'ON', 'IS NULL', 'AND', '<br/>ORDER BY', 'OR', '<br/>LIMIT', 'IN', 'MAX', '(', ')', '`', '\'', '.', ',');
        foreach ($keywords as $i => $keyword) {
            $keywordsReplaces[] = '<strong>' . $keyword . '</strong>';
            $keywords[$i] = strip_tags($keyword);
        }

        $keywords[] = '#__';
        $keywordsReplaces[] = JFactory::getDbo()->getPrefix();

        foreach (JFactory::getDbo()->getLog() as $key => $query) {
            $query = preg_replace('/`([^`]*)`/', '`<span style="color: blue">$1</span>`', $query);
            $query = preg_replace('/\'([^\']*)\'/', '\'<span style="color: green">$1</span>\'', $query);
            $query = preg_replace('/(\d+)/', '<span style="color: red">$1</span>', $query);
            $query = str_replace($keywords, $keywordsReplaces, $query);
            echo '<p>' . $query . '</p>';
        }
    }
    
    /**
     * Get count of trashed items.
     * 
     * @return int Count of trashed files
     */
    public static function trashedItemsCount () {
        return JFactory::getDbo()->setQuery('SELECT COUNT(*) FROM `#__joomdoc_flat` WHERE `state` = ' . JOOMDOC_STATE_TRASHED)->loadResult();
    }
    
    /**
     * Show mainframe message with information about set clipboard operation.
     */
    public static function clipboardInfo () {
        $operation = JoomDOCFileSystem::getOperation();
        $paths = JoomDOCFileSystem::getOperationPaths();
        if (!is_null($operation) && count($paths)) {
            $mainframe = JFactory::getApplication();
            /* @var $mainframe JApplication */
            $msg = 'JOOMDOC_CPMV_INFO_' . JString::strtoupper($operation);
            $paths = implode(', ', $paths);
            $mainframe->enqueueMessage(JText::sprintf($msg, $paths));
        }
    }

    /**
     * Show mainframe message if current folder is writable.
     *
     * @param string $path absolute path
     */
    public static function folderInfo ($path) {
        if (!is_writable($path)) {
            $mainframe = JFactory::getApplication();
            /* @var $mainframe JApplication */
            $mainframe->enqueueMessage(JText::_('JOOMDOC_FOLDER_UNWRITABLE'), 'notice');
        }
    }
    /**
     * Apply content plugins to document's description.
     *
     * @param string $text
     * @return string
     */
    public static function applyContentPlugins ($text, $path = null, $edocs = null) {
        static $dispatcher, $article, $params;
        if ($path && $edocs) {
            if ($edocs == 1) {
                $text = '{edocs}'.  $path . '{/edocs}' . $text;
            } elseif ($edocs == 2) {
                $text .= '{edocs}'.  $path . '{/edocs}';
            }
        }
        if (is_null($dispatcher)) {
            $dispatcher = JDispatcher::getInstance();
            /* @var $dispatcher JDispatcher */
            JPluginHelper::importPlugin('content');
            $article = new JObject();
            $params = new JRegistry();
        }
        $article->text = $text;
        $dispatcher->trigger('onContentPrepare', array('com_joomdoc.document', &$article, &$params, 0));
        
        return $article->text;
    }

    public static function license ($path) {
        static $model;
        if (is_null($model)) {
            $model = JModelLegacy::getInstance(JOOMDOC_LICENSE, JOOMDOC_MODEL_PREFIX);
            /* @var $model JoomDOCModelLicense */
        }
        $license = $model->license($path);
        return $license;
    }
    
    }
?>