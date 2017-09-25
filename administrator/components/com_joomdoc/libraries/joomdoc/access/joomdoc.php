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

class JoomDOCAccess {

    /**
     * Authorise concrete task in concrete section.
     *
     * @param string $task task name
     * @param string $section section name, default component
     * @return boolean
     */
    public static function authorise ($task, $section = JOOMDOC_OPTION, $sessionid = null) {
        $user = $sessionid ? JFactory::getUser(JoomDOCUser::getUserIdBySessionId($sessionid)) : JFactory::getUser();
        /* @var $user JUser */
        return $user->authorise($task, $section);
    }

    /**
     * Access acces component.
     *
     * @return boolean
     */
    public static function manage () {
        return JoomDOCAccess::authorise(JOOMDOC_CORE_MANAGE);
    }

    /**
     * Access configure component.
     *
     * @return boolean
     */
    public static function admin () {
        return JoomDOCAccess::authorise(JOOMDOC_CORE_ADMIN);
    }

    /**
     * Manage document's licenses.
     *
     * @return boolean
     */
    public static function licenses () {
        return JoomDOCAccess::authorise(JOOMDOC_CORE_LICENSES);
    }
}
?>