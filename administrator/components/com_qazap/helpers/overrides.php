<?php
/**
 * overrides.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

abstract class QZOverrides
{
	protected static $_tagHelperDone;

	public static function setup()
	{
		$app = JFactory::getApplication();
		
		if($app->isAdmin())
		{
			return;
		}
		
		self::overrideTagsHelper();
		self::overrideTagModel();
	}

	protected static function overrideTagsHelper()
	{
		static $checked = false;
		
		if($checked)
		{
			return;
		}
		
		$checked = true;
		
		// retrieve the JHelperTags class script
		$filename = JPATH_ROOT  . DS . 'libraries' . DS . 'cms' . DS . 'helper' . DS . 'tags.php';
		$handle = fopen($filename, 'r');
		$response = fread($handle, filesize($filename));
		
		// make safe for eval()
		$response = str_replace('<?php', '', $response);
		
		// Existing codes in the core Tags helper file
		$finds = array();
		$finds[] = "->select('m.type_alias, m.content_item_id, m.core_content_id, count(m.tag_id) AS match_count,  MAX(m.tag_date) as tag_date, MAX(c.core_title) AS core_title')";
		$finds[] = "->select('MAX(c.core_alias) AS core_alias, MAX(c.core_body) AS core_body, MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access')";
		$finds[] = "->select('MAX(c.core_metadata) AS core_metadata, MAX(c.core_created_user_id) AS core_created_user_id, MAX(c.core_created_by_alias) AS core_created_by_alias')";
		$finds[] = "->join('INNER', '#__content_types AS ct ON ct.type_alias = m.type_alias')";

		$coreCodesChanged = false;
		
		// We need to check if core codes are changed or updated
		foreach($finds as $find)
		{
			if(strpos($response, $find) === false)
			{
				$coreCodesChanged = true;
				break;
			}
		}
		
		// Codes are still valid so we can replace them now
		if(!$coreCodesChanged)
		{			
			$replaces = array();
			
			$case = array();
			$case[0] = "m.type_alias, m.content_item_id, m.core_content_id, count(m.tag_id) AS match_count,  MAX(m.tag_date) as tag_date, ";
			$case[1]  = " (CASE m.type_alias ";
			$case[1] .= "WHEN ' . \$db->quote('com_qazap.product') . ' THEN pd.product_name ";
			$case[1] .= "ELSE MAX(c.core_title) ";
			$case[1] .= "END) AS core_title";
			
			$replaces[] = "->select('" . implode($case) . "')";
					
			$case = array();
			$case[0]  = "(CASE m.type_alias ";
			$case[0] .= "WHEN ' . \$db->quote('com_qazap.product') . ' THEN pd.product_alias ";
			$case[0] .= "ELSE MAX(c.core_alias) ";
			$case[0] .= "END) AS core_alias";
			$case[1]  = ", (CASE m.type_alias ";
			$case[1] .= "WHEN ' . \$db->quote('com_qazap.product') . ' THEN pd.short_description ";
			$case[1] .= "ELSE MAX(c.core_body) ";
			$case[1] .= "END) AS core_body";		
			$case[2] = ", MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access";
			
			$replaces[] = "->select('" . implode($case) . "')";
			
			$case = array();
			$case[0]  = "(CASE m.type_alias ";
			$case[0] .= "WHEN ' . \$db->quote('com_qazap.product') . ' THEN pd.metadata ";
			$case[0] .= "ELSE MAX(c.core_metadata) ";
			$case[0] .= "END) AS core_metadata";
			$case[1] = ", MAX(c.core_created_user_id) AS core_created_user_id, MAX(c.core_created_by_alias) AS core_created_by_alias";
			
			$replaces[] = "->select('" . implode($case) . "')";
			
			$replaces[] = "->join('INNER', '#__content_types AS ct ON ct.type_alias = m.type_alias')->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = m.content_item_id AND pd.language = ' . \$db->quote(\$this->getCurrentLanguage()))";	
			
			// Lets replace the original codes by the new codes		
			$response = str_replace($finds, $replaces, $response);
		}		

		eval($response);
		self::$_tagHelperDone = true;	
	}
	
	protected static function overrideTagModel()
	{
		if(!self::$_tagHelperDone)
		{
			return;
		}
		
		static $checked = false;
		
		if($checked)
		{
			return;
		}
		
		$checked = true;
		
		// retrieve the TagsModelTag class script
		$filename = JPATH_ROOT  . DS . 'components' . DS . 'com_tags' . DS . 'models' . DS . 'tag.php';
		$handle = fopen($filename, 'r');
		$response = fread($handle, filesize($filename));
		
		// make safe for eval()
		$response = str_replace('<?php', '', $response);
		
		$find = "->where(\$this->_db->quoteName('c.core_title') . ' LIKE ' . \$this->_db->quote('%' . \$this->state->get('list.filter') . '%'));";
		
		$coreCodesChanged = false;
		
		if(strpos($response, $find) === false)
		{
			$coreCodesChanged = true;
		}
		
		if(!$coreCodesChanged)
		{
			$replace = "->where(\$this->_db->quoteName('c.core_title') . ' LIKE ' . \$this->_db->quote('%' . \$this->state->get('list.filter') . '%') . ' OR ' . \$this->_db->quoteName('pd.product_name') . ' LIKE ' . \$this->_db->quote('%' . \$this->state->get('list.filter') . '%'));";
			$response = str_replace($find, $replace, $response);
		}		
		
		eval($response);			
	}
	
}