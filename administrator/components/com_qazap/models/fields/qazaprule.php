<?php
/**
 * qazaprule.php
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
class JFormFieldQazaprule extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Qazaprule';
	
	protected $_rules = array();

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
		$operation = isset($this->element['operation']) ? (int) $this->element['operation'] : 1;
		
		if(!isset($this->_rules[$operation]))
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
					->select('a.calculation_rule_name, a.id')
					->from('#__qazap_taxes AS a')
					->where('a.type_of_arithmatic_operation = ' . (int) $operation);
			$db->setQuery($sql);
			$this->_rules[$operation] = $db->loadObjectList();			
		}
		
		$options = array(JHtml::_('select.option', (int) 0, (string) JText::_('COM_QAZAP_SELECT')));
		
		if(!empty($this->_rules[$operation]))
		{
			foreach($this->_rules[$operation] as $rule)
			{
				$options[] = JHtml::_('select.option', (int) $rule->id, $rule->calculation_rule_name);
			}			
		}
		
		return array_merge(parent::getOptions(), $options);;
	}
}
