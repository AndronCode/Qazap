<?php
/**
 * usergrouppricing.php
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

/**
 * Form Field class for the Joomla Platform.
 * Field for assigning permissions to groups for a given asset
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JAccess
 * @since       1.0.0
 */
class JFormFieldUsergrouppricing extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Usergrouppricing';


	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0.0
	 * @todo:   Add access check.
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');

		$jinput = JFactory::getApplication()->input;
		$product_id = $jinput->get('product_id', 0);

		$groups = $this->getUserGroups();

		$html[] = '<table id="usergroup_pricing_table" class="table table-striped table-bordered">';

		// The table heading.
		$html[] = '	<thead>';
		$html[] = '	<tr>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_USERGROUPS_LABEL'), JText::_('COM_QAZAP_FORM_USERGROUPS_DESC'), 0).'">' . JText::_('COM_QAZAP_FORM_USERGROUPS_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_BASEPRICE'), 0).'">' . JText::_('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_FINALPRICE'), 0).'">' . JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE') . '</span>';
		$html[] = '		</th>';		
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CUSTOMPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_CUSTOMPRICE'), 0).'">' . JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CUSTOMPRICE') . '</span>';
		$html[] = '		</th>';			
		$html[] = '	</tr>';
		$html[] = '	</thead>';

		$html[] = '	<tbody>';

		$product = new stdClass;
		$product->product_id = $this->form->getValue('product_id', 0);
		$product->dbt_rule_id = $this->form->getValue('dbt_rule_id', 0);
		$product->dat_rule_id = $this->form->getValue('dat_rule_id', 0);
		$product->tax_rule_id = $this->form->getValue('tax_rule_id', 0);
		$product->product_customprice = null;
		
		foreach ($groups as $group)
		{
			if (!isset($this->value[$group->usergroup_id]))
			{
				$this->value[$group->usergroup_id] = '';
			}
			
			$user_price_id = isset($this->value[$group->usergroup_id]['user_price_id']) ? $this->value[$group->usergroup_id]['user_price_id'] : 0;
			$product_baseprice = isset($this->value[$group->usergroup_id]['product_baseprice']) ? $this->value[$group->usergroup_id]['product_baseprice'] : '';
			$product_customprice = isset($this->value[$group->usergroup_id]['product_customprice']) ? $this->value[$group->usergroup_id]['product_customprice'] : '';
			
			$product->product_baseprice = isset($this->value[$group->usergroup_id]['product_baseprice']) ? $this->value[$group->usergroup_id]['product_baseprice'] : 0;
			
			$product_salesprice = QazapHelper::getFinalPrice($product, 'product_salesprice', true);			
			
			$html[] = '	<tr>';
			$html[] = '		<th class="acl-groups left">';
			$html[] = '			' . $group->text;
			$html[] = '				<input type="hidden" name="' . $this->name . '[' . $group->usergroup_id . '][user_price_id]" id="' . $this->id . $group->usergroup_id . '" value="' . $user_price_id . '"/>';			
			$html[] = '		</th>';
			$html[] = '		<td>';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $group->usergroup_id . '][product_baseprice]" id="' . $this->id . $group->usergroup_id . '" value="' . $product_baseprice . '"/>';
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $group->usergroup_id . '][product_salesprice]" id="' . $this->id . $group->usergroup_id . '_salesprice" value="' . $product_salesprice . '" disabled="disabled" />';
			$html[] = '		</td>';	
			$html[] = '		<td>';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $group->usergroup_id . '][product_customprice]" id="' . $this->id . $group->usergroup_id . '_customprice" value="' . $product_customprice . '" class="input-product-custom-price" />';
			$html[] = '		</td>';					
			$html[] = '	</tr>';
		}
		
		$html[] = '	</tbody>';
				
		$html[] = '	</table>';
		return implode("\n", $html);
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getUserGroups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
					->select('a.id AS usergroup_id, a.title AS text')
					->from('#__usergroups AS a')
					->leftJoin('#__qazap_memberships AS b ON b.jusergroup_id = a.id')
					->where('b.state = 1 OR a.id = 1')
					->order('a.id ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
