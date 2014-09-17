<?php
/**
 * order_history.php
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
 * order History Table class
 */
class QazapTableorder_history extends JTable 
{
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_order_history', 'id', $db);
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
		return parent::bind($array, $ignore);
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
		if (property_exists($this, 'ordering') && $this->id == 0) 
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
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
		return 'com_qazap.order_history.' . (int) $this->$k;
	}   
}
