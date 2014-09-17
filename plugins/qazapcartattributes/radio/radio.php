<?php
/**
 * radio.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazapcartattributes Radio Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

if(!class_exists('QZApp'))
{
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');
	// Setup Qazap for autload classes
	QZApp::setup();
}

class PlgQazapCartAttributesRadio extends QZAttributePlugin
{
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}	

	/**
	* Method to display plugin input form within product edit form.
	* 
	* @param	object	$data
	* @param	array		$header
	* @param	array		$html
	* 
	* @return $header and $html		Modified header and html in array format
	* @since	1.0
	*/		
	public function onDisplayProductAdmin($data, &$header = array(), &$html = array()) 
	{		
		if($data->element != $this->_name) 
		{
			return;
		}		
		
		$thisHeader = array(
								'COM_QAZAP_FORM_LBL_PRODUCT_ATTRIBUTE_VALUE',
								'COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE',
								'COM_QAZAP_FORM_LBL_PRODUCT_IN_STOCK',
								'COM_QAZAP_FORM_LBL_PRODUCT_ORDERED_PRODUCTS',
								'COM_QAZAP_FORM_LBL_PRODUCT_BOOKED_PRODUCTS',
								'#'
								);
								
		$thisHTML = array(
								'<input type="text" name="qzattribute['.$data->ordering.'][value]" value="'.$data->value.'" />',
								'<input type="text" class="input-small" name="qzattribute['.$data->ordering.'][price]" value="'.$data->price.'" />',
								'<input type="text" class="input-small" name="qzattribute['.$data->ordering.'][stock]" value="'.$data->stock.'" />',
								'<input type="text" class="input-small" readonly="readonly" name="qzattribute['.$data->ordering.'][ordered]" value="'.$data->ordered.'" />',
								'<input type="text" class="input-small" readonly="readonly" name="qzattribute['.$data->ordering.'][booked_order]" value="'.$data->booked_order.'" />',
								'<input type="hidden" name="qzattribute['.$data->ordering.'][typeid]" value="'.$data->typeid.'" /><input type="hidden" name="qzattribute['.$data->ordering.'][id]" value="'.$data->id.'" /><input type="hidden" class="qzattribute-ordering" name="qzattribute['.$data->ordering.'][ordering]" value="'.$data->ordering.'" /><input type="hidden" name="qzattribute['.$data->ordering.'][title]" value="'.$data->title.'" /><input type="hidden" name="qzattribute['.$data->ordering.'][element]" value="'.$data->element.'" />'
								);

		$header = array_merge($header, $thisHeader);
		$html = array_merge($html, $thisHTML);
		
		return true;				
	}	
	

	/**
	* Method to display plugin input form within product add to cart form.
	* 
	* @param	object	$attribute	Data object of the attribute to be displayed
	* 
	* @return object	Modified data opne with new display @property
	* @since	1.0
	*/		
	public function onDisplayProduct(&$attribute) 
	{
		if($attribute->plugin != $this->_name) 
		{
			return;
		}
		
		jimport( 'joomla.html.html.select' );
		$config = QZApp::getConfig();
		
		$default = null;
		$options = array();
		$class  = ' class="radio"';
		$values = array();
    
		foreach($attribute->data as $item) 
		{
			if(!$default)
			{
				$default = $item->attribute_id;
			}
			
			$title = $item->value;
			
			if($attribute->params->get('display_price')) 
			{
				$item->price = (float) $item->price;
				if($item->price > 0)
				{
					$title .= ' <span class="qzattribute-price">(+' . QZHelper::currencyDisplay($item->price) . ')</span>';
				}
				elseif($item->price < 0)
				{
					$title .= ' <span class="qzattribute-price">(-' . QZHelper::currencyDisplay($item->price) . ')</span>';
				}
			}
			
			if($attribute->check_stock && $attribute->params->get('display_stock')) 
			{
				$item->stock = (float) $item->stock;
				$item->booked_order = (float) $item->booked_order;
				
				if(($item->stock - $item->booked_order) > 0)
				{
					$title .= ' <span class="qzattribute-stock in-stock text-success">' . JText::_('COM_QAZAP_IN_STOCK') . '</span>';
				}
				else
				{
					$title .= ' <span class="qzattribute-stock out-of-stock text-error">' . JText::_('COM_QAZAP_OUT_OF_STOCK') . '</span>';
				}
			}
      		
			$values[] = trim($title);
			
			$tmp = JHtml::_('select.option', (int) $item->attribute_id, trim($title));
      
			// Set some option attributes.
			$tmp->class = '';
      						
			$options[] = $tmp;
		}
		
		$required = $config->get('attribute_required', 1) ? 'required aria-required="true"' : '';
		$default = (!$config->get('attribute_default_select', 0)) ? '' : $default;    
    
		$html[] = '<fieldset id="' . $attribute->field_id . '"' . $class . $required . ' >';
    
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ((string) $option->value == (string) $default) ? ' checked="checked"' : '';
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';

			$html[] = '<input type="radio" id="' . $attribute->field_id . $i . '" name="' . $attribute->field_name . '" value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . ' />';

			$html[] = '<label for="' . $attribute->field_id . $i . '"' . $class . ' >'
				. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $attribute->field_name)) . '</label>';

			$required = '';
		}
    
		$html[] = '</fieldset>';
    
		$attribute->display = implode($html);
		$attribute->compare_display = implode(', ', $values);
    
		return true;	
	}	
	
	public function onDisplayFilter(&$filter)
	{
		if($filter->plugin != $this->_name) 
		{
			return;
		}
		
	
	}
	
	public function onAddtoCart($type,$attribute,$quantity,$return=true)
	{		
		if($type !== $this->_name) 
		{
			return;
		}
		
		$app = JFactory::getApplication();
		$params = json_decode($attribute->params);
		
		if($params->check_stock && $attribute->stock<$quantity)
		{
			$msg = $attribute->title.' '.$attribute->value;
			$app->enqueueMessage(sprintf(JText::_('COM_QAZAP_ATTR_OUT_OF_STOCK'),$msg));
			$return = false;
		}
		return $return;
	}
}
