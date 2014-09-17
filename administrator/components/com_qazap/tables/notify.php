<?php
/**
 * notify.php
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
 * review Table class
 */
class QazapTablenotify extends JTable 
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_notify_product', 'id', $db);
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
	
	public function store($updateNulls = false)
	{
		$user = JFactory::getUser();
			
		// New contact. A contact created and created_by field can be set by the user,
		// so we don't touch either of these if they are set.
		if (!(int) $this->date)
		{
			$this->date = $date->toSql();
		}
		if (empty($this->user_id))
		{
			$this->user_id = $user->get('id');
		}	
	
		return parent::store($updateNulls);
	}

}
