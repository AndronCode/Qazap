<?php
/**
 * membership.php
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

class JFormFieldMembership extends JFormField
{
	protected $type = 'membership';

	protected function getInput()
	{		
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		
	
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($this->value && ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true'))
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
					->select('a.plan_name')
					->from('`#__qazap_memberships` AS a')
					->where('a.state = 1')
					->where('a.id = '.$this->value);
			$db->setQuery($sql);
			$plan_name = $db->loadResult();
				
			$return  = '<input type="text" name="' . $this->name . '" value="' . $plan_name . '" readonly="readonly" />';
			$return .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
			
			return $return;
		}
		else
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
				->select(array('a.id', 'a.plan_name'))
				->from('`#__qazap_memberships` AS a')
				->where('a.state = 1');
			$db->setQuery($sql);
			$memberships = $db->loadObjectList();	
			$options = array();
			$options[] = JHtml::_('select.option', '*', JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL' , 'Plan Name'));
			
			foreach($memberships as $membership)
			{
				$options[] = JHtml::_('select.option', (string) $membership->id, $membership->plan_name);
			}
			
			return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);			}
	}
}
