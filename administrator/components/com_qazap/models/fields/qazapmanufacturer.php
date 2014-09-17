<?php
/**
 * qazapmanufacturer.php
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
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Qazap Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldQazapManufacturer extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'QazapManufacturer';	

	protected static $manufacturers = null;	
	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   1.0.0
	*/
	protected function getOptions()
	{
		if(static::$manufacturers === null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
						->select('m.id, m.manufacturer_name')
						->from('#__qazap_manufacturers AS m')
						->join('LEFT', '#__qazap_manufacturercategories AS mc ON mc.id = m.manufacturer_category')
						->where('m.state = 1')
						->where('mc.state = 1');
			$db->setQuery($query);
			static::$manufacturers = $db->loadObjectList();			
		}
		
		$showSelect = isset($this->element['select']) ? $this->element['select'] : false;
		$options = array();
		
		if($showSelect)
		{
			$options[] = JHtml::_('select.option', '', JText::_('COM_QAZAP_SELECT'));
		}
		
		if(!empty(static::$manufacturers))
		{
			foreach(static::$manufacturers as $manufacturer)
			{
				$options[] = JHtml::_('select.option', (int) $manufacturer->id, $manufacturer->manufacturer_name);
			}			
		}

		return array_merge(parent::getOptions(), $options);
	}
}
