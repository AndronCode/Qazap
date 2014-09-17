<?php
/**
 * quantitypricing.php
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
class JFormFieldQuantitypricing extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'Quantitypricing';


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
		
		$html[] = '<table id="quantity_pricing_table" class="table table-striped table-bordered">';

		// The table heading.
		$html[] = '	<thead>';
		$html[] = '	<tr>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_QUANTITY_PRICE_MIN_QAUANTITY'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_QUANTITY_PRICE_MIN_QAUANTITY'), 0).'">' . JText::_('COM_QAZAP_FORM_LBL_PRODUCT_QUANTITY_PRICE_MIN_QAUANTITY') . '&nbsp;*</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action hasTooltip" title="'.JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_QUANTITY_PRICE_MAX_QAUANTITY'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_QUANTITY_PRICE_MAX_QAUANTITY'), 0).'">' . JText::_('COM_QAZAP_FORM_LBL_PRODUCT_QUANTITY_PRICE_MAX_QAUANTITY') . '&nbsp;*</span>';
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
		$html[] = '		<th class="center">';
		$html[] = '			<span class="acl-action">#</span>';
		$html[] = '		</th>';		
		$html[] = '	</tr>';
		$html[] = '	</thead>';
		
		$html[] = '	<tbody>';
		
		if(empty($this->value)) 
		{
			$default = array();
			$default['min_quantity'] = 0;
			$default['max_quantity'] = 0;
			$default['product_baseprice'] = 0.0000000000;
			$default['product_salesprice'] = '';
			$default['product_customprice'] = '';
			$default['quantity_price_id'] = 0;
			$this->value[] = $default;
		}
		
		$product = new stdClass;
		$product->product_id = $this->form->getValue('product_id', 0);
		$product->dbt_rule_id = $this->form->getValue('dbt_rule_id', 0);
		$product->dat_rule_id = $this->form->getValue('dat_rule_id', 0);
		$product->tax_rule_id = $this->form->getValue('tax_rule_id', 0);
		$product->product_customprice = null;
		
		foreach ($this->value as $key => $item)
		{
			$product->product_baseprice = $item['product_baseprice'];			
			
			$product_salesprice = QazapHelper::getFinalPrice($product, 'product_salesprice', true);	
			$product_salesprice = $product_salesprice ? $product_salesprice : 0;		
			
			$html[] = '	<tr>';
			$html[] = '		<td class="left min-quantity-col">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $key . '][min_quantity]" id="' . $this->id .'_'. $key . '_min_quantity" value="' . $item['min_quantity'] . '" data-index="'.$key.'" class="input-small" />';			
			$html[] = '		</td>';
			$html[] = '		<td class="left max-quantity-col">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $key . '][max_quantity]" id="' . $this->id .'_'. $key . '_max_quantity" value="' . $item['max_quantity'] . '" data-index="'.$key.'" class="input-small" />';			
			$html[] = '		</td>';	
			$html[] = '		<td class="left">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $key . '][product_baseprice]" id="' . $this->id .'_'. $key . '_product_baseprice" value="' . $item['product_baseprice'] . '" data-index="'.$key.'" class="input-medium" />';			
			$html[] = '		</td>';
			$html[] = '		<td class="left">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $key . '][product_salesprice]" id="' . $this->id .'_'. $key . '_product_salesprice" value="' . $product_salesprice . '" data-index="'.$key.'" class="input-medium calculated-price" disabled="disabled" />';	
			$html[] = '		</td>';
			$html[] = '		<td class="left">';
			$html[] = '				<input type="text" name="' . $this->name . '[' . $key . '][product_customprice]" id="' . $this->id .'_'. $key . '_product_customprice" value="' . $item['product_customprice'] . '" data-index="'.$key.'" class="input-medium input-product-custom-price" />';			
			$html[] = '		</td>';	
			$html[] = '		<td class="center">';
			$html[] = '				<input type="hidden" name="' . $this->name . '[' . $key . '][quantity_price_id]" id="' . $this->id .'_'. $key . '_quantity_price_id" value="' . $item['quantity_price_id'] . '" data-index="'.$key.'" />';	
			$html[] = '				<span class="delete-me" onclick="return Qazap.deleteMe(this);"><i class="icon-cancel"></i></span>';					
			$html[] = '		</td>';					
			$html[] = '	</tr>';
		}
		
		$html[] = '	</tbody>';
				
		$html[] = '	</table>';
		$html[] = '	<button type="button" class="btn btn-primary pull-right" onclick="Qazap.add_pricing_row(\'#quantity_pricing_table\');"><i class="icon-plus"></i> '.JText::_('COM_QAZAP_ADD_MORE').'</button>';

		return implode("\n", $html);
	}
}
