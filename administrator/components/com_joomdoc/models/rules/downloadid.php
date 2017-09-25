<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage		JoomDOC
 * @author      	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright		Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class JFormRuleDownloadid extends JFormRule {

    public function test(\SimpleXMLElement $element, $value, $group = null, \JRegistry $input = null, \JForm $form = null) {
        $value = JString::trim($value);
        if ($value && !preg_match('/^[a-z0-9]{32}$/', $value)) {
            JFactory::getLanguage()->load('com_joomdoc', JPATH_ADMINISTRATOR);
            return false;
        }
        $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_joomdoc/joomdoc.xml');
        $server = (string) $xml->updateservers->server;
        $name = (string) $xml->updateservers->server['name'];
        $location = str_replace('.xml', '-' . $value . '.xml', $server);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('#__update_sites')
                ->set('location = ' . $db->q($location))
                ->where('name = ' . $db->q($name));
        $db->setQuery($query)->execute();
        return true;
    }

}
