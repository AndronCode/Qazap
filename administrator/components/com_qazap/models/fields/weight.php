<?php
/**
 * weight.php
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
* Form Field class for the Joomla Platform.
* Supports a generic list of options.
*
* @package     Joomla.Platform
* @subpackage  Form
* @since       1.0.0
*/
class JFormFieldWeight extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'Weight';

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
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
								->select('id, product_measure_unit_name AS name')
								->from('#__qazap_product_uom')
								->where('product_attributes = ' . $db->quote('weight'));
								
		$db->setQuery($query);
		$units = $db->loadObjectList();
		
		$options = array();
		
		if(!empty($units))
		{
			foreach($units as $unit)
			{
				$options[] = JHtml::_('select.option', (int) $unit->id, htmlspecialchars($unit->name, ENT_COMPAT, 'UTF-8'));
			}			
		}
		
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
