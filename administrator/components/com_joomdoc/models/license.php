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

class JoomDOCModelLicense extends JModelAdmin {
    /**
     * @var string The prefix to use with controller messages.
     * @since	1.6
     */
    protected $text_prefix = JOOMDOC_OPTION;

    function __construct ($config) {
        parent::__construct();
        $this->option = JOOMDOC_OPTION;
        $this->name = $this->getName();
        $this->setState($this->getName() . '.id', JRequest::getInt('id'));
        $this->checkin();
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record a record object.
     * @return boolean True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete ($record) {
        return JoomDOCAccess::licenses();
    }

    /**
     * Method to test whether a record can be modified.
     *
     * @param JForm $record a record object.
     * @return boolean True if allowed to change the state of the record. Defaults to the permission set in the component.
     */
    protected function canEditState ($record) {
        return JoomDOCAccess::licenses();
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param type The table type to instantiate
     * @param string A prefix for the table class name. Optional.
     * @param array Configuration array for model. Optional.
     * @return JTable A database object
     */
    public function getTable ($type = JOOMDOC_LICENSE, $prefix = JOOMDOC_TABLE_PREFIX, $config = array ()) {
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

        $form = $this->loadForm(sprintf('%s.%s', JOOMDOC_OPTION, JOOMDOC_LICENSE), JFile::read(JOOMDOC_MODELS . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . JOOMDOC_LICENSE . '.xml'), array('control' => 'jform', 'load_data' => $loadData));
        /* @var $form JForm */
        if (empty($form))
            return false;
        if (!JoomDOCAccess::licenses()) {
            $form->setFieldAttribute('id', 'disabled', 'true');
            $form->setFieldAttribute('title', 'disabled', 'true');
            $form->setFieldAttribute('alias', 'disabled', 'true');
            $form->setFieldAttribute('text', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('default', 'disabled', 'true');
            $form->setFieldAttribute('checked_out', 'disabled', 'true');
            $form->setFieldAttribute('checked_out_time', 'disabled', 'true');
            $form->setFieldAttribute('created', 'disabled', 'true');
            $form->setFieldAttribute('created_by', 'disabled', 'true');
            $form->setFieldAttribute('modified', 'disabled', 'true');
            $form->setFieldAttribute('modified_by', 'disabled', 'true');
            $form->setFieldAttribute('params', 'disabled', 'true');
            $form->setFieldAttribute('id', 'filter', 'unset');
            $form->setFieldAttribute('title', 'filter', 'unset');
            $form->setFieldAttribute('alias', 'filter', 'unset');
            $form->setFieldAttribute('text', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('default', 'filter', 'unset');
            $form->setFieldAttribute('checked_out', 'filter', 'unset');
            $form->setFieldAttribute('checked_out_time', 'filter', 'unset');
            $form->setFieldAttribute('created', 'filter', 'unset');
            $form->setFieldAttribute('created_by', 'filter', 'unset');
            $form->setFieldAttribute('modified', 'filter', 'unset');
            $form->setFieldAttribute('modified_by', 'filter', 'unset');
            $form->setFieldAttribute('params', 'filter', 'unset');
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
        $data = JFactory::getApplication()->getUserState(sprintf('%s.%s.%s.data', JOOMDOC_OPTION, JOOMDOC_TASK_EDIT, JOOMDOC_LICENSE), array());
        if (empty($data))
            $data = $this->getItem();
        return $data;
    }
    /**
     * Set license as default or non default. If set as default others are set as non default.
     *
     * @param int $id license id
     * @param int $value default value
     * @return booelan
     */
    public function defaults ($id, $value) {
        $table = $this->getTable();
        /* @var $table JoomDOCTableLicense */
        return $table->defaults($id, $value);
    }
    /**
     * Trash license's.
     *
     * @param array $cid license's id's
     * @return int affected's license's count
     */
    public function delete (&$cid) {
        $table = $this->getTable();
        /* @var $table JoomDOCTableLicense */
        return $table->delete($cid);
    }

    /**
     * Delete trashed license's.
     *
     * @return int affected's license's count
     */
    public function trash () {
        $table = $this->getTable();
        /* @var $table JoomDOCTableLicense */
        return $table->trash();
    }

    /**
     * Search for path license. Search in Parent Documents backward for License to inherite.
     * If not found inherited License return default License.
     *
     * @param string $path
     * @return mixed stdClass with full License or null if not found
     */
    public function license ($path) {
        static $default, $cache, $licenses;
        if (is_null($cache)) {
            $cache = $licenses = array();
        }
        $segments = explode(DIRECTORY_SEPARATOR, $path);
        $count = count($segments);
        for ($i = $count - 1; $i > 0; $i--) {
            // generate Parents Paths backward
            $parent = array_slice($segments, 0, $i);
            $parent = implode(DIRECTORY_SEPARATOR, $parent);
            // search Parent Path in Cache
            foreach ($cache as $stored) {
                if ($stored->path == $parent) {
                    // found Parent in Cache with License to inherite
                    $license = $stored->license;
                    break;
                }
            }
            if (isset($license)) {
                // inherited License found in Cache
                break;
            }
            // save all Parents found on Way to inherited License
            $item = new JObject();
            $item->path = $parent;
            $items[] = $item;
            // search Parent Document for published License
            $query = 'SELECT `document`.`id`, `document`.`license`, `license`.`state` ';
            $query .= 'FROM `#__joomdoc` AS `document` ';
            $query .= 'LEFT JOIN `#__joomdoc_license` AS `license` ON `license`.`id` = `document`.`license` ';
            $query .= 'WHERE `path` = ' . $this->_db->quote($parent);
            $this->_db->setQuery($query);
            $candidate = $this->_db->loadObject();
            if (!is_null($candidate) && $candidate->license && $candidate->state == JOOMDOC_STATE_PUBLISHED) {
                // Candidate has published License to inherite
                $license = $candidate->license;
                break;
            }
        }
        if (isset($license)) {
            if ($license) {
                // found License to inherite
                if (!isset($licenses[$license])) {
                    // search published License in Cache or load and store
                    $query = 'SELECT * FROM `#__joomdoc_license` WHERE `id` = ' . $license . ' AND `state` = ' . JOOMDOC_STATE_PUBLISHED;
                    $this->_db->setQuery($query);
                    $licenses[$license] = $this->_db->loadObject();
                }
                $license = $licenses[$license];
            }
        } else {
            // License to inherite didn't find
            if (is_null($default)) {
                // search for default published License for all Documents without own License or inherited License
                $query = 'SELECT * FROM `#__joomdoc_license` WHERE `default` = ' . JOOMDOC_STATE_DEFAULT . ' AND `state` = ' . JOOMDOC_STATE_PUBLISHED;
                $this->_db->setQuery($query);
                $default = $this->_db->loadObject();
                if (is_null($default)) {
                    // default License not available
                    $default = 0;
                } else {
                    $licenses[$default->id] = $default;
                }
            }
            // use default License
            $license = $default;
        }
        if (isset($items)) {
            // save into Cache all Documents on Way to inherited License
            $licenseID = is_object($license) ? $license->id : 0;
            foreach ($items as $item) {
                $item->license = $licenseID;
                $cache[] = $item;
            }
        }
        return $license;
    }
}
?>