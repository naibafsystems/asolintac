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

class JHtmlJoomDOC {
    
    const TOOLTIP_SELECTOR = '.hasTip';
    
    /**
     * Display icon tool to set item as default or non default.
     *
     * @param int $value value of item parameter default
     * @param int $i order number in list to identify row
     * @param string $entity name of entity
     * @param boolean $canChange icon is active or disabled
     * @return string HTML code
     */
    public static function defaults ($value, $i, $entity, $canChange) {
        $states[JOOMDOC_STATE_UNDEFAULT] = array('disabled.png', JoomDOCHelper::getTask($entity, JOOMDOC_TASK_DEFAULT), 'JOOMDOC_DEFAULT', 'JOOMDOC_TO_DEFAULT');
        $states[JOOMDOC_STATE_DEFAULT] = array('featured.png', JoomDOCHelper::getTask($entity, JOOMDOC_TASK_UNDEFAULT), 'JOOMDOC_UNDEFAULT', 'JOOMDOC_TO_UNDEFAULT');
        $state = JArrayHelper::getValue($states, $value, $states[JOOMDOC_STATE_DEFAULT]);
        $html = JHtml::_('image', 'admin/' . $state[0], JText::_($state[2], true), null, true);
        if ($canChange)
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" title="' . JText::_($state[3], true) . '">' . $html . '</a>';
        return $html;
    }

    /**
     * Parent documents (folders) tree list form field (select box).
     *
     * @param string $selected selected value
     * @param string $name field name param
     * @param string $id field id param
     * @return string HTML code
     */
    public static function parents ($selected, $name = 'joomdoc_parent', $id = 'joomdoc_parent', $realPaths = false) {
        $parents = JoomDOCFileSystem::getParents();
        $tree = array();
        // group parents and their children
        foreach ($parents as $parent) {
        	// this params require JHtml::_('select.genericlist'
            $parent->name = $parent->title;
            $parent->parent_id = $parent->parent;
            // ID and PARENT have to be numeric, cannot use path as array key, use CRC32 to get unique number for each path
            $parent->id = crc32($parent->path);
            // add item to parent group
            $tree[crc32($parent->parent)][] = $parent;
        }
        // make tree list
        $tree = JHtml::_('menu.treerecurse', 0, '', array(), $tree);
        // empty value
        $options = array(JHtml::_('select.option', '', JText::_('JOOMDOC_SEARCH_EVERYWHERE')));
        foreach ($tree as $list) {
            $options[] = JHtml::_('select.option', ($realPaths && !empty($list->full_alias) ? $list->full_alias : $list->path), $list->treename);
        }
        return JHtml::_('select.genericlist', $options, $name, 'autocomplete="off"', 'value', 'text', $selected, $id);
    }
    
    /**
     * Folders navigator with MooTree.
     * 
     * @param array $folders data in this format: JObject('ident' => 'relative path', 'route' => 'URL to open folder', 'title' => 'Folder name', 'entry' => 'Is accessible')
     * @param string $parent root folder 
     * @param bool $expandable if tree is hide-able (only for J2.5 admin layout)
     * @param bool $ajax generate one tree list only
     * @return string source of tree
     */
    public static function mooTree($folders = null, $parent = '', $expandable = false, $ajax = false) {
    	$config = JoomDOCConfig::getInstance();
    	$document = JFactory::getDocument();
    	$mainframe = JFactory::getApplication();
		$document->addScript(JURI::root(true) . '/media/system/js/mootree' . (JDEBUG ? '-uncompressed' : '') . '.js');
		$document->addStyleSheet(JURI::root(true) . '/media/system/css/mootree.css');
		$path = JoomDOCRequest::getPath();
		$code = '';
		if (!$ajax) {
			$code .= '<div id="MooTree" style="position: relative; overflow: visible;"></div>'; // here MooTree will draw tree
			$code .= '<ul id="MooTreeSrc">'; // start of source of tree
		}
		if (is_null($folders)) {
			$folders = JoomDOCFileSystem::getNonTrashedParents($parent, 1, ($ajax ? null : $path));
			if (empty($folders)) return ''; // nothing to do
			foreach ($folders as $i => $item) {
				$folder = new JObject();
				$folder->set('ident', $item);
				$folder->set('route', JoomDOCRoute::viewDocuments($item));
				$folder->set('title', basename($item));
				$folder->set('entry', JoomDOCAccessFileSystem::enterFolder(false, $item));
				$folders[$i] = $folder;
			}
		}
		
		foreach ($folders as $i => $folder) { // all folders into deep
			$currentLevel = count(explode(DIRECTORY_SEPARATOR, $folder->get('ident'))); // level of folder deep (folders in doc root have 1)
			if (!empty($lastLevel)) { // not for root folder
				if ($currentLevel > $lastLevel) $code .= '<ul>'; // it's subfolder of previous folder
				elseif ($currentLevel < $lastLevel){
					$code .= str_repeat('</li></ul>', $lastLevel - $currentLevel).'</li>'; // end of subfolder, close previous subfolders
				}
				else $code .= '</li>'; // at the same level as previous
			}
			if ($folder->get('entry'))
				$code .= '<li id="' . str_replace(DIRECTORY_SEPARATOR, '-', $folder->get('ident')) . '"' . ($folder->get('ident') == $path ? ' class="selected"' : '') . '>
						<a href="' . JRoute::_($folder->get('route')) . '" target="folderframe" name="' . $folder->get('ident') . '">' . $folder->get('title') . '</a>'; // current item, tag leave open to append subfolder	
			$lastLevel = $currentLevel;
		}
		if (empty($lastLevel))
			$lastLevel = 0;
		$code .= str_repeat('</li></ul>', $lastLevel); // end of source tree
		
		if ($ajax) {
			ob_clean();
			die($code);
		}
		// start MooTree after completely loading page
		$js = "
			window.addEvent('domready', function() {
				var tree = new MooTreeControl(
				{
					div: 'MooTree', 
					mode: 'folders', 
					grid: true, 
					theme: '" . htmlspecialchars(JURI::root(true), ENT_QUOTES) . "/media/system/images/mootree.gif', 
					loader: { // set up Ajax loader
						icon: null, 
						text: '" . JText::_('JOOMDOC_LOADING', true) . "', 
						color:'#FF0000'
    				},
					onClick: function(node) { // event after click on folder
						window.location = node.data.url; 
					},
					onExpand: function(node, state) { // event after expand tree node
						if (state && treeLoading < 0) { // opening
							node.loading = true;
							node.clear(); // clear the node to append loader
							node.insert(node.control.loader); // insert loader into the node
							new Request({
								method: 'GET',
								url: '" . JRoute::_(JoomDOCRoute::updatemootree(), false) . "',
								data: {
									path: node.data.name
								},
								onSuccess: function(html, xml) {
									node.clear(); // clear loader from the node
									node.loading = false;
									var ul = new Element('ul#MooTreeSrc');
									ul.set('html', html);
									$$('body').adopt(ul);
									tree.adopt('MooTreeSrc', node);
								}
							}).send();
						}
						treeLoading --;
					}
				},
				{
					text: '" . ($parent ? $parent : $config->explorerRoot) . "', 
				 	open: true, 
				 	data: {
						url: '" . JRoute::_(JoomDOCRoute::viewDocuments($mainframe->isSite() ? $parent : 'JoomDOC'), false) . "', 
						target: 'folderframe'
    				}
    			}
			);
			tree.adopt('MooTreeSrc'); // load source in ul structure
			tree.selected = tree.get('node_" . htmlspecialchars(str_replace(DIRECTORY_SEPARATOR, '-', $path), ENT_QUOTES) . "');
		";
		
		//make current folder highlighted
		if ($path) {
			$toSelect = JoomDOCFileSystem::getFullPath($path);
			if (JFile::exists($toSelect))
				$toSelect = JoomDOCFileSystem::getParentPath($toSelect); // select parent folder if file is selected
			$toSelect = JoomDOCFileSystem::getRelativePath($toSelect);
			$js .= "
				tree.get('node_" . htmlspecialchars(str_replace(DIRECTORY_SEPARATOR, '-', $toSelect), ENT_QUOTES) . "').selected = true;
			";
		}
		
		// open actual folder path
		$breadCrumbs = JoomDOCFileSystem::getPathBreadCrumbs($path);

		foreach ($breadCrumbs as $i => $breadCrumb) {
			if ($i) {
				$js .= "
					var node = tree.get('node_" . htmlspecialchars(str_replace(DIRECTORY_SEPARATOR, '-', $breadCrumb->path), ENT_QUOTES) . "');
					if (node) node.toggle(false, true);
				";
			}
		}
		$js .= '});
			var treeLoading = ' . (count($breadCrumbs) - 2) . ';';
		
		//tree expandable - only in admin view and J2.5. In joomla 3.0, tree is fixed part of sidebar
		if ($expandable){
                $js .= "window.addEvent('domready', function() {

                			//add show/hide button to pathway
                			els = Elements.from('<span class=\"hideMooTree\">".JText::_('JOOMDOC_SHOW_HIDE_TREE')."</span>');
                			els.inject($('pathway'), 'bottom');

                			//add own wrapper for fx.Slide, so we can set float and wifth by css
                			myWrapper = new Element('div', {id: 'MooTreeWrap'}).wraps($('MooTree'), 'top');
                		
                			//create fx.Slide instance and store it inside element 'storage' (MooTools)
                			$('MooTree').store('slide', new Fx.Slide($('MooTree'), {
                				mode: 'horizontal', 
                				duration: 1000, 
                				transition: Fx.Transitions.Pow.easeOut, 
                				resetHeight: true, 
    							wrapper: myWrapper}).show());

                			//hide MooTree, if stored so in HTML5 localstorage
                			if (window.localStorage && localStorage.getItem('hidden')=='true')
								$('MooTree').retrieve('slide').hide();

                			//add event to remember opened/closed state in localStorage
                            els.addEvent('click', function() {
	
                				if ($('MooTree').retrieve('slide').open){ 
                					$('MooTree').retrieve('slide').slideOut();
                					localStorage.setItem('hidden', 'true'); } 
                				else {
                					$('MooTree').retrieve('slide').slideIn();
                					localStorage.setItem('hidden', 'false');}

                            });
                					
                        });";
                
		}

		$document->addScriptDeclaration($js);
		return $code;
    }
    
    //next functions are for easier adding tabs/slides betwen versions J2.5 and J3.0
    //although J3.0 also supports tabs.* syntax, but better use bootstrap, its nicer and compatible with mobile devices, etc.
    public static function startTabs($tabGroup, $active = null)
    {
		if (JOOMDOC_ISJ3) {
            JHtml::_('behavior.tabstate');
            if (JFactory::getApplication()->isSite()) {
                JHtml::_('bootstrap.loadcss', true, JFactory::getDocument()->getDirection());
            }
			return JHtml::_('bootstrap.startTabSet', $tabGroup, array('active' => $active));
        } else
			return JHtml::_('tabs.start', $tabGroup, array('useCookie' => true));
    }
    
    public static function addTab($label, $id, $tabGroup)
    {
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.addTab', $tabGroup, $id, JText::_($label, true));
    	else
    		return JHtml::_('tabs.panel', JText::_($label), $id);
    }
    
    public static function endTab()
    {
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.endTab');
    	
    	//no end : start of next is end of prev

    }
    
    public static function endTabs()
    {
	    if (JOOMDOC_ISJ3)
	    	return JHtml::_('bootstrap.endTabSet');
	    else
	    	return JHtml::_('tabs.end');
    }
    
    public static function startSliders($slidersId, $active)
    {    	
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.startAccordion', $slidersId, array('active' => $active));
    	else {
    		jimport('joomla.html.html.sliders' );
    		return JHtml::_('sliders.start', $slidersId, array('useCookie' => 1));
    	}
    }
    
    public static function addSlide($slidersId, $label, $id)
    {
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.addSlide', $slidersId, JText::_($label), $id);
    	else
    		return JHtml::_('sliders.panel', JText::_($label), $id);
    }
    
    public static function endSlide()
    {
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.endSlide');
    }
    
    public static function endSlides()
    {
    	if (JOOMDOC_ISJ3)
    		return JHtml::_('bootstrap.endAccordion');
    	else
    		return JHtml::_('sliders.end');
    }
    
    /**
     * Filter folder list for frontend (published, ACL etc)
     * 
     * @param JoomDOCFolder $root
     * @param string $parent
     * @return array
     */
    public static function folders($root, $parent)
    {
    	$folders = array();
    	$root->initIteration();
    	$parents = array(JoomDOCFileSystem::getRelativePath($parent));
    	while ($root->hasNext()) {
    		$item = $root->getNext();
    		$access = new JoomDOCAccessHelper($item);
    		$itemParent = JoomDOCFileSystem::getParentPath($access->relativePath);
    		if (!(empty($itemParent) || in_array($itemParent, $parents))) // parent has to be visible
    			continue;
    		if ($access->docid && $item->document->published == JOOMDOC_STATE_UNPUBLISHED) // item has to published
    			continue;
    		$folder = new JObject(); // prepare data for MooTree
    		$folder->set('ident', $access->relativePath);
    		$folder->set('route', JoomDOCRoute::viewDocuments($access->relativePath, $access->alias));
    		$folder->set('title', $access->docid ? $item->document->title : $item->getFileName());
    		$folder->set('entry', $access->canEnterFolder);
    		$folders[] = $folder;
    		$parents[] = $access->relativePath; // save into visible parents
    	}
    	return $folders;
    }
    
    /**
     * Setup Joomla tool tip for some HTML element.
     * 
     * @param string $el element ID in HTML
     * @param string $ttl tool tip title
     * @param string $txt tool tip text
     */
    public static function  tooltip($el, $ttl, $txt) 
    {        	
    	$ttl = JText::_($ttl, true);
    	$txt = JText::_($txt, true);
    	$el  = addslashes($el);
    	
        if (version_compare(JVERSION, '3.3', '<')){
            JFactory::getDocument()->addScriptDeclaration("
                window.addEvent('domready', function() {
                    JoomDOC.mTip('$el', '$ttl', '$txt');
                });
            ");
        } else {
            JFactory::getDocument()->addScriptDeclaration("
                jQuery(document).ready(function() { 
                    JoomDOC.jTip('$el', '$ttl', '$txt');
                });
            ");
        }
    }
    
    /**
     * Show document field value on frontend
     * 
     * @param stdClass $field
     * @param stdClass $document
     * @return string
     */
    public static function  showfield($field, $document) {
    	$name = 'field' . $field->id;
    	
    	if (isset($document->$name)) {
    		$value = JString::trim($document->$name);
    		
    		switch ($field->type) {
    			case JOOMDOC_FIELD_TEXT:
    				return $value;
    		
    			case JOOMDOC_FIELD_TEXTAREA:
    				return $value ? nl2br($value) : '';
    		
    			case JOOMDOC_FIELD_EDITOR:
    				return $value ? JoomDOCHelper::applyContentPlugins($value) : '';
    	
    			case JOOMDOC_FIELD_DATE:
    				switch($value) {
    				 	case '':    				 	
    					case '0000-00-00':
    					case '0000-00-00 00:00:00':
    						return '';
    					default:
    						return JHtml::date($value, JText::_('DATE_FORMAT_LC4'));
    				}
    	
    			case JOOMDOC_FIELD_RADIO:
    				switch($value) {
    					case '0':
    						return JText::_('JNO'); 
    					case '1':
    						return JText::_('JYES');
    					default:
    						return '';
    				}
    				
    			case JOOMDOC_FIELD_CHECKBOX:
    			case JOOMDOC_FIELD_MULTI_SELECT:
                case JOOMDOC_FIELD_SUGGEST:
    				$registry = new JRegistry($value);
    				$array = $registry->toArray();
    				$data = array();
    				if (is_array($array))
    					foreach ($array as $var => $val)
    						foreach ($field->options as $option)
    							if ($option->value == $val && JString::trim($option->label))
    								$data[] = $option->label;
    				return implode(', ', $data);
    				
    			case JOOMDOC_FIELD_SELECT:
    				foreach ($field->options as $option)
    					if ($option->value == $value && JString::trim($option->label))
    						return $option->label;
    		}
    	}
    	return '';
    }
    
    /**
     * Patch for Joomla! 3.3. Use standard format of tooltip (title::text) which is in J!3.3 deprecated.
     */
    public static function behaviortooltip() {
        if (version_compare(JVERSION, '3.3.0', '>=')) {
            JFactory::getDocument()->addScriptDeclaration("
                jQuery(document).ready(function() {
                    JoomDOC.bTip('" . self::TOOLTIP_SELECTOR . "')
                });        
            ");
            JHTML::_('bootstrap.tooltip', self::TOOLTIP_SELECTOR);
        } else {
            JHTML::_('behavior.tooltip');
        }
    }
    
    /**
     * Setup jQuery autocomplete suggest for input text custom field.
     * 
     * @param string $inputId id parameter of HTML form text input
     * @param int $fieldId id of JoomDOC custom field
     */
    public static function suggest($inputId, $fieldId) {
        if (JOOMDOC_ISJ3) {
            JHtml::_('jquery.framework');
        } else {
            JHTML::script('components/com_joomdoc/assets/jquery-ui/jquery' . (JDEBUG ? '' : '.min' ) . '.js');
            JHTML::script('components/com_joomdoc/assets/jquery-ui/jquery-noconflict.js');
        }
        JHTML::script('components/com_joomdoc/assets/jquery-ui/jquery-ui' . (JDEBUG ? '' : '.min') . '.js');
        JHTML::stylesheet('components/com_joomdoc/assets/jquery-ui/jquery-ui' . (JDEBUG ? '' : '.min') . '.css');

        JFactory::getDocument()->addScriptDeclaration("
            jQuery(document).ready(function() {
                jQuery('select#" . $inputId . "').autocomplete({
                    source: '" . JRoute::_('index.php?option=com_joomdoc&task=field.suggest&field=' . $fieldId, false) . "',
                });
            });");
    }
    
    /**
     * Load jQuery chosen for selectbox. With J!3 use standard Joomla framework.
     * 
     * @param string $id selectbox ID
     * @param boolean $j3 load J!3 framework
     */
    public function chosen($id, $j3 = true) {
        if (JOOMDOC_ISJ3) {
            if ($j3) {
                JHtml::_('formbehavior.chosen', 'select#' . $id);
            }
        } else {
            JHtml::stylesheet('components/com_joomdoc/assets/jquery-ui/chosen.css');
            JHTML::script('components/com_joomdoc/assets/jquery-ui/jquery' . (JDEBUG ? '' : '.min' ) . '.js');
            JHTML::script('components/com_joomdoc/assets/jquery-ui/jquery-noconflict.js');
            JHtml::script('components/com_joomdoc/assets/jquery-ui/chosen.jquery' . (JDEBUG ? '' : '.min' ) . '.js');
            JFactory::getDocument()->addScriptDeclaration("
                (function($){
                    $(document).ready(function () {                
                        $('select#" . $id . "').chosen({
                            'disable_search_threshold': 10,
                            'allow_single_deselect': true,
                            'placeholder_text_multiple': '".JText::_('JOOMDOC_FIELD_SUGGEST_SOME_OPTIONS', true)."',
                            'placeholder_text_single': '".JText::_('JOOMDOC_FIELD_SUGGEST_SELECT_AN_OPTION', true)."',
                            'no_results_text': '".JText::_('JOOMDOC_FIELD_SUGGEST_NO_RESULTS_MATCH', true)."'
                        });
                    });
                })(jQuery);                    
            ");
        }
    }
    
    /**
     * Upload files by drop & drag.
     * 
     * @param string $folderPath folder path to upload in
     */   
    public static function dropAndDrag($folderPath) {
        $document = JFactory::getDocument();
        /* @var $document JDocumentHTML */
        if (!JOOMDOC_ISJ3) {
            $document->addScript(JOOMDOC_ASSETS . 'jquery-ui/jquery' . (JDEBUG ? '' : '.min' ) . '.js?1.11.2');
            $document->addScript(JOOMDOC_ASSETS . 'jquery-ui/jquery-noconflict.js');
        }
        $document->addScript(JOOMDOC_ASSETS . 'js/dropzone.js?4.2.0');    
        $document->addScriptDeclaration('
jQuery(document).ready(function() {
    JoomDOC.dropAndDrag("' . JRoute::_('index.php?option=' . JOOMDOC_OPTION . '&view=' . JOOMDOC_DOCUMENTS . '&task=' . JoomDOCHelper::getTask(JOOMDOC_DOCUMENTS, JOOMDOC_TASK_UPLOADFILE) . '&path=' . $folderPath, false) . '", "'.JOOMDOC_IMAGES.'preloader.gif");
});        
');
    }
}

?>