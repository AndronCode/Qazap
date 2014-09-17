<?php
/**
 * member.php
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
* member Table class
*/
class QazapTableMember extends JTable 
{
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_members', 'id', $db);
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

		if(!$array['user_id'])
		{
			$this->setError(JText::_('COM_QAZAP_USER_ERROR'));
			return false;
		}

		if(isset($array['effected_items']) && is_array($array['effected_items']))
		{
			$array['effected_items'] = array_map('intval', array_values($array['effected_items']));
			$array['effected_items'] = json_encode($array['effected_items']);
		}

		return parent::bind($array, $ignore);
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
			// We don't touch either of these if they are set.
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
