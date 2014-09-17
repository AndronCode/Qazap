<?php
/**
 * object.php
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

class QZObject extends JObject 
{
	protected $_db = null;
	
	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $properties  Either and associative array or another
	 *                              object to set the initial properties of the object.
	 *
	 * @since   1.0
	 */
	public function __construct($properties = null)
	{
		parent::__construct($properties);
	}
	
	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   1.0
	 */
	public function set($property, $value = null)
	{
		if(property_exists($this, $property))
		{
			$previous = isset($this->$property) ? $this->$property : null;
			$this->$property = $value;
			return $previous;
		}
	}
	
	protected function getDbo()
	{
		if($this->_db === null)
		{
			$this->_db = JFactory::getDbo();
		}
		
		return $this->_db;
	}
	
}

?>