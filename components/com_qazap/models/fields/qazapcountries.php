<?php
/**
 * qazapcountries.php
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
defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldQazapCountries extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'QazapCountries';

	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   1.0.0
	*/
	protected function getInput()
	{
		$class = 'class="qazap-country-field"';
		$multiple = $this->element['multiple'] ? ' multiple="' . $this->element['multiple'] . '"' : '';
		$attr = $class.$multiple;
		
		$showSelect = $this->element['showselect'] ? $this->element['showselect'] : false;
		$showAll = $this->element['showall'] ? $this->element['showall'] : false;		
		
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
							->select(array('id', 'country_name'))
							->from('`#__qazap_countries`')
							->where('state = 1');
		$db->setQuery($sql);
		$countries = $db->loadObjectList();
		
		$options = array();
		
		if($showSelect) 
		{
			$options[] = JHtml::_('select.option', '', JText::_('JSELECT'));
		}
		
		if($showAll) 
		{
			$options[] = JHtml::_('select.option', (int) 0, JText::_('JALL'));
		}
		
		foreach($countries as $country)
		{
			$options[] = JHtml::_('select.option', (int) $country->id, $country->country_name);
		}
		
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

		return $html;
	}

}
