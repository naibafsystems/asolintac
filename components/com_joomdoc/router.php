<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

$language = JFactory::getLanguage();
/* @var $language JLanguage */
$language->load('com_joomdoc', JPATH_ADMINISTRATOR, 'en-GB');
$language->load('com_joomdoc', JPATH_ADMINISTRATOR, $language->getTag(), true);

/**
 * Build SEF route.
 *
 * @param array $query URL query string without Joomla internal params as option, Itemid etc.
 * @return array SEF URL segments
 */
function JoomDOCBuildRoute (&$query) {
    $segments = array();
    // file/folder relative path or document full alias
    if (isset($query['path'])) {
        if (!empty($query['path']))
            $segments[] = JPath::clean($query['path'], '/');       
        unset($query['path']);
        $path = true;
    }

    // task action type
    if (isset($query['task'])) {
        foreach (JoomDOCRouteTasks() as $task => $segment)
            if ($task == $query['task']) {
                $segments[] = $segment;
                unset($query['task']);
                break;
            }
    }
    // page view type
    if (isset($query['view'])) {
        switch ($query['view']) {
            case 'documents':
                $segments[] = JText::_('JOOMDOC_ROUTE_DETAIL');
                break;
            case 'license':
                if (isset($query['id'])) {
                    $segments[] = $query['id'];
                    unset($query['id']);
                }
                $segments[] = JText::_('JOOMDOC_LICENSE');
                if (isset($query['tmpl']))
                    unset($query['tmpl']);
                break;
        }
        unset($query['view']);
    }
    // page view layout type
    if (isset($query['layout'])) {
        switch ($query['layout']) {
            case 'edit':
                $segments[] = JText::_('JOOMDOC_ROUTE_EDIT_PAGE');
                break;
        }
        unset($query['layout']);
    }
    
    return $segments;
}

/**
 * Parse SEF URL to real URL.
 *
 * @param array $segments SEF URL segments without menu item segment
 * @return array real URL query string without Joomla internal params as option, Itemid etc.
 */
function JoomDOCParseRoute ($segments) {
    $vars = array();
    if (count($segments)) {
        // file/folder relative path or document full alias
        $last = count($segments) - 1;
        $ignoreSegments = 1;
        switch (str_replace(':' ,'-', $segments[$last])) {
            case JText::_('JOOMDOC_ROUTE_DETAIL'):
                // view document/folder/file page view
                $vars['view'] = 'documents';
                break;
            case JText::_('JOOMDOC_ROUTE_EDIT_PAGE'):
                // edit document
                $vars['view'] = 'document';
                $vars['layout'] = 'edit';
                break;
            case JText::_('JOOMDOC_LICENSE'):
                $vars['view'] = 'license';
                $nextLast = $last - 1;
                $vars['id'] = $segments[$nextLast];
                unset($segments[$nextLast]);
                $segments = array_merge($segments);
                $last = count($segments) - 1;
                $vars['tmpl'] = 'component';
                $ignoreSegments = 2;
                break;            
            default:
                // search in available tasks
                foreach (JoomDOCRouteTasks() as $task => $segment)
                    if ($segment == $segments[$last]) {
                        $vars['task'] = $task;
                        break;
                    }
                break;
        }
        unset($segments[$last]);
        $segmentsCount = count($segments);
        $juri = JUri::getInstance();
        $path = $juri->getPath();
        if ($path) {
            $parts = explode('/', $path);
            // cleanup array
            foreach ($parts as $key => $part) {
                if (!JString::trim($part)) {
                    unset($parts[$key]);
                }
            }
            // reindexing
            $parts = array_merge($parts);
            $requestURICount = count($parts);
            $offset = $requestURICount - $segmentsCount - $ignoreSegments;
            $length = $segmentsCount;
            $path = array_slice($parts, $offset, $length);
            $path = implode('/', $path);
            $path = urldecode($path);
        } else {
            $path = implode('/', $segments);
        }
        if ($path)
            $vars['path'] = $path;
    } else {
        $vars['view'] = 'documents';
    }
    return $vars;
}

/**
 * Get component route available tasks.
 *
 * @return array key is task and value segment title
 */
function JoomDOCRouteTasks () {
    $tasks['document.download'] = JText::_('JOOMDOC_ROUTE_DOWNLOAD');
    $tasks['document.edit'] = JText::_('JOOMDOC_ROUTE_EDIT');
    $tasks['document.add'] = JText::_('JOOMDOC_ROUTE_ADD');
    $tasks['document.deletefile'] = JText::_('JOOMDOC_ROUTE_DELETEITEM');
    $tasks['documents.delete'] = JText::_('JOOMDOC_ROUTE_DELETE');
    $tasks['document.publish'] = JText::_('JOOMDOC_ROUTE_PUBLISH');
    $tasks['document.unpublish'] = JText::_('JOOMDOC_ROUTE_UNPUBLISH');
    return $tasks;
}
?>