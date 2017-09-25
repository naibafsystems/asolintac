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

class JoomDOCController extends JControllerLegacy {
    function webdav () {
        /* webdav list URL */
        // session id
        $session = JRequest::getString('s');
        // parent folder path
        $path = JoomDOCRequest::getPath();
        if (!$session) {
            /* webdav edit url */
            // file path
            $file = JRequest::getString('file');
            // file path segments
            $segments = explode('/', $file);
            // cleanup
            $segments = array_map('JString::trim', $segments);
            $segments = array_filter($segments);
            // reindexing
            $segments = array_merge($segments);
            // session id is first file path segment
            $session = $segments[0];
            // remove session id
            unset($segments[0]);
            // file path in JoomDOC standard
            $path = implode(DIRECTORY_SEPARATOR, $segments);
            //$model = JModelLegacy::getInstance(JOOMDOC_FILE, JOOMDOC_MODEL_PREFIX);
            /* @var $model JoomDOCSiteModelFile */
            //$path = $model->getPathById($path); // since 4.0.3 is path as ID
            //$segments = explode(DIRECTORY_SEPARATOR, $path);
            // file path in webdav framework usage (with slash on the begin)
            $file = '/' . implode('/', $segments);
            // set file param in get for webdav framework
            JRequest::setVar('path', $file, 'get');            
        }
        $webDavUser = JFactory::getUser(JoomDOCUser::getUserIdBySessionId($session));
        if (JoomDOCAccessFileSystem::editWebDav(null, JoomDOCFileSystem::clean($path), $session)) {
            if (!JRequest::getVar('path'))
                JRequest::setVar('path', '/');
            require_once(JOOMDOC_WEBDAV . DIRECTORY_SEPARATOR . 'index.php');
        } else
            die('{"error":"","files":""}');
    }
}
?>