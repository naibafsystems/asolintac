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

jimport('joomla.application.component.modellist');

class JoomDOCModelList extends JModelList {
    /**
     * Filter items for list.
     *
     * @var array
     */
    protected $filter;

    /**
     * Auto-populate the model state.
     *
     * @return	void
     */

    public function __construct ($config) {
        $this->option = JOOMDOC;
        parent::__construct($config);
        if (!isset($this->state)) {
            $this->populateState();
            $this->state = $this->getState();
        }
    }

    protected function populateState ($ordering = null, $direction = null) {
        if (isset($this->filter)) {
            $mainframe = JFactory::getApplication();
            /* @var $mainframe JApplication */

            foreach ($this->filter as $name => $type) {
                $value = $this->cleanData($this->getUserStateFromRequest($this->getSessionName($name), JoomDOCView::getFieldName($name), $this->getDefaultValue($type), $type), $type);
                $this->setState(JoomDOCView::getStateName($name), $value);
            }

            if ($mainframe->isSite())
                $this->setState(JoomDOCView::getStateName(JOOMDOC_FILTER_ACCESS), true);

            $keys = array_keys($this->filter);
            parent::populateState(reset($keys), 'asc');
        }
    }

    /**
     * Get property session key name.
     *
     * @param string $name property name
     * @return string
     */
    public function getSessionName ($name) {
        return sprintf('%s.filter.%s', $this->context, $name);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * @param string $id a prefix for the store id.
     * @return string a store id.
     */
    protected function getStoreId ($id = '') {
        foreach ($this->filter as $name => $type)
            $id .= sprintf(':%s', JoomDOCView::getStateName($name));
        return parent::getStoreId($id);
    }

    /**
     * Get property value from model state.
     *
     * @param string $name property name
     * @param mixed $default property default value if state is no available
     * @return mixed property from model state or default value
     */
    public function getState ($name = null, $default = null) {
        return parent::getState(JoomDOCView::getStateName($name, $default));
    }

    /**
     * Get default value for data type.
     *
     * @param string $dataType data type string name int|array|string
     * @return mixed default value
     */
    public function getDefaultValue ($dataType) {
        switch ($dataType) {
            case JOOMDOC_INT:
                return 0;
            case JOOMDOC_ARRAY:
                return array();
            case JOOMDOC_STRING:
                return '';
            default:
                return null;
        }
    }

    /**
     * Clean data by their data type.
     *
     * @param mixed $value data value
     * @param string $dataType data type
     * @return mixed
     */
    public function cleanData ($value, $dataType) {
        switch ($dataType) {
            case JOOMDOC_INT:
                return (int) $value;
            case JOOMDOC_STRING:
                return JString::trim($value);
            default:
                return $value;
        }
    }
    /**
     * Copy row of database without primary key. After copy is row checkin.
     *
     * @param string $table  table name
     * @param string $pkey   primary key
     * @param string $pvalue primary key value
     * @return int new inserted id
     */
    public static function copyRow ($table, $pkey, $pvalue) {
        $db = JFactory::getDbo();
        /* @var $db JDatabaseMySQL */
        static $cache;
        if (is_null($cache))
            $cache = array();
        if (!isset($cache[$table])) {
            $item = new JObject();
            // all fields of table
            $item->fields = $db->getTableColumns($table);//reset();
            //JError::raiseNotice( 100, $item->fields );

            // field names
            $sfields = array_keys($item->fields);
            // unset primary key field
            unset($sfields[array_search($pkey, $sfields)]);
            // into query format
            $sfields = implode('`,`', $sfields);
            
            //var_dump($sfields);
            //ob_flush();
            //flush();
            //JError::raiseNotice( 100, $sfields );           
            $item->copyQuery = 'INSERT INTO `' . $table . '` (`' . $sfields . '`) (SELECT `' . $sfields . '` FROM `' . $table . '` WHERE `' . $pkey . '` =  %d)';
            if (isset($item->fields['checked_out']) && isset($item->fields['checked_out_time']))
                $item->checkinQuery = 'UPDATE `' . $table . '` SET `checked_out` = 0, `checked_out_time` = "0000-00-00 00:00:00" WHERE `id` = %d';
            else
                $item->checkinQuery = false;
            $cache[$table] = $item;
        } else
            $item = $cache[$table];
        // copy row
        //JError::raiseNotice( 100, sprintf($item->copyQuery, $pvalue) );
        $db->setQuery(sprintf($item->copyQuery, $pvalue));
        if ($db->query()) {
            // new ID
            $id = $db->insertid();
            if ($item->checkinQuery && $id) {
                // check in new row
                $db->setQuery(sprintf($item->checkinQuery, $pvalue));
                $db->query();
            }
            return $id;
        }
        return false;
    }

    /**
     * Get MySQL query fragment to control if document can be display on frontend.
     */
    public static function getDocumentPublished () {
        $db = JFactory::getDbo();
        /* @var $db JDatabaseMySQL */
        $user = JFactory::getUser();
        /* @var $user JUser logged user */
        $date = JFactory::getDate();
        /* @var $date JDate current date */
        $config = JoomDOCConfig::getInstance();

        $nullDate = $db->Quote($db->getNullDate());
        /* @var $nullDate string empty date as MySQL datetime */
        $nowDate = $db->Quote($date->toSql());
        /* @var $nowDate current date date as MySQL datetime in GMT0 */

        // document publish state
        $published[] = '`document`.`state` = ' . JOOMDOC_STATE_PUBLISHED;
        // document publishing interval
        $published[] = '(`document`.`publish_up` = ' . $nullDate . ' OR `document`.`publish_up` <= ' . $nowDate . ')';
        $published[] = '(`document`.`publish_down` = ' . $nullDate . ' OR `document`.`publish_down` >= ' . $nowDate . ')';

        // user group access
        if ($config->documentAccess == 2) {
            $groups = array(($user->id ? $user->id : -1));
        } else {
            $groups = $user->getAuthorisedViewLevels();
        }
        if (count($groups)) {
            JArrayHelper::toInteger($groups);
            $published[] = '`document`.`access` IN (' . implode(',', $groups) . ')';
        }

        // all criteria have to pass
        $published = implode(' AND ', $published);
        // or logged user owes document
        $published = '(' . $published . ') OR `document`.`created_by` = ' . $user->id;
        
        return $published;
    }
}
?>












