<?php
/**
 * user.php
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

/**
 * Methods supporting a list of Qazap records.
 */
class QZUser
{

	protected static $_users = array();
	protected static $_userfields = array();
	protected static $_cache = array();
	
	public static function get($id = null, $type = 'bt', $info_id = NULL)
	{
		$id = (int) $id;
		
		if(!$id)
		{
			$id = JFactory::getUser()->get('id');
		}
		
		$type = strtolower($type);
		
		if(!in_array($type, array('bt', 'st')))
		{
			return false;
		}		
		
		$hash = md5('id:'.$id.'.type:'.$type.'.info_id:'.$info_id);
		
		if(!(isset(self::$_users[$hash])))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$common_fields = array('id', 'ordering', 'state', 'user_id', 'address_type');
			$user_fields = self::getUserFields($type);
			
			$fields = array_merge($common_fields, $user_fields);			
			$fields = array_map(function($val) { return 'u.'.$val;}, $fields);
			
			$query->select($fields);
			$query->from('#__qazap_userinfos AS u');
			
			$query->select('(CASE WHEN COUNT(v.id) > 0 AND v.state = 1 THEN 1 ELSE 0 END) AS activeVendor, '. 
											'v.id AS vendor_id');
			$query->join('LEFT', '#__qazap_vendor AS v ON v.vendor_admin = u.user_id');
			
			if($type == 'bt')
			{
				$query->where('u.address_type = ' . $db->quote('bt'));
			}
			else
			{
				$query->where('u.address_type = ' . $db->quote('st'));
			}									
			
			$query->where('u.user_id = ' . (int) $id);
			
			if($info_id)
			{
				$query->where('u.id = ' . (int) $info_id);
			}
			
			$db->setQuery($query);			
			
			try 
			{
				$user = $db->loadAssoc();
			} 
			catch (RuntimeException $e) 
			{
				throw new RuntimeException($e->getMessage());
				return false;
			}		

			if(!isset($user['id']) && $id > 0)
			{
				$query->clear();
				$query->select('(CASE WHEN COUNT(v.id) > 0 AND v.state = 1 THEN 1 ELSE 0 END) AS activeVendor, '. 
							'v.id AS vendor_id');
				$query->from('#__qazap_vendor AS v');
				$query->where('v.vendor_admin = ' . $id);
				$db->setQuery($query);			
				
				try 
				{
					$vendor = $db->loadAssoc();
			
				} 
				catch (RuntimeException $e) 
				{
					throw new RuntimeException($e->getMessage());
					return false;
				}
				
				if(!empty($vendor))
				{
					$user = array_merge($user, $vendor);
				}										
			}			
			
			if(!empty($user))
			{	
				self::$_users[$hash] = new QZUserNode($user);
			}
			else
			{
				self::$_users[$hash] = false;
			}
		}
		
		return self::$_users[$hash];
	}
	
	
	public static function getUserFields($type = 'bt')
	{
		if(!isset(self::$_userfields[$type]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			if (!isset(self::$_cache['getfield.sql']))
			{
				$query->clear();
				$query->select($query->castAsChar('f.field_title'));
				$query->from('#__qazap_userfields AS f');
				$query->where('f.field_type != '.$db->quote('fieldset'));
				$query->where('f.state = 1');
				$query->where('f.%s = 1');
				$query->group('f.id, f.field_title');
				$query->order('f.ordering ASC');
				self::$_cache['getfield.sql'] = (string) $query;
			}
			
			if($type == 'bt')
			{
				$db->setQuery(sprintf(self::$_cache['getfield.sql'], (string) 'show_in_userbilling_form'));
				$user_fields = $db->loadColumn();					
			}
			else
			{
				$db->setQuery(sprintf(self::$_cache['getfield.sql'], (string) 'show_in_shipment_form'));
				$user_fields = $db->loadColumn();
			}
			
			if(empty($user_fields))
			{
				self::$_userfields[$type] = array();
			}
			else
			{
				self::$_userfields[$type] = $user_fields;
			}			
		}
		
		return self::$_userfields[$type];		
	}	
	
}


class QZUserNode
{
	protected $_registry = NULL;	
	public $new = false;
	public $isVendor = false;	
	public $juser = null;
	
	public function __construct($user = NULL) 
	{
		if(!$user)
		{
			return false;
		}
		
		// Overload user data
		foreach($user as $k => $v) 
		{
			$this->{$k} = $v;
		}	
			
		// Check for new user
		if(!$this->id)
		{
			$this->new = true;
		}
		
		if($this->vendor_id > 0)
		{
			$this->isVendor = true;
		}
		
		// Add Joomla User details
		if(!$this->juser)
		{
			if($this->user_id)
			{
				$this->juser = JFactory::getUser($this->user_id);
			}
			else
			{
				$this->juser = JFactory::getUser();
			}
		}
		
		// Create a registry object for easy access to each fields
		$this->_registry = new JRegistry;
		$this->_registry->loadArray($this);		
		
		return true;			
  }
  
  
  public function get($property, $default = null)
  {
		return $this->_registry->get($property, $default);
	}
  
}

?>