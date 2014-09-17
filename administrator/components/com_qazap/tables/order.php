<?php
/**
 * order.php
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
 * order Table class
 */
class QazapTableOrder extends JTable 
{
	//public $products = array();

	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_order', 'order_id', $db);
	}

	public function setProducts($products)
	{
		$this->products = $products;
	}
	
	public function setShopname($shopname)
	{
		$this->shop_name = $shopname;
	}	
	/**
	* Overloaded bind function to pre-process the params.
	*
	* @param	array		Named array
	* @return	null|string	null is operation was satisfactory, otherwise returns an error
	* @see		JTable:bind
	* @since	1.0.0
	*/
    public function bind($array, $ignore = '') 
	{        
		if(isset($array['billing_address']) && is_array($array['billing_address']))
		{
			$array['billing_address'] = json_encode($array['billing_address']);
		}

		if(isset($array['shipping_address']) && is_array($array['shipping_address']))
		{
			$array['shipping_address'] = json_encode($array['shipping_address']);
		}		
		

		return parent::bind($array, $ignore);
	}
    
	/**
	* Method to store a row in the database from the JTable instance properties.
	* If a primary key value is set the row with that primary key value will be
	* updated with the instance property values.  If no primary key value is set
	* a new row will be inserted into the database with the properties from the
	* JTable instance.
	*
	* @param   boolean  $updateNulls  True to update fields even if they are null.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTable/store
	* @since   1.0.0
	*/
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->order_id)
		{
			// Existing item
			$this->modified_on = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_on)
			{
				$this->created_on = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}		
		
		return parent::store($updateNulls);
	}	
    
	/**
	* This function convert an array of JAccessRule objects into an rules array.
	* @param type $jaccessrules an arrao of JAccessRule objects.
	*/
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();
		foreach($jaccessrules as $action => $jaccess)
		{
			$actions = array();
			foreach($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool)$allow);
			}
			$rules[$action] = $actions;
		}
		return $rules;
	}

	/**
	* Overloaded check function
	*/
    public function check() 
	{
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->order_id == 0) 
		{
			$this->ordering = self::getNextOrder();
		}
		return parent::check();
    }

	/**
	* Method to set the publishing state for a row or list of rows in the database
	* table.  The method respects checked out rows by other users and will attempt
	* to checkin rows that it can after adjustments are made.
	*
	* @param    mixed    An optional array of primary key values to update.  If not
	*                    set the instance property value is used.
	* @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
	* @param    integer The user id of the user performing the operation.
	* @return    boolean    True on success.
	* @since    1.0.0
	*/
    public function publish($pks = null, $state = 1, $userId = 0) 
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) 
		{
			if ($this->$k) 
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else 
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) 
		{
			$checkin = '';
		} 
		else 
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int) $state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) 
		{
			// Checkin each row.
			foreach ($pks as $pk) 
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) 
		{
		    $this->state = $state;
		}

		$this->setError('');
		return true;
	}
    
	/**
	* Define a namespaced asset name for inclusion in the #__assets table
	* @return string The asset name 
	*
	* @see JTable::_getAssetName 
	*/
	protected function _getAssetName() 
	{
		$k = $this->_tbl_key;
		return 'com_qazap.order.' . (int) $this->$k;
	}
}
