<?php
/**
 * product.php
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
 * Form Field class for the Qazap Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldProduct extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'Product';

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
		
		$multiple = $this->element['multiple'] ? ' multiple="' . (int) $this->element['multiple'] . '"' : '';
		$lang = JFactory::getLanguage();
		$present_language = $lang->getTag();
		$productID = JRequest::getInt('product_id', 0);	
		$options = array();
		$attr = '';
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
				->select(array('a.product_id','b.product_name'))
				->from('#__qazap_products AS a')
				->join('LEFT', '#__qazap_product_details AS b on b.product_id = a.product_id')
				->where('b.language = '.$db->Quote($present_language))
				->where('a.state = 1')
				->where('a.product_id !='. $productID);
		$db->setQuery($sql);
		$products = $db->loadObjectList();
		$options[] = JHtml::_('select.option','0', '-NA-');
		
		foreach($products as $product)
		{
			$options[] = JHtml::_('select.option', (string) $product->product_id, $product->product_name);
		}
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($multiple), 'value', 'text', $this->value, $this->id);

		return $html;
	}
}
