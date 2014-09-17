<?php
/**
 * orderitem.php
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
defined('_JEXEC') or die;

abstract class QZOrderitem 
{	
	
	public static function getItem($product_id, $attr_ids = null, $membership_id = null, $quantity = null, $user_id = null, $forceload = false)
	{
		$options = array();
		if($user_id)
		{
			$options['user_id'] = $user_id;
		}
		
		$helper = QZProducts::getInstance($options);
		$product = $helper->getSelection($product_id, $attr_ids, $membership_id, $quantity, $forceload);

		if($product)
		{
			$node = new QZOrderItemNode($product, $quantity);
			//$node->prepareSaveData();
		}
		else
		{
			$node = false;
		}
		
		return $node;
	}
	
	public static function getCartItem($product_id, $attr_ids = null, $membership_id = null, $quantity = null, $forceload = false)
	{
		$options = array();	
		$helper = QZProducts::getInstance($options);
		$product = $helper->getSelection($product_id, $attr_ids, $membership_id, $quantity, $forceload);

		if($product)
		{
			$node = new QZCartItemNode($product, $quantity);
/*			$node->createGroup();
			$node->calculateCommission();*/
/*			$node->checkQuantity();
			$node->checkStock();*/
			
			//$node->prepareSaveData();
		}
		else
		{
			$node = false;
		}
		
		return $node;
	}	
	
}



class QZOrderItemNode extends QZObject
{
	public $order_items_id = null;
	public $deleted = null;
	public $order_id = null;
	public $product_id = null;
	public $group_id = null;
	public $vendor = null;
	public $product_quantity = null;
	public $stock_affected = 0;
	public $stock_booked = 0;
	public $product_baseprice = null;
	public $product_basepricewithVariants = null;
	public $product_salesprice = null;
	public $product_tax = null;
	public $product_discount = null;
	public $product_totalprice = null;
	public $commission = null;
	public $total_tax = null;
	public $total_discount = null;
	public $order_status = null;
	public $product_name = null;
	public $product_sku = null;
	public $product_attributes = null;
	public $product_membership = null;
	
	public $download_id = null;
	public $download_passcode = null;
	public $file_id = null;
	public $downloadable_file = null;
	public $download_mime_type = null;
	public $download_start_date = null;
	public $download_count = null;
	public $last_download = null;
	public $download_block = null;
	
	public $created_on = null;
	public $created_by = null;
	public $modified_on = null;
	public $modified_by = null;	

	protected $user_id = null;
	//protected $product_name = null;
	protected $in_stock = null;
	protected $ordered = null;
	protected $booked_order = null;	
	protected $params = null;
	protected $_decoded = null;

	public function __construct($data = null, $quantity = 0) 
	{
		if ($data)
		{
			$this->setProperties($data);
			
			if(isset($data->prices))
			{
				$this->setProperties($data->prices);
			}
			
			$this->product_quantity = (int) $quantity;		
		}

		return true;	
	}

	
	public function calculateTotals()
	{
		if($this->product_quantity !== null)
		{
			$this->total_tax = ($this->product_tax * $this->product_quantity);
			$this->total_discount = ($this->product_discount * $this->product_quantity);
			$this->product_totalprice = ($this->product_salesprice * $this->product_quantity);
		}
		
		return true;
	}
	
	public function createGroup()
	{
		if(!$this->group_id)
		{
			$this->group_id = (string) $this->product_id;
			
			if(is_array($this->product_attributes) && count($this->product_attributes))
			{
				$attribute_ids = array();
				foreach($this->product_attributes as $attribute)
				{
					$attribute_ids[] = $attribute->attribute_id;
				}
				
				$this->group_id .= '::' . implode(':', $attribute_ids) . '::';
			}
			else
			{
				$this->group_id .= '::0::';
			}
			
			if($this->product_membership)
			{
				$this->group_id .= (string) $this->product_membership->id;
			}
			else
			{
				$this->group_id .= '0';
			}
		}
		
		return true;
	}

	public function groupToArray($group_id)
	{
		if(strpos($group_id, '::') === false)
		{
			$this->setError('Invalid Group ID');
			return false;
		}
		
		$product_group_id = explode('::', $group_id);
		$product_id = $product_group_id[0];
		$product_attr_ids = isset($product_group_id[1]) ? $product_group_id[1] : 0;
		$product_attr_ids = (strpos($product_attr_ids, ':') === false) ? $product_attr_ids : explode(':', $product_attr_ids);
		$membership_id = isset($product_group_id[2]) ? $product_group_id[2] : 0;
		$return = array($product_id, (array) $product_attr_ids, $membership_id);

		return  $return;	
	}
	
	public function calculateCommission()
	{
		$vendorModel = QZApp::getModel('vendor');
		if(!$commission = $vendorModel->getCommission($this->vendor))
		{
			$this->setError($vendorModel->getError());
			return false;
		}

		if($this->product_totalprice === null)
		{
			$this->calculateTotals();
		}
		
		$this->commission = ($this->product_totalprice * (float) $commission) / 100;
		
		return true;
	}
	
	public function checkStock($old_quantity = 0)
	{
		$product_quantity = ($this->product_quantity - $old_quantity);
		$presentStock = ($this->in_stock - $this->booked_order);
		$previousStock = ($presentStock + $this->stock_affected + $this->stock_booked);
		$newProductStock = ($previousStock - $product_quantity);
		
		// If product stock is less than ordered quantity
		if($newProductStock < 0) 
		{
			$this->setError(JText::sprintf('COM_QAZAP_INSUFFICIENT_PRODUCT_STOCK', $this->product_name));
			return false;
		}
		
		if(is_array($this->product_attributes) && count($this->product_attributes))
		{
			foreach($this->product_attributes as $attribute)
			{
				$presentAttrStock = ($attribute->stock - $attribute->booked_order);
				$previousAttrStock = ($presentAttrStock + $this->stock_affected + $this->stock_booked);
				$newAttrStock = ($previousAttrStock - $product_quantity);
				
				if($attribute->check_stock && $newAttrStock < 0)
				{
					$this->setError(JText::sprintf('COM_QAZAP_INSUFFICIENT_ATTRIBUTE_STOCK', $attribute->title, $attribute->value));
					// Not enough stock available for %s : %s
					return false;
				}
			}			
		}	
		
		return true;
	}
	
	public function checkQuantity()
	{
		$this->params = QZApp::getConfig(false, $this->params);
		
		$minimum_purchase_quantity = $this->params->get('minimum_purchase_quantity', 1);
		$minimum_quantity_checked = ($this->product_quantity >= $minimum_purchase_quantity);
		
		$maximum_purchase_quantity = $this->params->get('maximum_purchase_quantity', 100);
		$maximum_quantity_checked = ($this->product_quantity <= $maximum_purchase_quantity);
		
		$purchase_quantity_steps = $this->params->get('purchase_quantity_steps', 1);
		
		if($this->product_quantity == $minimum_purchase_quantity)
		{
			$purchase_step_checked = true;
		}
		else
		{
			$interval = (($this->product_quantity - $minimum_purchase_quantity) / $purchase_quantity_steps);
			$purchase_step_checked = (floor($interval) == $interval);			
		}

		
		if($this->product_quantity <= 0)
		{
			$this->setError(JText::_('COM_QAZAP_ERROR_INVALID_QUANTITY'));
			return false;
		}
		elseif(!$minimum_quantity_checked)
		{
			$this->setError(JText::sprintf('COM_QAZAP_ERROR_MINIMUM_QUANTITY', $minimum_purchase_quantity));
			return false;
		}
		elseif(!$maximum_quantity_checked)
		{
			$this->setError(JText::sprintf('COM_QAZAP_ERROR_MAXIMUM_QUANTITY', $maximum_purchase_quantity));
			return false;
		}
		elseif(($this->product_quantity != $minimum_purchase_quantity) && !$purchase_step_checked)
		{
			$this->setError(JText::sprintf('COM_QAZAP_ERROR_STEP_QUANTITY', $purchase_quantity_steps));
			return false;			
		}

		return true;		
	}
	
	public function recalculate()
	{
		$config = QZApp::getConfig();
		$multple_pricing = $config->get('multiple_product_pricing', false);
		$user = JFactory::getUser();
		
		if($multple_pricing && $this->user_id != $user->get('id'))
		{
			$options = array();
			$helper = QZProducts::getInstance($options);
			list($product_id, $attr_ids, $membership_id) = $this->groupToArray($this->group_id);
			$product = $helper->getSelection($product_id, $attr_ids, $membership_id, $this->product_quantity);					
		}
		else
		{
			if(!class_exists('QZProductNode'))
			{
				require_once (QZPATH_HELPER_ADMIN . DS . 'products.php'); 
			}
			$this->user_id = $user->get('id');
			$product = new QZProductNode($this);
			$product->setPrices();			
		}

		if($product)
		{			
			$this->setProperties($product);
			
			if(isset($product->prices))
			{
				$this->setProperties($product->prices);
			}				
		}
		
		$this->createGroup();
		$this->calculateTotals();
		$this->calculateCommission();
		$this->checkQuantity();
		$this->checkStock();
	}	
	
	public function prepareSaveData()
	{
		if(is_array($this->product_attributes))
		{
			$this->product_attributes = json_encode($this->product_attributes);
		}
		
		if(is_object($this->product_membership))
		{
			$this->product_membership = json_encode($this->product_membership);
		}
	}
	
	public function decodeData()
	{
		if($this->_decoded === null)
		{
			$encodesFields = array(
												'manufacturer_images',
												'images',
												'related_categories',
												'related_products',
												'product_attributes',
												'product_membership',
												'prices'
												);
												
			foreach($encodesFields as $field)
			{
				if(isset($this->$field) && $this->$field && is_string($this->$field))
				{
					$this->$field = json_decode($this->$field);
				}
			}
			
			$this->_decoded = true;			
		}

	}
	
	public function cleanProperties()
	{
		if(property_exists($this, 'params'))
		{
			unset($this->params);
		}
	}
	
	public function getVarients()
	{
		if($this->_decoded === null)
		{
			$this->decodeData();
		}
		
		if(!empty($this->product_attributes))
		{
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapcartattributes');
			$display = array();
			
			foreach($this->product_attributes as $data) 
			{
				$data = (object) $data;
				$cartattributes = $dispatcher->trigger('onDisplayAfterSelection', array(&$data));
				if(!empty($data->display))
				{
					$display[] = $data->display;
				}				
			}					
		}
		
		if(!empty($this->product_membership))
		{
			$this->product_membership = (object) $this->product_membership;
			$display[] = '<span class="membership-title">' . JText::_('COM_QAZAP_SUBSCRIPTION') . ': </span><span class="membership-name">' . $this->product_membership->plan_name . '</span>';
		}		
		
		if(!empty($display))
		{
			$display = '<div class="product-varient">' . implode('</div><div class="product-varient">', $display) . '</div>';
		}
		else
		{
			$display = null;
		}		
		
		return $display;		
	}	
	
	public function getWeight($uom_id)
	{
		if(!isset($this->product_weight) || !isset($this->product_weight_uom) || empty($this->product_weight_uom))
		{
			return null;
		}
		
		if(empty($this->product_weight))
		{
			return 0;
		}
		
		return QZUom::convert($this->product_weight, $this->product_weight_uom, $uom_id, 'weight');	
	}
		
}


class QZCartItemNode extends QZOrderItemNode 
{
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

	
	public function __construct($data = null, $quantity = 0) 
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
	
	
	public function recalculate()
	{
		$config = QZApp::getConfig();
		$multple_pricing = $config->get('multiple_product_pricing', false);
		$user = JFactory::getUser();
		
		if($multple_pricing && $this->multiple_pricing == 1 && $this->user_id != $user->get('id'))
		{
			$options = array();
			$helper = QZProducts::getInstance($options);
			list($product_id, $attr_ids, $membership_id) = $this->groupToArray($this->group_id);
			$product = $helper->getSelection($product_id, $attr_ids, $membership_id, $this->product_quantity);					
		}
		else
		{
			if(!class_exists('QZProductNode'))
			{
				require_once (QZPATH_HELPER_ADMIN . DS . 'products.php'); 
			}
			$this->user_id = $user->get('id');
			$product = new QZProductNode($this);
			$product->setPrices();			
		}

		if($product)
		{
			parent::__construct($product, $this->product_quantity);
			$this->setProperties($product);
			if(isset($product->prices))
			{
				$this->setProperties($product->prices);
			}				
		}
		
		$this->createGroup();
		$this->calculateTotals();
		$this->calculateCommission();
		$this->checkQuantity();
		$this->checkStock();
	}
	
	public function cleanProperties()
	{
		parent::cleanProperties();				
		$this->prices->cleanProperties('product_attributes');
		$this->prices->cleanProperties('product_membership');
	}	
	
	public function getOrderItem()
	{
		return new QZOrderItemNode($this, $this->product_quantity);
	}
	
}
?>