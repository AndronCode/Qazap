<?php
/**
 * orderstates.php
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
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

JFormHelper::loadFieldClass('list');
/**
 * Supports an HTML select list of categories
 */
class JFormFieldOrderstates extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var		string
	* @since	1.0.0
	*/
	protected $type = 'orderstates';

	/**
	* Method to get a list of categories that respects access controls and can be used for
	* either category assignment or parent category assignment in edit screens.
	* Use the parent element to indicate that the field will be used for assigning parent categories.
	*
	* @return  array  The field option objects.
	*
	* @since   1.0.0
	*/
	protected function getOptions()
	{
		$options = array();
		$useglobal = $this->element['useglobal'] ? (bool) $this->element['useglobal'] : false;
		
		if($useglobal)
		{
			$options[] = JHtml::_('select.option', '', JText::_('JGLOBAL_USE_GLOBAL'));
		}
		
		if(is_string($this->value) && strpos($this->value, ','))
		{
			$this->value = explode(',', $this->value);
		}
		
		static $fields = null;
		
		if($fields === null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
							->select('status_code, status_name')
							->from('#__qazap_order_status');
			$db->setQuery($query);
			$fields = $db->loadObjectList();		
		}
		
		foreach($fields as $field)
		{
			$options[] = JHtml::_('select.option', (string) $field->status_code, JText::_($field->status_name));
		}
		
		$options = array_merge(parent::getOptions(), $options);

		return $options;		
	}
}