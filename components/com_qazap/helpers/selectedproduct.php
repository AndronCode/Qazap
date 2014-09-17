<?php
/**
 * selectedproduct.php
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
defined('_JEXEC') or die;
if(!class_exists('QZOrderItemNode'))
{
	require (QZPATH_HELPER_ADMIN . DS . 'orderitem.php');
}

abstract class QZSelectedProduct
{
	public static function getItem($product_id, $attr_ids = null, $membership_id = null, $quantity = null, $forceload = false)
	{
		$options = array();
		$helper = QZProducts::getInstance($options);
		$selection = $helper->getSelection($product_id, $attr_ids, $membership_id, $quantity, $forceload);

		if($selection)
		{
			$node = new QZSelectedProductNode($selection, $quantity);
			$node->decodeData();
			$node->prepareDisplayData();
			$node->checkStock();
		}
		else
		{
			$node = false;
		}
		
		return $node;
	}	
}

class QZSelectedProductNode extends QZOrderItemNode {

	public $product_id = null;
	public $group_id = null;
	public $product_name = null;
	public $category_name = null;
	public $parent_id = null;
	public $product_sku = null;
	public $featured = null;
	public $vendor = null;
	public $vendor_admin = null;
	public $vendor_group_id = null;
	public $shop_name = null;	
	public $manufacturer_name = null;
	public $manufacturer_images = null;	
	public $manufacturer_id = null;
	public $category_id = null;
	public $product_baseprice = null;
	public $product_basepricewithVariants = null;
	public $product_salespriceBeforeDiscount = null;
	public $product_salesprice = null;
	public $product_tax = null;
	public $product_discount = null;
	public $product_quantity = null;		
	public $product_totalprice = null;
	public $commission = null;
	public $total_tax = null;
	public $total_discount = null;	
	public $multiple_pricing = null;
	public $dbt_rule_id = null;
	public $dat_rule_id = null;
	public $tax_rule_id = null;
	public $in_stock = null;
	public $ordered = null;
	public $booked_order = null;
	public $product_length = null;
	public $product_length_uom = null;
	public $product_width = null;
	public $product_height = null;
	public $product_weight = null;
	public $product_weight_uom = null;
	public $product_packaging = null;
	public $product_packaging_uom = null;
	public $units_in_box = null;
	public $images = null;
	public $related_categories = null;
	public $related_products = null;
	public $params = null;
	public $short_description = null;
  public $product_quantity_prices = null;  
	public $user_id = null;
  public $product_attributes = null;
	public $product_membership = null;
	public $prices = null;	
	public $slug = null;
	public $info_msg = null;
	
	
	function __construct($data = null, $quantity = 0) 
	{
		parent::__construct($data, $quantity);
		
		if ($data)
		{
			$this->setProperties($data);
			
			if(isset($data->prices))
			{
				$this->setProperties($data->prices);
			}			
		}

		return true;			
	}
	
	public function checkQuantity()
	{
		if(!parent::checkQuantity())
		{
			$this->info_msg = parent::getError();
		}
	}	
	
	public function checkStock($old_quantity = 0)
	{
		if(!parent::checkStock($old_quantity))
		{
			$this->info_msg = parent::getError();
		}		
	}
	
	public function prepareDisplayData()
	{
		$pricing_properties = array('product_attributes_price', 'product_baseprice', 'product_basepriceAfterTax', 'product_basepriceBeforeTax', 'product_basepricewithVariants', 'product_customprice', 'product_dat', 'product_dbt', 'product_discount', 'product_membership_price', 'product_salespriceBeforeDiscount', 'product_salesprice', 'product_tax');
		foreach($pricing_properties as $property)
		{
			if(isset($this->prices->$property))
			{
				$this->prices->$property = QZHelper::currencyDisplay($this->prices->$property);
			}
		}
	}
}