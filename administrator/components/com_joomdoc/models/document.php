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

jimport('joomla.application.component.modeladmin');

class JoomDOCModelDocument extends JModelAdmin {

    /**
     * Path parents.
     *
     * @var array of JObjects with two params: parentPath (last parent path with document), subparentPath (last parent path), documentID
     */
    public $parents;

    function __construct ($config) {
        parent::__construct();
        $this->option = JOOMDOC_OPTION;
        $this->name = $this->getName();
        $this->setState($this->getName() . '.id', JRequest::getInt('id'));
        $this->checkin();
    }

    /**
     * @var string The prefix to use with controller messages.
     * @since	1.6
     */
    protected $text_prefix = JOOMDOC_OPTION;

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record a record object.
     * @return boolean True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete ($record) {
        return JoomDOCAccessDocument::delete($record->id);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param JForm $record a record object.
     * @return boolean True if allowed to change the state of the record. Defaults to the permission set in the component.
     */
    protected function canEditState ($record) {
        if (!empty($record)) {
            if ($record instanceof JForm)
                return JoomDOCAccessDocument::editState($record->getValue('id'), $record->getValue('checked_out'), $record->getValue('path'));
            else
                return JoomDOCAccessDocument::editState($record->id, $record->checked_out, $record->path);
        }
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param JTable A JTable object.
     * @return void
     */
    protected function prepareTable ($table) {
        if ($table->state == 1 && intval($table->publish_up) == 0)
            $table->publish_up = JFactory::getDate()->toSql();
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param type The table type to instantiate
     * @param string A prefix for the table class name. Optional.
     * @param array Configuration array for model. Optional.
     * @return JTable A database object
     */
    public function getTable ($type = JOOMDOC_DOCUMENT, $prefix = JOOMDOC_TABLE_PREFIX, $config = array ()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param array	$data Data for the form.
     * @param boolean $loadData	True if the form is to load its own data (default case), false if not.
     * @return mixed A JForm object on success, false on failure
     */
    public function getForm ($data = array (), $loadData = true) {

        $form = $this->loadForm(sprintf('%s.%s', JOOMDOC_OPTION, JOOMDOC_DOCUMENT), JFile::read(JOOMDOC_MODELS . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . JOOMDOC_DOCUMENT . '.xml'), array('control' => 'jform', 'load_data' => $loadData));
        /* @var $form JForm */
        $data = $this->loadFormData();

        if (empty($form))
            return false;

        $config = JoomDOCConfig::getInstance();
        if ($config->documentAccess == 2) { // single user instead of access level
            $form->setFieldAttribute('access', 'type', 'user');
        } else {
            $form->removeField('download');
        }
        if (!$this->canEditState($form)) {

            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('favorite', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');

            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('favorite', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');

        }
        if (!JoomDOCAccess::admin()) { 
            $form->setFieldAttribute('versioning_mailing_document_based', 'disabled', 'true', 'params');
            $form->setFieldAttribute('versioning_mailing_document_based', 'filter', 'unset', 'params');
        }
        $params = JComponentHelper::getParams(JOOMDOC_OPTION);
        $registry = new JRegistry($data->params);
        $params->merge($registry);
        if (!$params->get('versioning_mailing_document_based') || !JoomDOCAccessDocument::notifications($data->id)) {
            $form->setFieldAttribute('versioning_mailing_allow', 'disabled', 'true', 'params');
            $form->setFieldAttribute('versioning_mailing_allow', 'filter', 'unset', 'params');
        }

        if (!JoomDOCAccess::licenses()) {
            $form->setFieldAttribute('license', 'disabled', 'true');
            $form->setFieldAttribute('license', 'filter', 'unset');
        }

        $config = JoomDOCConfig::getInstance();
        /* @var $config JoomDOCConfig */
        if ($config->versionDocument && JoomDOCAccessDocument::viewVersions($form->getValue('id'))) {
            $form->setValue('versionNote', '');
            if ($config->versionRequired)
                $form->setFieldAttribute('versionNote', 'required', 'true');
        }

        // load custom fields
        $query = $this->getDbo()->getQuery(true);
        $query->select('id, title, type, params')
        	->from('#__joomdoc_field')
        	->order('ordering')
        	->where('published = 1');
        $fields = $this->getDbo()->setQuery($query)->loadObjectList('id');
        
        if ($fields) {
        	
        	// load custom fields options
        	$query = $this->getDbo()->getQuery(true);
        	$query->select('field, value, label')
        		->from('#__joomdoc_option')
        		->where('field IN (' . implode(', ', array_keys($fields)) . ')')	
        		->order('ordering');
        	$options = $this->getDbo()->setQuery($query)->loadObjectList();
        	
        	$fieldmap[JOOMDOC_FIELD_TEXT]         = 'text';
        	$fieldmap[JOOMDOC_FIELD_DATE]         = 'calendar';
        	$fieldmap[JOOMDOC_FIELD_RADIO]        = 'radio';
        	$fieldmap[JOOMDOC_FIELD_SELECT]       = 'list';
        	$fieldmap[JOOMDOC_FIELD_CHECKBOX]     = 'checkboxes';
        	$fieldmap[JOOMDOC_FIELD_TEXTAREA]     = 'textarea';
        	$fieldmap[JOOMDOC_FIELD_EDITOR]       = 'editor';
        	$fieldmap[JOOMDOC_FIELD_MULTI_SELECT] = 'list';
            $fieldmap[JOOMDOC_FIELD_SUGGEST]      = 'suggest';
        	
        	foreach ($fields as $field) {
        		// create basic form element
        		$element = new SimpleXMLElement('<field/>');
        	
        		// setup basic field params
                $element->addAttribute('field_id', $field->id);
        		$element->addAttribute('name',  'field' . $field->id);
        		$element->addAttribute('label', $field->title);
        		if ($field->type == JOOMDOC_FIELD_EDITOR && !JFactory::getUser()->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $field->id))
        			$element->addAttribute('type', 'div');
        		else
        			$element->addAttribute('type',  $fieldmap[$field->type]);

        		// setup other field params
        		$params = new JRegistry($field->params);
        		$params = $params->toArray();
        		
        		// setup field options
        		if ($field->type == JOOMDOC_FIELD_RADIO) {
        			$element->addChild('option', JText::_('JNO') )->addAttribute('value', 0);
        			$element->addChild('option', JText::_('JYES'))->addAttribute('value', 1);
        			if (JFactory::getUser()->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $field->id))
        				$params['class'] = JString::trim(JArrayHelper::getValue($params, 'class', '', 'string') . ' btn-group');
        		}
        		
       			if ($field->type == JOOMDOC_FIELD_SELECT || $field->type == JOOMDOC_FIELD_CHECKBOX || $field->type == JOOMDOC_FIELD_MULTI_SELECT)
       				foreach ($options as $option)
       					if ($field->id == $option->field)
       						$child = $element->addChild('option', $option->label)->addAttribute('value', $option->value);
        		
       			if ($field->type == JOOMDOC_FIELD_EDITOR)
       				$element->addAttribute('filter',  'JComponentHelper::filterText');
       			if ($field->type == JOOMDOC_FIELD_MULTI_SELECT || $field->type == JOOMDOC_FIELD_SUGGEST)
       				$element->addAttribute('multiple',  'multiple');
       			
       			foreach ($params as $var => $val) {
       				if ($var == 'required' && !JFactory::getUser()->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $field->id))
       					continue;
       				if ($val)
       					$element->addAttribute($var,  $val);
       			}       			
       			
       			if (!JFactory::getUser()->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $field->id)) {
       				$element->addAttribute('disabled', 'true');
       				if ($field->type == JOOMDOC_FIELD_RADIO || $field->type == JOOMDOC_FIELD_CHECKBOX)
       					foreach ($element->children() as $option)
       						$option->addAttribute('disabled', 'true');       			
       			}
       			
	        	$form->setField($element);
	        	
	        	if ($field->type == JOOMDOC_FIELD_CHECKBOX || $field->type == JOOMDOC_FIELD_MULTI_SELECT || $field->type == JOOMDOC_FIELD_SUGGEST) {
	        		$registry = new JRegistry(is_array($data) ? JArrayHelper::getValue($data, 'field'.$field->id) : $data->get('field'.$field->id));
	        		is_array($data) ? $data['field'.$field->id] = (array) $registry->toArray() : $data->set('field'.$field->id, (array) $registry->toArray());
	        	}
    	    }
    	     
        	$form->bind($data);
        }
        
        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData () {
        $data = JFactory::getApplication()->getUserState(sprintf('%s.%s.%s.data', JOOMDOC_OPTION, JOOMDOC_TASK_EDIT, JOOMDOC_DOCUMENT), array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        $data->params['versioning_mailing_allow'] = $this->loadAllowNotifications((int) $data->id);
        return $data;
    }
    
    /**
     * Load allow notifications param value based on user and document.
     * @param int $docId current document ID
     * @return int
     */
    private function loadAllowNotifications($docId) {
        $userId = (int) JFactory::getUser()->id;
        return (int) $this->_db->setQuery('SELECT notifications '
                        . 'FROM #__joomdoc_user '
                        . "WHERE user_id = $userId AND document_id = $docId")->loadResult();
    }

    /**
     * (non-PHPdoc)
     * @see JModelAdmin::save()
     */
    public function save($data, $preloadPath = false) {
    	// load custom fields of type checkbox
        $user = JFactory::getUser();
        $db = $this->getDbo();
    	$query = $db->getQuery(true);
    	$query->select('id')->from('#__joomdoc_field')->where('type = ' . JOOMDOC_FIELD_CHECKBOX . ' OR type = ' . JOOMDOC_FIELD_MULTI_SELECT . ' OR type = ' . JOOMDOC_FIELD_SUGGEST);
    	$cid = $db->setQuery($query)->loadColumn();
    	
    	foreach ($cid as $id) {
    		if (!isset($data['field' . $id])) { // if no checkbox checked or no multi-selectbox selected then request is empty
                if ($user->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $id)) {
                    $data['field' . $id] = array(); 
                }
            }
        }
            
    	$query->clear()->select('id')->from('#__joomdoc_field')->where('type = ' . JOOMDOC_FIELD_SUGGEST);
    	$tid = $db->setQuery($query)->loadColumn();     
        
        if ($tid) {
            JFormHelper::loadFieldClass('suggest');
            $option = JTable::getInstance('option', JOOMDOC_TABLE_PREFIX);
            /* @var $option JoomDOCTableOption */
            foreach ($tid as $id) {
                if ($user->authorise('joomdoc.field.edit', 'com_joomdoc.field.' . $id)) {
                    $values = JArrayHelper::getValue($data, 'field' . $id, array(), 'array');
                    foreach ($values as $vi => $value) {
                        if (JString::strpos($value, JFormFieldSuggest::PREFIX) === 0) { // new field value
                            $value = JString::substr($value, JString::strlen(JFormFieldSuggest::PREFIX));
                            $query->clear()->select('COUNT(*)')->from('#__joomdoc_option')->where('LOWER(TRIM(value)) = ' . $db->q(JString::strtolower($value)))->where('field = ' . (int) $id); // check if the tag already has such option

                            if (!$db->setQuery($query)->loadResult()) { // new tag option
                                $option->setProperties(array('id' => null, 'field' => $id, 'value' => $value, 'label' => $value)); // value is the same as label
                                $query->clear()->select('MAX(ordering) + 1')->from('#__joomdoc_option')->where('field = ' . (int) $id); // append at the option list end
                                $option->set('ordering', $db->setQuery($query)->loadResult());
                                $option->store();
                            }
                            $values[$vi] = $value;
                        }
                    }
                    $data['field' . $id] = $values;
                }
            }
        }
        
        if ($preloadPath) {
            $this->setState($this->getName() . '.id', $db->setQuery('SELECT MAX(id) FROM #__joomdoc WHERE path = ' . $db->q($data['path']))->loadResult());
        }
        // save the param per user/document not per document
        $allow = 0;
        if (isset($data['params']['versioning_mailing_allow'])) {
            $allow = (int) $data['params']['versioning_mailing_allow'];
            unset($data['params']['versioning_mailing_allow']);
        }
        // prevent params lossing
        $item = $this->getItem();
        if (!empty($item->params)) {
            $data['params'] = array_merge((array) $item->params, $data['params']);
        }
        if (($result = parent::save($data))) {
            $this->saveAllowNotification($allow);
        }
        return $result;
    }

    /**
     * Save allow notification param into separate table per user/document.
     * @param int $allow 1/0 allowed/denied
     * @return bool
     */
    private function saveAllowNotification($allow) {
        $userId = (int) JFactory::getUser()->id;
        $docId = (int) $this->getState($this->getName() . '.id');
        return $this->_db->setQuery('INSERT INTO #__joomdoc_user (user_id, document_id, notifications) ' .
                "VALUES ($userId, $docId, $allow) " .
                "ON DUPLICATE KEY UPDATE notifications = $allow")->execute();
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param object A record object.
     * @return array An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions ($table) {
        $condition[] = '`parent_path` = ' . $this->_db->quote($table->parent_path) . ' AND `id` IN (SELECT MIN(`id`) FROM `#__joomdoc` GROUP BY `path`)';
        return $condition;
    }

    /**
     * Load document by path value.
     *
     * @param string $path file path
     * @return stdClass
     */
    public function getItemByPath ($path) {
    	$path = $this->_db->quote($path);
        
    	$query = 'SELECT `id`, (' . JoomDOCModelList::getDocumentPublished() . ') AS `published`, `title`, `description`, `params`, `full_alias`, `created_by`, `state`, `created`, `modified`, `favorite`, `parent_path`, `file_state`, `access`, `download`, `file_id` ';
        
        foreach ($this->getPublishedFields() as $field)
        	$query .= ', `field' . $field->id . '` ';
        
        $query .= 'FROM `#__joomdoc_flat` AS `document` 
        		   WHERE `path` = ' . $path;
        
        $item = $this->_db->setQuery($query)->loadObject();
        if (!$item) {
            $item = new stdClass();
        }
		
        $this->_db->setQuery('SELECT SUM(`hits`) FROM `#__joomdoc_file` WHERE `path` = ' . $path . ' GROUP BY `path`');
        $item->hits = $this->_db->loadResult();
        
        return $item;
    }
    
    public function getPublishedFields() {
    	$fields = JModelLegacy::getInstance(JOOMDOC_FIELDS, JOOMDOC_MODEL_PREFIX);
    	/* @var $fields JoomdocModelFields */
    	return $fields->getAllowedFields('joomdoc.field.frontend');
    }
    
    /**
     * Get parent document by parent path.
     *
     * @param string $parentPath parent relative path
     * @return stdClass if not found return null
     */
    public function getParent ($parentPath) {
        if (!JString::trim($parentPath)) {
            return null;
        }
        $this->_db->setQuery('SELECT * FROM `#__joomdoc` WHERE `path` = ' . $this->_db->Quote($parentPath) . ' ORDER BY `version` DESC');
        return $this->_db->loadObject();
    }

    /**
     * Search document ID by file relative path.
     *
     * @param string $path relative path
     * @return int
     */
    public function searchIdByPath ($path) {
    	if (empty($path))
    		return 0;
    	static $cache;
    	if (!isset($cache[$path])) {
        	$this->_db->setQuery('SELECT `id` FROM `#__joomdoc` WHERE `path` = ' . $this->_db->quote($path) . ' ORDER BY `version` DESC', 0, 1);
        	$cache[$path] = (int) $this->_db->loadResult();
    	}
    	return $cache[$path];
    }

    /**
     * Search checked out by file relative path.
     *
     * @param string $path relative path
     * @return int
     */
    public function searchCheckedOutByPath ($path) {
        $this->_db->setQuery('SELECT `checked_out` FROM `#__joomdoc` WHERE `path` = ' . $this->_db->quote($path) . ' ORDER BY `version` DESC', 0, 1);
        return (int) $this->_db->loadResult();
    }

    /**
     * Search full document alias by relative file path.
     *
     * @param string $path relative path of file
     * @return string full alias of last version of document, null if not found
     */
    public function searchFullAliasByPath ($path) {
        static $results;
        if (is_null($results))
            $results = array();
        // search in cache		
        foreach ($results as $result)
            if ($result->path == $path)
                return $result->alias;
        $this->_db->setQuery('SELECT `full_alias` FROM `#__joomdoc` WHERE `path` = ' . $this->_db->quote($path) . ' ORDER BY `version` DESC', 0, 1);
        $result = new JObject();
        $result->path = $path;
        $result->alias = $this->_db->loadResult();
        // cache result
        $results[] = $result;
        return $result->alias;
    }

    /**
     * Search file relative path by document full alias.
     *
     * @param string $fullAlias value from table #__joomdoc column full_alias
     * @return string value from table #__joomdoc column columns parent_path and path
     */
    public function searchRelativePathByFullAlias ($fullAlias) {
        if (!JString::trim($fullAlias)) {
            return null;
        }
        $config = JoomDOCConfig::getInstance();
        if ($config->virtualFolder)
            // if turn on virtual folder try to complete full document alias
            if (($rootPath = JoomDOCFileSystem::getRelativePath($config->path))) {
                // search for full alias of virtual root parent
                $rootFullAlias = $this->searchFullAliasByPath($rootPath);
                if ($rootFullAlias)
                    // add parent full alias on begin of document virtual full alias
                    $fullAlias = $rootFullAlias . DIRECTORY_SEPARATOR . $fullAlias;
                else
                    // add virtual root path parent on begin of document virtual full alias
                    $fullAlias = $rootPath . DIRECTORY_SEPARATOR . $fullAlias;
            }
        // search for file relative path by document full alias
        $this->_db->setQuery('SELECT `path` FROM `#__joomdoc` WHERE `full_alias` = ' . $this->_db->quote($fullAlias) . ' ORDER BY `version` DESC', 0, 1);
        return $this->_db->loadResult();
    }
    
    /**
     * Get ID of document of last path parent.
     *
     * @param string $path relative file path
     * @return int document ID, null of not found
     */
    public function getParentDocumentID ($path) {
        static $results;
        // last parent path of path
        $parentPath = $subParentPath = JoomDOCFileSystem::getParentPath($path);

        // search in cache
        if (is_array($results))
            foreach ($results as $result)
                if ($result->subparentPath == $subParentPath)
                    return $result->documentID;

        // generate parents tree
        while ($parentPath) {
            $where[] = '`path` = ' . $this->_db->quote($parentPath);
            $parentPath = JoomDOCFileSystem::getParentPath($parentPath);
        }

        if (isset($where)) {
            // search last parent path with document
           	$this->_db->setQuery('SELECT `id`, `path` 
           			              FROM 
           							  (SELECT `id`, `path` 
           			                   FROM `#__joomdoc` 
           			                   WHERE (' . implode(' OR ', $where) . ') 
           			                   ORDER BY `id` ASC /* latest version */
           			                  ) AS s 
           						  ORDER BY `path` DESC /* nearest parent */');
			$row = $this->_db->loadObject();

            // cache result
            $result = new JObject();
            $result->parentPath = $row ? $row->path : false;
            $result->subparentPath = $subParentPath;
            $result->documentID = $row ? $row->id : false;
            $results[] = $result;

            return $result->documentID;
        }
        return false;
    }

    /**
     * Set document state by file path.
     *
     * @param string $path file path
     * @param int $value new state value
     * @return boolean
     */
    public function setPublish ($path, $value) {
        if (($candidate = $this->searchRelativePathByFullAlias($path)))
            $path = $candidate;
        if (($id = $this->searchIdByPath($path)) && JoomDOCAccessDocument::editState($id, $this->searchCheckedOutByPath($path))) {
            $this->_db->setQuery('UPDATE `#__joomdoc` SET `state` = ' . $value . ' WHERE `id` = ' . $id);
            $success = $this->_db->query();
            JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($path);
            return $success;
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see JModelAdmin::publish()
     */
    public function publish(&$pks, $value = 1) {
    	$success = parent::publish($pks, $value);
    	JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat(null, $pks);
    	return $success;
    }

    /**
     * Get relative path's of all trashed document's.
     *
     * @return array relative path's
     */
    public function emptytrash () {
    	$this->_db->setQuery('SELECT MIN(`id`) FROM #__joomdoc WHERE `state` = ' . JOOMDOC_STATE_TRASHED . ' GROUP BY `path`');
    	$cid = $this->_db->loadColumn();
    	$count = 0;
    	if (!empty($cid)) {
	        $this->_db->setQuery('SELECT `path` FROM `#__joomdoc` WHERE `state` = ' . JOOMDOC_STATE_TRASHED . ' AND `id` IN (' . implode(',', $cid) . ')');
	        $paths = $this->_db->loadColumn();
	        $count = count($paths);
	        if ($count) {
	            $this->_db->setQuery('DELETE FROM `#__joomdoc` WHERE `path` IN (' . implode(',', array_map(array($this->_db, 'quote'), $paths)) . ')');
	            $this->_db->query();
	            JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat($paths);
	        }
    	}
        return $count;
    }
    
    /**
     * Trash documents.
     *
     * @param array $cid document's id's
     * @return boolean
     */
    public function trash ($cid) {
        $this->_db->setQuery('UPDATE `#__joomdoc` SET `state` = ' . JOOMDOC_STATE_TRASHED . ' WHERE `id` IN (' . implode(',', $cid) . ')');
        $this->_db->query();
        JModelLegacy::getInstance(JOOMDOC_DOCUMENTS, JOOMDOC_MODEL_PREFIX)->flat(null, $cid);
        return $this->_db->getAffectedRows();
    }
    }
?>