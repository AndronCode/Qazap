<?php
/**
 * brand.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelBrand extends JModelItem 
{
	
	protected $_item = array();
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.6
	*/
	public function __construct($config = array()) 
	{
		parent::__construct($config);
	}

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		
		$pk = $app->input->getInt('brand_id', 0);
		$this->setState('brand.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}


	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0
	*/
	public function getTable($type = 'manufacturer', $prefix = 'QazapTable', $config = array())
	{
		// Include admin tables path
		JTable::addIncludePath(QZPATH_TABLE_ADMIN);
		return JTable::getInstance($type, $prefix, $config);
	}
	
	
	public function getItem($pk = null) 
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('brand.id');		
		
		if (!isset($this->_item[$pk]))
		{
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
					->select('a.*')
					->from('#__qazap_manufacturers AS a');
			
			// Join category table
			$sql->select('b.manufacturer_category_name')
					->join('INNER', '#__qazap_manufacturercategories AS b ON b.id = a.manufacturer_category');
			
			$sql->where('a.id = ' . (int) $pk);
			
			try
			{
				$db->setQuery($sql);
				$brandDetails = $db->loadObject();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if (empty($brandDetails))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_BRAND_NOT_FOUND'));
			}			

			$brandDetails->images = (!empty($brandDetails->images) && is_string($brandDetails->images)) 
																	? json_decode($brandDetails->images) 
																	: array();
															
			$this->_item[$pk] = $brandDetails;

		}
		
		return $this->_item[$pk];
	}
    
}
