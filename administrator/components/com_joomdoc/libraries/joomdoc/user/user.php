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

class JoomDOCUser {
    /**
     * Set user state property into database.
     *
     * @param string $param property name
     * @param string $value property value
     */
    public static function setUserState ($param, $value) {
        if (isset($_COOKIE['joomdoc'])) {
            $session = (int) $_COOKIE['joomdoc'];
        } else {
            $session = rand(100);
            $uri = JURI::getInstance();
            setcookie('joomdoc', $session, time() + 365 * 24 * 60 * 60, '/', $uri->toString(array('host')));
        }
        $db = JFactory::getDbo();
        $db->setQuery('SELECT `params` FROM `#__joomdoc_userstate` WHERE `session` = ' . $session);
        $params = new JParameter($db->loadResult());
        $params->setValue($param, $value);
        $params = $db->Quote($params->toString());
        $query = 'INSERT INTO `#__joomdoc_userstate` (`session`,`params`) VALUES (' . $session . ',' . $params . ') ';
        $query .= 'ON DUPLICATE KEY UPDATE `params` = ' . $params;
        $db->setQuery($query);
        $db->query();
    }
    /**
     * Get user state from database.
     *
     * @param string $param property name
     * @return string
     */
    public static function getUserState ($param) {
        if (isset($_COOKIE['joomdoc'])) {
            $db = JFactory::getDbo();
            $db->setQuery('SELECT `params` FROM `#__joomdoc_userstate` WHERE `session` = ' . (int) $_COOKIE['joomdoc']);
            $params = new JParameter($db->loadResult());
            return $params->getValue($param);
        }
        return null;
    }

    /**
     * Search user ID in table jos_session by value of column session_id.
     * 
     * @param string $sessionId
     * @return int
     */
    public static function getUserIdBySessionId ($sessionId) {
        $db = JFactory::getDbo();
        /* @var $db JDatabaseMySQL */
        $db->setQuery('SELECT `userid` FROM `#__session` WHERE `session_id` = ' . $db->quote($sessionId));
        return $db->loadResult();
    }
}
?>