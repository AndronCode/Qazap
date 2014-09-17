<?php
/**
 * customfieldtype.php
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
 * customfieldtype Table class
 */
class QazapTablecustomfieldtype extends JTable 
{

	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_customfieldtype', 'id', $db);
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
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');
		if(($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state','com_qazap') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		if (isset($array['params']) && is_array($array['params'])) 
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
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
		if (property_exists($this, 'ordering') && $this->id == 0) 
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
	* Method to store a node in the database table.
	*
	* @param   boolean  $updateNulls  True to update null values as well.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/store
	* @since   1.0.0
	*/
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->id)
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
}