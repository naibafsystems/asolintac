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

jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');

// JoomDOC defines
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'defines.php');

// JoomDOC framework prepare to auto load
JLoader::register('JoomDOCFileSystem', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'filesystem' . DIRECTORY_SEPARATOR . 'filesystem.php');
JLoader::register('JoomDOCConfig', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config.php');
JLoader::register('JoomDOCModelList', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'component' . DIRECTORY_SEPARATOR . 'modellist.php');
JLoader::register('JoomDOCView', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'component' . DIRECTORY_SEPARATOR . 'view.php');
// JoomDOC language
$language = JFactory::getLanguage();
/* @var $language JLanguage */
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, 'en-GB');
$language->load(JOOMDOC_OPTION, JPATH_ADMINISTRATOR, $language->getTag(), true);
// set paths to load JoomDOC core classes
JHtml::addIncludePath(JOOMDOC_HTML);
JModelLegacy::addIncludePath(JOOMDOC_MODELS);

class JFormFieldModal_Parents extends JFormField {
    protected $type = 'Modal_Parents';

    /**
     * Display element.
     *
     * @return string
     */
    function getInput () {
        return JHtml::_('joomdoc.parents', $this->value, $this->name, $this->id);
    }
}
?>