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

class JoomDOCTableLicense extends JTable {
    public $id;
    public $title;
    public $text;
    public $state;
    public $default;
    public $checked_out;
    public $checked_out_time;
    public $created;
    public $created_by;
    public $modified;
    public $modified_by;
    public $params;

    /**
     * Constructor.
     *
     * @param JDatabase A database connector object
     */

    public function __construct (&$db) {
        parent::__construct('#__joomdoc_license', 'id', $db);
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param array Named array
     * @return null|string	null is operation was satisfactory, otherwise returns an error
     */
    public function bind ($array, $ignore = '') {
        $this->default = isset($array['default']) ? JOOMDOC_STATE_DEFAULT : JOOMDOC_STATE_UNDEFAULT;
        return parent::bind($array, $ignore);
    }

    /**
     * Overload the store method for the license table.
     *
     * @param	boolean	Toggle whether null values should be updated.
     * @return	boolean	True on success, false on failure.
     */
    public function store ($updateNulls = false) {
        $date = JFactory::getDate();
        /* @var $date JDate current date */
        $currentDate = $date->toSql();
        /* @var $currentDate string current date as MySQL datetime in GMT0 */
        $user = JFactory::getUser();
        /* @var $user JUser current logged user */
        $app = JFactory::getApplication();
        /* @var $app JApplication */
        if ($this->id) {
            $this->modified = $currentDate;
            $this->modified_by = $user->get('id');
        } else {
            $this->created = $currentDate;
            $this->created_by = $user->get('id');
        }
        // if user doesn't set alias use title
        if (!JString::trim($this->alias)) {
            $this->alias = $this->title;
        }
        // convert alias to safe string
        if ($app->getCfg('unicodeslugs') == 1) {
            $this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
        } else {
            $this->alias = JFilterOutput::stringURLSafe($this->alias);
        }
        if (parent::store($updateNulls)) {
            if ($this->default) {
                $this->_db->setQuery('UPDATE `#__joomdoc_license` SET `default` = ' . JOOMDOC_STATE_UNDEFAULT . ' WHERE `id` <> ' . $this->id);
                $this->_db->query();
            }
            return true;
        }
        return false;
    }

    /**
     * Set license as default or non default. If set as default others are set as non default.
     *
     * @param int $id license id
     * @param int $value default value
     * @return booelan
     */
    public function defaults ($id, $value) {
        $id = (int) $id;
        if ($value == JOOMDOC_STATE_DEFAULT) {
            $this->_db->setQuery('UPDATE `#__joomdoc_license` SET `default` = ' . JOOMDOC_STATE_UNDEFAULT . ' WHERE `id` <> ' . $id);
            $this->_db->query();
            $this->_db->setQuery('UPDATE `#__joomdoc_license` SET `default` = ' . JOOMDOC_STATE_DEFAULT . ' WHERE `id` = ' . $id);
        } else
            $this->_db->setQuery('UPDATE `#__joomdoc_license` SET `default` = ' . JOOMDOC_STATE_UNDEFAULT . ' WHERE `id` = ' . $id);
        $this->_db->query();
        return $this->_db->getAffectedRows() > 0;
    }

    /**
     * Set license's state value.
     *
     * @param array $cid license's id's
     * @param int $value state value
     * @return int affected's license's count
     */
    public function publish ($cid = null, $value = 1, $userId = 0) {
        return $this->state($cid, $value);
    }

    /**
     * Trash license's.
     *
     * @param array $cid license's id's
     * @return int affected's license's count
     */
    public function delete ($cid = null) {
        return $this->state($cid, JOOMDOC_STATE_TRASHED);
    }

    /**
     * Set license's state's.
     *
     * @param array $cid license's id's
     * @param int $value new state value
     * @return int affected's license's count
     */
    public function state ($cid, $value) {
        if (count($cid)) {
            JArrayHelper::toInteger($cid);
            $this->_db->setQuery('UPDATE `#__joomdoc_license` SET `state` = ' . $value . ' WHERE `id` IN (' . implode(', ', $cid) . ')');
            $this->_db->query();
            return $this->_db->getAffectedRows();
        }
        return 0;
    }

    /**
     * Delete trashed license's.
     *
     * @return int affected's license's count
     */
    public function trash () {
        $this->_db->setQuery('DELETE FROM `#__joomdoc_license` WHERE `state` = ' . JOOMDOC_STATE_TRASHED);
        $this->_db->query();
        return $this->_db->getAffectedRows();
    }
}
?>