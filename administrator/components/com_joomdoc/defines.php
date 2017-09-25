<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

//joomla
define('JOOMDOC_ISJ3', version_compare(JVERSION, '3.0.0') >= 0);

// main
define('JOOMDOC', 'JoomDOC');
define('JOOMDOC_LOG', false);
define('JOOMDOC_OPTION', 'com_joomdoc');
define('JOOMDOC_ACCESS_PREFIX', 'JoomDOCAccess');
define('JOOMDOC_HELPER_PREFIX', 'JoomDOC');
define('JOOMDOC_MODEL_PREFIX', 'JoomDOCModel');
define('JOOMDOC_MODEL_SITE_PREFIX', 'JoomDOCSiteModel');
define('JOOMDOC_TABLE_PREFIX', 'JoomDOCTable');
define('JOOMDOC_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . JOOMDOC_OPTION);
define('JOOMDOC_SITE', JPATH_SITE . '/components/' . JOOMDOC_OPTION);
define('JOOMDOC_MANIFEST', JOOMDOC_ADMINISTRATOR . '/joomdoc.xml');
define('JOOMDOC_CONFIG', JOOMDOC_ADMINISTRATOR . '/config.xml');
define('JOOMDOC_PARAMS_WINDOW_HEIGHT', 600);
define('JOOMDOC_PARAMS_WINDOW_WIDTH', 800);
define('JOOMDOC_VERSION_DIR', '.versions');
define('JOOMDOC_URL_FEATURES', 'http://www.artio.net/joomdoc/features');
define('JOOMDOC_URL_ESHOP', 'http://www.artio.net/e-shop/joomdoc');
$manifest = JApplicationHelper::parseXMLInstallFile(JOOMDOC_MANIFEST);
define('JOOMDOC_VERSION_ALIAS', JFilterOutput::stringURLSafe($manifest['version']));

//upgrade
define('ARTIO_UPGRADE_MANIFEST', JOOMDOC_ADMINISTRATOR . '/joomdoc.xml');
define('ARTIO_UPGRADE_NEWEST_VERSION_URL', 'http://www.artio.cz/updates/joomla/joomdoc4/version');
define('ARTIO_UPGRADE_LICENSE_URL', 'http://www.artio.net/license-check');
define('ARTIO_UPGRADE_UPGRADE_URL', 'http://www.artio.net/joomla-auto-upgrade');
define('ARTIO_UPGRADE_OPTION', 'com_joomdoc');
define('ARTIO_UPGRADE_CAT', 'joomdoc4');
define('ARTIO_UPGRADE_ALIAS', 'joomdoc');


define('ARTIO_UPGRADE_OPTION_PCKG', 'com_joomdoc4_std');


//folders
define('JOOMDOC_CONTROLLERS', JOOMDOC_ADMINISTRATOR . '/controllers');
define('JOOMDOC_TABLES', JOOMDOC_ADMINISTRATOR . '/tables');
define('JOOMDOC_HELPERS', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc');
define('JOOMDOC_ACCESS', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/access');
define('JOOMDOC_HTML', JOOMDOC_ADMINISTRATOR . '/libraries/joomdoc/html');
define('JOOMDOC_MODELS', JOOMDOC_ADMINISTRATOR . '/models');
define('JOOMDOC_SITE_MODELS', JOOMDOC_SITE . '/models');
define('JOOMDOC_PATH_IMAGES', JOOMDOC_SITE . '/assets/images');
define('JOOMDOC_PATH_ICONS', JOOMDOC_SITE . '/assets/images/icons');
define('JOOMDOC_BUTTONS', JOOMDOC_ADMINISTRATOR . '/libraries/joomla/html/toolbar/button');
define('JOOMDOC_FORMS', JOOMDOC_ADMINISTRATOR . '/libraries/joomla/form/fields');

define('JOOMDOC_ASSETS', str_replace('/administrator/', '/', JURI::base(true) . '/components/' . JOOMDOC_OPTION . '/assets/'));
//define('JOOMDOC_ASSETS', JURI::root().'components/'.JOOMDOC_OPTION.'/assets/');
define('JOOMDOC_IMAGES', JOOMDOC_ASSETS . 'images/');
define('JOOMDOC_ICONS', JOOMDOC_ASSETS . 'images/icons/');

//entities
define('JOOMDOC_JOOMDOC', 'joomdoc');
define('JOOMDOC_CATEGORIES', 'categories');
define('JOOMDOC_CATEGORY', 'category');
define('JOOMDOC_DOCUMENTS', 'documents');
define('JOOMDOC_DOCUMENT', 'document');
define('JOOMDOC_UPGRADE', 'upgrade');
define('JOOMDOC_UPGRADE_MIGRATION', 'upgrademigration');
define('JOOMDOC_FILE', 'file');
define('JOOMDOC_MANUAL', 'manual');
define('JOOMDOC_CHANGELOG', 'changelog');
define('JOOMDOC_SUPPORT', 'support');
define('JOOMDOC_LICENSES', 'licenses');
define('JOOMDOC_LICENSE', 'license');
define('JOOMDOC_FIELDS', 'fields');
define('JOOMDOC_FIELD', 'field');
define('JOOMDOC_FIELD_TEXT', 1);
define('JOOMDOC_FIELD_DATE', 2);
define('JOOMDOC_FIELD_RADIO', 3);
define('JOOMDOC_FIELD_SELECT', 4);
define('JOOMDOC_FIELD_CHECKBOX', 5);
define('JOOMDOC_FIELD_TEXTAREA', 6);
define('JOOMDOC_FIELD_EDITOR', 7);
define('JOOMDOC_FIELD_MULTI_SELECT', 8);
define('JOOMDOC_FIELD_SUGGEST', 9);
define('JOOMDOC_MIGRATION', 'migration');

//tasks filesystem
define('JOOMDOC_TASK_UPLOADFILE', 'uploadfile');
define('JOOMDOC_TASK_NEWFOLDER', 'newfolder');
define('JOOMDOC_TASK_DELETEFILE', 'deletefile');

//tasks common
define('JOOMDOC_TASK_SAVEORDER', 'saveorder');
define('JOOMDOC_TASK_EDIT', 'edit');
define('JOOMDOC_TASK_ORDERUP', 'orderup');
define('JOOMDOC_TASK_ORDERDOWN', 'orderdown');
define('JOOMDOC_TASK_ADD', 'add');
define('JOOMDOC_TASK_PUBLISH', 'publish');
define('JOOMDOC_TASK_UNPUBLISH', 'unpublish');
define('JOOMDOC_TASK_ARCHIVE', 'archive');
define('JOOMDOC_TASK_CHECKIN', 'checkin');
define('JOOMDOC_TASK_DELETE', 'delete');
define('JOOMDOC_TASK_TRASH', 'trash');
define('JOOMDOC_TASK_UNTRASH', 'untrash');
define('JOOMDOC_TASK_APPLY', 'apply');
define('JOOMDOC_TASK_SAVE', 'save');
define('JOOMDOC_TASK_SAVE2NEW', 'save2new');
define('JOOMDOC_TASK_CANCEL', 'cancel');
define('JOOMDOC_TASK_SAVE2COPY', 'save2copy');
define('JOOMDOC_TASK_DOWNLOAD', 'download');
define('JOOMDOC_TASK_RENAME', 'rename');
define('JOOMDOC_TASK_COPY', 'copy');
define('JOOMDOC_TASK_MOVE', 'move');
define('JOOMDOC_TASK_PASTE', 'paste');
define('JOOMDOC_TASK_RESET', 'reset');
define('JOOMDOC_TASK_RESTORE', 'restore');
define('JOOMDOC_TASK_REVERT', 'revert');
define('JOOMDOC_TASK_REFRESH', 'refresh');
define('JOOMDOC_TASK_REINDEX', 'reindex');
define('JOOMDOC_TASK_FLAT', 'flat');
// licenses
define('JOOMDOC_TASK_DEFAULT', 'defaults');
define('JOOMDOC_TASK_UNDEFAULT', 'undefaults');
define('JOOMDOC_TASK_UPDATEMOOTREE', 'updatemootree');

//actions component
define('JOOMDOC_CORE_ADMIN', 'core.admin');
define('JOOMDOC_CORE_MANAGE', 'core.manage');
define('JOOMDOC_CORE_LICENSES', 'core.licenses');
//actions filesystem
define('JOOMDOC_CORE_UPLOADFILE', 'core.uploadfile');
define('JOOMDOC_CORE_NEWFOLDER', 'core.newfolder');
define('JOOMDOC_CORE_DELETEFILE', 'core.deletefile');
define('JOOMDOC_CORE_RENAME', 'core.rename');
define('JOOMDOC_CORE_COPY_MOVE', 'core.copy.move');
define('JOOMDOC_CORE_VIEWFILEINFO', 'core.viewfileinfo');
define('JOOMDOC_CORE_DOWNLOAD', 'core.download');
define('JOOMDOC_CORE_ENTERFOLDER', 'core.enterfolder');
define('JOOMDOC_CORE_EDIT_WEBDAV', 'core.edit.webdav');
define('JOOMDOC_CORE_REFRESH', 'core.refresh');
//actions documents
define('JOOMDOC_CORE_CREATE', 'core.create');
define('JOOMDOC_CORE_EDIT', 'core.edit');
define('JOOMDOC_CORE_EDIT_OWN', 'core.edit.own');
define('JOOMDOC_CORE_EDIT_STATE', 'core.edit.state');
define('JOOMDOC_CORE_DELETE', 'core.delete');
define('JOOMDOC_CORE_VIEW_VERSIONS', 'core.view.versions');
define('JOOMDOC_CORE_CREATE_VERSIONS', 'core.create.versions');
define('JOOMDOC_CORE_MANAGE_VERSIONS', 'core.manage.versions');
define('JOOMDOC_CORE_UNTRASH', 'core.untrash');
define('JOOMDOC_CORE_RECEIVE_NOTIFICATION', 'core.receive.notification');

//filter fields
//Joomla core
define('JOOMDOC_FILTER_ORDERING', 'list.ordering');
define('JOOMDOC_FILTER_DIRECTION', 'list.direction');
define('JOOMDOC_FILTER_START', 'list.start');
define('JOOMDOC_FILTER_LINKS', 'list.links');
define('JOOMDOC_FILTER_LIMIT', 'list.limit');
//JoomDOC
define('JOOMDOC_FILTER_TITLE', 'title');
define('JOOMDOC_FILTER_FILENAME', 'filename');
define('JOOMDOC_FILTER_CATEGORY', 'category_id');
define('JOOMDOC_FILTER_ACCESS', 'access');
define('JOOMDOC_FILTER_SEARCH', 'search');
define('JOOMDOC_FILTER_STATE', 'state');
define('JOOMDOC_FILTER_PATHS', 'paths');
define('JOOMDOC_FILTER_PATH', 'path');
define('JOOMDOC_FILTER_CREATED', 'created');
define('JOOMDOC_FILTER_HITS', 'hits');
define('JOOMDOC_FILTER_UPLOAD', 'upload');
define('JOOMDOC_FILTER_ID', 'id');
define('JOOMDOC_FILTER_PUBLISH_UP', 'publish_up');
define('JOOMDOC_FILTER_PUBLISH_DOWN', 'publish_down');
define('JOOMDOC_FILTER_KEYWORDS', 'filter');

//data types
define('JOOMDOC_INT', 'int');
define('JOOMDOC_STRING', 'string');
define('JOOMDOC_ARRAY', 'array');

//ordering
define('JOOMDOC_ORDER_ID', 'id');
define('JOOMDOC_ORDER_PATH', 'path');
define('JOOMDOC_ORDER_UPLOAD', 'upload');
define('JOOMDOC_ORDER_TITLE', 'title');
define('JOOMDOC_ORDER_ACCESS', 'access');
define('JOOMDOC_ORDER_STATE', 'state');
define('JOOMDOC_ORDER_FILE_STATE', 'file_state');
define('JOOMDOC_ORDER_ORDERING', 'ordering');
define('JOOMDOC_ORDER_PUBLISH_UP', 'publish_up');
define('JOOMDOC_ORDER_HITS', 'hits');
define('JOOMDOC_ORDER_CATEGORY', 'category');
define('JOOMDOC_ORDER_NEWEST', 'newest');
define('JOOMDOC_ORDER_OLDEST', 'oldest');
define('JOOMDOC_ORDER_DESC', 'desc');
define('JOOMDOC_ORDER_ASC', 'asc');
define('JOOMDOC_ORDER_NEXT', 'next');
define('JOOMDOC_ORDER_PREV', 'prev');

//state
define('JOOMDOC_STATE_PUBLISHED', 1);
define('JOOMDOC_STATE_UNPUBLISHED', 0);
define('JOOMDOC_STATE_TRASHED', -2);
define('JOOMDOC_FAVORITE', 1);
define('JOOMDOC_STANDARD', 0);
define('JOOMDOC_STATE_DEFAULT', 1);
define('JOOMDOC_STATE_UNDEFAULT', 0);

// Joomla 1.5.x user groups
define('JOOMDOC_GROUP_PUBLIC', 0);
define('JOOMDOC_GROUP_REGISTERED', 18);
define('JOOMDOC_GROUP_AUTHOR', 19);
define('JOOMDOC_GROUP_EDITOR', 20);
define('JOOMDOC_GROUP_PUBLISHER', 21);
define('JOOMDOC_GROUP_MANAGER', 23);
define('JOOMDOC_GROUP_ADMINISTRATOR', 24);
define('JOOMDOC_GROUP_SUPER_ADMINISTRATOR', 25);

// user state properties
define('JOOMDOC_USER_STATE_PATHS', 'joomdoc_user_state_paths');
define('JOOMDOC_USER_STATE_OPERATION', 'joomdoc_user_state_operation');

// items operations
define('JOOMDOC_OPERATION_COPY', 'copy');
define('JOOMDOC_OPERATION_MOVE', 'move');

// lihgtbox size
define('JOOMDOC_LIGHTBOX_WIDTH', 800);
define('JOOMDOC_LIGHTBOX_HEIGHT', 600);

define('JOOMDOC_LINK_TYPE_DETAIL', 'detail');
define('JOOMDOC_LINK_TYPE_DOWNLOAD', 'download');

// searching
define('JOOMDOC_SEARCH_ANYKEY', 0);
define('JOOMDOC_SEARCH_ALLKEY', 1);
define('JOOMDOC_SEARCH_PHRASE', 2);
define('JOOMDOC_SEARCH_REGEXP', 3);

?>
