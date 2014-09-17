<?php
/**
 * productuom.php
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

jimport('joomla.application.component.modeladmin');

/**
 * Qazap model.
 */
class QazapModelProductuom extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';


	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0
	*/
	public function getTable($type = 'Productuom', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	* Method to get the record form.
	*
	* @param	array	$data		An optional array of data for the form to interogate.
	* @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	* @return	JForm	A JForm object on success, false on failure
	* @since	1.0.0
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_qazap.productuom', 'productuom', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}

	/**
	* Method to get the data that should be injected in the form.
	*
	* @return	mixed	The data for the form.
	* @since	1.0.0
	*/
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.productuom.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
            
		}

		return $data;
	}

	/**
	* Method to get a single record.
	*
	* @param	integer	The id of the primary key.
	*
	* @return	mixed	Object on success, false on failure.
	* @since	1.0.0
	*/
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			//Do any procesing on fields here if needed

		}

		return $item;
	}

	/**
	* Prepare and sanitise the table prior to saving.
	*
	* @since	1.0.0
	*/
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_product_uom');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	/**
	* Get UOM calculation
	* 
	* @since 1.0.0
	*/
	public function convert($value, $fromUnit, $toUnit, $type = null)
	{
		if(!$exchange = $this->getExchange($fromUnit, $toUnit, $type))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$value = (float) ($value * $exchange);

		return $value;
	}	
	
	public function getExchange($fromUnit, $toUnit, $type = null)
	{
		static $cache = array();
		$hash = md5('From:' . $fromUnit . '.To:' . $toUnit . '.Type:' . $type);
		
		if(!isset($cache[$hash]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('id, exchange_rate')
						->from('#__qazap_product_uom AS a')
						->where('a.id IN (' . (int) $fromUnit . ',' . (int) $toUnit . ')');
						
			if(!empty($type))
			{
				$query->where('a.product_attributes = '. $db->quote($type));
			}						
			
			try
			{
				$db->setQuery($query);
				$exchangeRate = $db->loadObjectList('id');
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;				
			}
			
			if(count($exchangeRate) <> 2)
			{
				$this->setError(JText::_('COM_QAZAP_INVALID_PRODUCT_UOM'));
				return false;
			}

			$value = ($exchangeRate[$toUnit]->exchange_rate)/($exchangeRate[$fromUnit]->exchange_rate);
			$cache[$hash] = $value;
		}
		
		return $cache[$hash];
	}
	

}