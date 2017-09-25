<?php	

/**
 * @version	$Id$
 * @package	Joomla.Administrator
 * @subpackage	JoomDOC
 * @author   	ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

class JoomDOCTableField extends JTable {

	public function __construct (&$db) {
		parent::__construct('#__joomdoc_field', 'id', $db);
	}

	public function store($updateNulls = false)
	{
		$db = $this->getDbo();
		
		$result = parent::store($updateNulls);
		
		if ($result) {
			
			$jdc = $db->getTableColumns('#__joomdoc');
			$flt = $db->getTableColumns('#__joomdoc_flat');
			
			$fname = 'field' . $this->id;
			
			$params = new JRegistry($this->params);
			
			$default = JString::trim($params->get('default'));
			$default = $default != '' ? 'DEFAULT ' . $db->quote($default) : '';
			
			switch ($this->type) {
				default:
				case JOOMDOC_FIELD_TEXT:
				case JOOMDOC_FIELD_SELECT:
				case JOOMDOC_FIELD_MULTI_SELECT:
				case JOOMDOC_FIELD_CHECKBOX:
                case JOOMDOC_FIELD_SUGGEST:
					$type = 'MEDIUMTEXT';
					break;
				case JOOMDOC_FIELD_DATE:
					$type = 'DATE';
					break;
				case JOOMDOC_FIELD_RADIO:
					$type = 'TINYINT(1)';
					break;
				case JOOMDOC_FIELD_TEXTAREA:
				case JOOMDOC_FIELD_EDITOR:
					$type = 'TEXT';
					break;
			}
			
			if (empty($jdc[$fname]))
				$db->setQuery("ALTER TABLE #__joomdoc ADD $fname $type NOT NULL $default")->query();
			else 
				$db->setQuery("ALTER TABLE #__joomdoc CHANGE $fname $fname $type NOT NULL $default")->query();
			if (empty($flt[$fname]))
				$db->setQuery("ALTER TABLE #__joomdoc_flat ADD $fname $type NOT NULL $default")->query();
			else
				$db->setQuery("ALTER TABLE #__joomdoc_flat CHANGE $fname $fname $type NOT NULL $default")->query();
			
			$rules = new stdClass();
            $jform = JRequest::getVar('jform');
            if (is_array($jform)) {
                foreach (JArrayHelper::getValue($jform, 'rules') as $rule => $usergroups) {
                    $rules->$rule = new stdClass();
                    foreach ($usergroups as $usergroup => $value)
                        if ($value !== '')
                            $rules->$rule->$usergroup = $value;
                }
			
                $query = $db->getQuery(true);
                $query->update('#__assets')->set('rules = ' . $db->quote(json_encode($rules)))->where('name = ' . $db->quote($this->_getAssetName()));
                $db->setQuery($query)->query();
            }			
		}
		return $result;
	}

	public function delete($pk = null)
	{
		$result = parent::delete($pk);
		if ($result) {
			$joomdoc = $this->_db->getTableColumns('#__joomdoc');
			if (isset($joomdoc['field'.$this->id])) {
				$this->_db->setQuery('ALTER TABLE #__joomdoc DROP field' . (int) $pk)->query();
				$this->_db->setQuery('ALTER TABLE #__joomdoc_flat DROP field' . (int) $pk)->query();
			}
		}
		return $result;
	}

	public function bind($src, $ignore = array())
	{
		if (isset($src['params']) && is_array($src['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['params']);
			$src['params'] = $registry->toString();
		}
		return parent::bind($src, $ignore);
	}
	
	protected function _getAssetName () 
	{
		return 'com_joomdoc.field.'.$this->id;
	}
	
	protected function _getAssetTitle () 
	{
		return $this->title;
	}
}