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

class JoomDOCModelLicenses extends JoomDOCModelList {

    /**
     * Create object and set filter.
     *
     * @param array $config
     * @return void
     */
    function __construct ($config = array ()) {

        $this->filter[JOOMDOC_FILTER_STATE] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_ORDERING] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_DIRECTION] = JOOMDOC_STRING;
        $this->filter[JOOMDOC_FILTER_START] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_LINKS] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_LIMIT] = JOOMDOC_INT;
        $this->filter[JOOMDOC_FILTER_KEYWORDS] = JOOMDOC_STRING;
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(JOOMDOC_FILTER_ID, JOOMDOC_FILTER_TITLE, JOOMDOC_FILTER_STATE, JOOMDOC_FILTER_CREATED, JOOMDOC_FILTER_ORDERING, JOOMDOC_ORDER_ORDERING);
        }

        parent::__construct($config);
    }

    /**
     * Get query to load full licenses list to browse table.
     *
     * @return string
     */
    public function getListQuery () {
        $query = 'SELECT `lcs`.*, `edt`.`name` AS `editor`, `crt`.`name` AS `creator` ';
        $query .= 'FROM `#__joomdoc_license` AS `lcs` ';
        $query .= 'LEFT JOIN `#__users` AS `edt` ON `edt`.`id` = `lcs`.`checked_out` ';
        $query .= 'LEFT JOIN `#__users` AS `crt` ON `crt`.`id` = `lcs`.`created_by` ';
        if ($this->state->get(JOOMDOC_FILTER_KEYWORDS)) {
            $query .= 'WHERE LOWER(`lcs`.`title`) LIKE ' . $this->_db->quote('%' . JString::strtolower($this->state->get(JOOMDOC_FILTER_KEYWORDS)) . '%') . ' ';
        }
        $query .= 'ORDER BY `' . $this->state->get(JOOMDOC_FILTER_ORDERING) . '` ' . JString::strtoupper($this->state->get(JOOMDOC_FILTER_DIRECTION));
        return $query;
    }
}
?>