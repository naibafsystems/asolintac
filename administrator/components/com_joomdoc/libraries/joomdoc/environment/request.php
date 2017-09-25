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

class JoomDOCRequest {
    /**
     * Get path paremeter from request. Test if parameter is relative path or document alias.
     * Test if path is virtual path.
     *
     * @param string $path path to control, if null use param path from request
     * @return string
     */
    public static function getPath ($path = null) {
        static $model;
        /* @var $model JoomDOCModelDocument */
        $mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication (JSite or JAdministrator) */
        if ($mainframe->isSite() && is_null($model))
            $model = JModelLegacy::getInstance(JOOMDOC_DOCUMENT, JOOMDOC_MODEL_PREFIX);
        // get root path from request or config
        if (!$path)
            $path = JString::trim(JRequest::getString('path'));
        $path = JoomDOCString::urldecode($path);
        // frontend
        if ($mainframe->isSite()) {
            // path from request can be document full alias - search for path in document table
            $candidate = $model->searchRelativePathByFullAlias($path);
            if ($candidate)
                $path = $candidate;
            else
                // if request param is path convert from virtual to full relative path
                $path = JoomDOCFileSystem::getNonVirtualPath($path);
        } else {
            // backend
            $path = $mainframe->getUserStateFromRequest(JoomDOCRequest::getSessionPrefix() . 'path', 'path', '', 'string');
            if ($path == JText::_('JOOMDOC_ROOT'))
                return '';
        }
        
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path); //windows
        
        if ($path)
        	$path = JPath::clean($path);
        return $path;
    }
    /**
     * Get array of relative paths from request parameter paths.
     *
     * @return array
     */
    public static function getPaths () {
        return JRequest::getVar('paths', array(), 'default', 'array');
    }

    /**
     * Get Prefix for Variables stored in Joomla session storage (database, file, APC etc.).
     * Prefix is unique for concrete component and view.
     * For example: com_joomdoc_documents_
     *
     * @param boolean $usePath add into session Prefix Variable Path
     * @return string
     */
    public static function getSessionPrefix ($usePath = false) {
        $pieces = array(JRequest::getString('option'), JRequest::getString('view'), JRequest::getString('layout'));
        if ($usePath) {
            $pieces[] = JoomDOCRequest::getPath();
        }
        return implode('.', array_filter($pieces, 'JString::trim')) . '.';
    }

    /**
     * Get checkbox field type value from request or session.
     *
     * @param string $checkbox field name in request or session
     * @param string $tester   another field in request to check if request was delivered
     * @param mixed  $default  default value if field neither in request nor in session
     * @return mixed request, session or default value
     */
    public static function getCheckbox ($checkbox, $tester, $default) {
    	$mainframe = JFactory::getApplication();
        /* @var $mainframe JApplication */
        if (!is_null(JRequest::getVar($tester, null, 'default', 'string'))) {
            /* Request was delivered. Search for checkbox field in request.
               If checkbox was checked get his value from request, otherwise get zero value (if isnt't in request wasn't checked). 
               Save value in session to next using. */
            $mainframe->setUserState($checkbox, JRequest::getInt($checkbox));
        }
        // Search for field in session or return default value
        return $mainframe->getUserStateFromRequest($checkbox, $checkbox, $default, 'int');
    }
}
