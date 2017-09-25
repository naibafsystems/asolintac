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

class JoomDOCMenu {
    /**
     * Get menu item for documents list. Search for path.
     *
     * @param string $path
     * @return int
     */
    public static function getMenuItemID ($path) {
        static $paths, $default, $results;
        if (is_null($paths)) {
            $app = JFactory::getApplication();
            /* @var $app JSite */
            $menus = $app->getMenu('site');
            /* @var $menus JMenuSite control object of frontend menus */
            $component = JComponentHelper::getComponent(JOOMDOC_OPTION);
            /* @var $component stdClass informations about component */
            $items = $menus->getItems('component_id', $component->id);
            /* @var $items array list of menus assigned with JoomDOC */
            $paths = array();
            /* @var $paths array available menu items for documents list, key is menu Itemid, value is parent path */
            if (is_array($items))
                foreach ($items as $item)
                    // search for Itemids for concrete parent paths
                    if (isset($item->query['path']))
                        $paths[$item->id] = str_replace(DIRECTORY_SEPARATOR, '/', JString::trim($item->query['path'])); //for all cases..
            if (!count($paths) && count($items)) {
                // not found any menu item for documents list (with param path) use first from availables
                $default = reset($items);
                $paths[$default->id] = '';
            }
            // sort for shift on begin item with empty path (total root)
            arsort($paths);
            $keys = array_keys($paths);
            $default = (int) end($keys);
        }
        if (is_null($results))
            $results = array();
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path); //for all cases
        // search in cache
        foreach ($results as $result)
            if ($result->path == $path)
                return $result->itemID;
        // search menu item for path parent
        if ($path)
            foreach ($paths as $itemID => $parent)
                if ($parent && JString::strpos($path, $parent) === 0) {
                    $result = new JObject();
                    $result->path = $path;
                    $result->itemID = $itemID;
                    // cache result
                    $results[] = $result;
                    return $result->itemID;
                }
        // try to serve default
        if (!empty($default) && !empty($paths[$default]) && JString::strpos($path, $paths[$default]) === 0) {
            return $default;
        }
        return 0;
    }
}
?>