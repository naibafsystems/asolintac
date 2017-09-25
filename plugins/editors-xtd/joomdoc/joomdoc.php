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

jimport('joomla.plugin.plugin');
JHtml::_('behavior.modal');
JLoader::register('JoomDOCRoute', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomdoc' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'route.php');
include_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomdoc' . DIRECTORY_SEPARATOR . 'defines.php');

class plgButtonJoomDOC extends JPlugin {
    /**
     * Constructor
     *
     * @access      protected
     * @param       object  $subject The object to observe
     * @param       array   $config  An array that holds the plugin configuration
     * @since       1.5
     */
    public function __construct (&$subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Display the button.
     *
     * @param string $name field name
     * @return JObject button settings
     */
    function onDisplay ($name) {

        $doc = JFactory::getDocument();
        /* @var $doc JDocumentHTML */

        $script[] = '//<![CDATA[';
        $script[] = 'function jSelectJoomdocDocument(id, title, url) {';
        $script[] = '  var tag = \'<a href="\' + url + \'" title="">\' + title + \'</a>\';';
        $script[] = '  jInsertEditorText(tag, \'' . addslashes($name) . '\');';
        $script[] = '  SqueezeBox.close();';
        $script[] = '}';
        $script[] = '//]]>';

        $script = implode(PHP_EOL, $script);

        $doc->addScriptDeclaration($script);

        $button = new JObject();
        $button->set('modal', true);
        $button->set('link', str_replace('&', '&amp;', JoomDOCRoute::modalDocuments(null, true)));
        $button->set('text', JText::_('PLG_EDITORS-XTD_JOOMDOC_BTN'));
        $button->set('name', 'file-add');
        $button->set('options', "{handler: 'iframe', size: {x: 770, y: 400}}");
        $button->set('class', 'btn');

        return $button;
    }
}
?>