<?php

/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * product Table class
 */
class QazapTableProduct extends JTable 
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db) 
	{		
		parent::__construct('#__qazap_products', 'product_id', $db);
		
		$this->_observers = new JObserverUpdater($this); 
		JObserverMapper::attachAllObservers($this);
		JObserverMapper::addObserverClassToClass('JTableObserverTags', 'QazapTableProduct', array('typeAlias' => 'com_qazap.product'));
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '') 
	{
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');
		$user = JFactory::getUser();
		
		if(($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state','com_qazap') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		//JTableObserverTags::createObserver($this, array('typeAlias' => 'com_qazap.product'));

		//Support for multiple field: images
		if(isset($array['images']) && is_array($array['images']))
		{
			$array['images'] = json_encode($array['images']);
		}			

		//Support for multiple field: related_categories
		if (isset($array['related_categories']) && is_array($array['related_categories']))
		{
			$array['related_categories'] = array_filter($array['related_categories']);
			$array['related_categories'] = json_encode($array['related_categories']);
		}

		//Support for multiple field: related_products
		if (isset($array['related_products']) && is_array($array['related_products']))
		{
			$array['related_products'] = array_filter($array['related_products']);
			$array['related_products'] = json_encode($array['related_products']);
		}

		if (isset($array['membership']) && is_array($array['membership']))
		{
			$array['membership'] = array_filter($array['membership']);
			$array['membership'] = json_encode($array['membership']);
		}
		
		$array['product_baseprice'] = (float) $array['product_baseprice'];
		
		$array['product_customprice'] = (float) $array['product_customprice'];

		if(empty($array['product_customprice']))
		{
			$array['product_customprice'] = '';
		}		

		if ($array['access'] == "")
		{
			$this->setError('COM_QAZAP_ACCESS_BLANK');
			return false;	
		}
		
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);

			if ((int) $registry->get('minimum_purchase_quantity', 0) < 0)
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', JText::_('COM_QAZAP_CONFIG_LBL_DEFAULT_MINIMUM_PURCHASE_QUANTITY')));

				return false;
			}

			if ((int) $registry->get('maximum_purchase_quantity', 0) < 0)
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', JText::_('COM_QAZAP_CONFIG_LBL_DEFAULT_MAXIMUM_PURCHASE_QUANTITY')));

				return false;
			}
			
			if ((int) $registry->get('purchase_quantity_steps', 0) < 0)
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', JText::_('COM_QAZAP_CONFIG_LBL_DEFAULT_PURCHASE_QUANTITY_STEPS')));

				return false;
			}			

			// Converts the width and height to an absolute numeric value:
			$minimum_purchase_quantity = abs((int) $registry->get('minimum_purchase_quantity', 0));
			$maximum_purchase_quantity = abs((int) $registry->get('maximum_purchase_quantity', 0));
			$purchase_quantity_steps = abs((int) $registry->get('purchase_quantity_steps', 0));		

			// Sets the width and height to an empty string if = 0
			$registry->set('minimum_purchase_quantity', ($minimum_purchase_quantity ? $minimum_purchase_quantity : ''));
			$registry->set('maximum_purchase_quantity', ($maximum_purchase_quantity ? $maximum_purchase_quantity : ''));
			$registry->set('purchase_quantity_steps', ($purchase_quantity_steps ? $purchase_quantity_steps : ''));

			$array['params'] = (string) $registry;
		}		
	
		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 */
	public function check() 
	{
	  //If there is an ordering column and this is a new row then get the next ordering value
	  if (property_exists($this, 'ordering') && $this->product_id == 0) 
	  {
			$this->ordering = self::getNextOrder();
	  }

		return parent::check();
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;		
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->$k)
		{
			// Existing item
			$this->modified_time		= $date->toSql();
			$this->modified_by			= $user->get('id');
		}
		else
		{
			// New contact. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}		
		
		return parent::store($updateNulls);
	}

	/**
	 * Method to get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;

		// This is a article under a category.
		if ($this->category_id)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__qazap_categories'))
				->where($this->_db->quoteName('category_id') . ' = ' . (int) $this->category_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
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
   * @since    1.0.4
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
    
    
	protected function filter_callback($val) 
	{
    $val = trim($val);
    return $val != '';
	}
    
	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function activate($pks = null, $state = 1, $userId = 0)
	{
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
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('block') . ' = ' . (int) $state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
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
	 * Method to set the featured state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function featured($pks = null, $state = 1, $userId = 0)
	{
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
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('featured') . ' = ' . (int) $state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
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
}
