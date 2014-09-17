<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

/**
* Methods supporting a list of Qazap records.
*/
class QazapModelCompare extends JModelList
{
	
	protected $_compare = null;
	
	protected $_list = array();
	protected $_products = null;
	
	/**
	* Model typeAlias string. Used for version history.
	*
	* @var        string
	*/
	public $typeAlias = 'com_qazap.compare';	
	/**
	* Constructor.
	*
	* @param			array    An optional associative array of configuration settings.
	* @see				JController
	* @since			1.0.0
	*/
	public function __construct($config = array()) 
	{
		parent::__construct($config);	 
	}
  
	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		$product_ids = $app->input->get('p_id', array(), 'array');
		$this->setState('product.ids', $product_ids);

		// Load the parameters.
		$params	= QZApp::getConfig(true);
		$this->setState('params', $params);
		
		parent::populateState($ordering, $direction);
	} 
	
	public function setCompareSession()
	{
		$session = JFactory::getSession();
		$this->_compare = array_unique((array) $this->_compare);
		
		$session->set('QazapCompare', $this->_compare, 'qazap');
			
		return true;
	}
	
	public function clearCompareSession()
	{
		$session = JFactory::getSession();
		$session->clear('QazapCompare', 'qazap');
		
		return true;	
	}
	
	public function getCompareSession()
	{
		$session = JFactory::getSession();
		$this->_compare = $session->get('QazapCompare', array(), 'qazap');	
		
		return $this->_compare;
	}
	
	public function add($product_id)
	{	
		$params = $this->getState('params');
		
		$maximum_no_of_products = $params->get('compare_product_number');
		//print($maximum_no_of_products);exit;
		$this->getCompareSession();
		
		if(in_array($product_id, $this->_compare))
		{
			$this->setError('This product is already added to comparison');
			return false;
		}

		$this->_compare[] = $product_id;
		
		$count = count($this->_compare);
		
		if($count > $maximum_no_of_products)
		{
			array_shift($this->_compare);
		}
		
		$this->setCompareSession();
		
		return $this->_compare;
	}
	
	
	
	public function getList()
	{
		$app = JFactory::getApplication();
		$url_ids = $app->input->get('p_id', array(), 'array');

		$product_ids = !empty($url_ids) ? $url_ids : $this->getCompareSession();
		$product_ids = array_unique($product_ids);
		
		if(empty($product_ids))
		{
			return null;
		}
		
		$hash = md5(serialize($product_ids));
		
		if(!isset($this->_list[$hash]))
		{
			$helper = QZProducts::getInstance();
			$products = $helper->getList(0, $product_ids);
			
			if($products === false && $helper->getError())
			{
				$this->setError($helper->getError());
				return false;
			}
			
			if(empty($products))
			{
				$this->_list[$hash] = null;
			}
			else
			{
				$this->_products = $products;
				$products = $this->flipDiagonally($products);
				$products['custom_fields'] = 	$this->flipDiagonally($products['custom_fields'], true, 'title');
				$products['attributes'] = 	$this->flipDiagonally($products['attributes'], true, 'title');	
				
				$orderBy = array('product_id','ordering,state', 'block', 'parent_id','product_name', 'rating', 'review_count', 'images', 'prices', 'shop_name','manufacturer_name','product_baseprice','product_sku', 'ordered', 'booked_order', 'membership', 'short_description','product_description', 'category_name', 'custom_fields', 'attributes', 'product_attributes', 'product_membership', 'category_params','product_quantity_prices','user_id', 'product_length','product_length_uom','product_width','product_height','product_weight','product_weight_uom','product_packaging','product_packaging_uom','units_in_box', 'related_categories','related_products', 'vendor','vendor_admin','vendor_group_id','featured','manufacturer_email','manufacturer_category','manufacturer_description','manufacturer_url','manufacturer_images','urls','manufacturer_id','category_id','access','category_access','product_customprice','multiple_pricing', 'dbt_rule_id', 'dat_rule_id','tax_rule_id','in_stock', 'slug', 'metakey', 'metadesc', 'metadata', 'params','checked_out','checked_out_time','created_by', 'created_time', 'modified_by', 'modified_time', 'hits','product_alias');
				
				$products = $this->sortArrayByArray($products, $orderBy);

				$this->_list[$hash] = $products;
			}						
		}
		
		return $this->_list[$hash];
	}
	
	public function getProducts()
	{
		if($this->_products === null)
		{
			if($this->getList() === false)
			{
				$this->setError($this->getError());
				return false;
			}
		}
		
		return $this->_products;
	}
	
	public function remove($product_id)
	{
		$this->getCompareSession();
		
		if(in_array($product_id, $this->_compare))
		{
			$key = array_search($product_id, $this->_compare);	
			unset($this->_compare[$key]);
			
			$this->setCompareSession();
		}

		return true;
	}
	
	public function removeAll()
	{
		$this->getCompareSession();
		$this->_compare = array();
		$this->setCompareSession();
		return true;
	}

	
	function flipDiagonally($array, $forceNull = false, $titleKey = null) 
	{
	    $return = array();
		$allKeys = array();
		
	    foreach ($array as $key => $subarr) 
		{	
			if(!in_array($key, $allKeys))
			{
				$allKeys[] = $key;
			}
			
			if(!empty($subarr))
			{
				if($subarr instanceof QZProductNode)
				{					
					$subarr->membership = $subarr->getMemberships();
					$subarr->getAttributes();
					$subarr->getCustomfields();
					
					$params = $subarr->getParams();
					$params->merge($this->getState('params'));
					$this->setState('params', $params);
										
					if($subarr->in_stock - $subarr->booked_order > 0 || !$params->get('enablestockcheck')) 
					{
						if($image = $params->get('in_stock_image', null) ) 
						{
							
							$subarr->availability = '<img src="' . JUri::base(true) . $image . '" alt="' . JText::_('COM_QAZAP_IN_STOCK') . '" />';
						}
						else
						{
							$subarr->availability = JText::_('COM_QAZAP_IN_STOCK');
						}
					}
					else
					{
						if($image = QZApp::getConfig()->get('out_of_stock_image', null))
						{
							$subarr->availability = '<img src="' . JUri::base(true) . $image . '" alt="' . JText::_('COM_QAZAP_OUT_OF_STOCK') . '" />';
						}
						else
						{
							$subarr->availability = JText::_('COM_QAZAP_OUT_OF_STOCK');
						}
					}		
				}
				
				foreach ($subarr as $subkey => $subvalue) 
				{
					if(strpos($subkey, '_') === 0)
					{
						continue;	
					}
					
					if(!empty($titleKey))
					{
						if(!isset($return[$subkey]))
						{
							$return[$subkey] = new stdClass;
						}
						
						if(!isset($return[$subkey]->$titleKey) || empty($return[$subkey]->$titleKey))
						{
							$return[$subkey]->$titleKey = (isset($subvalue->$titleKey) && !empty($subvalue->$titleKey)) ? $subvalue->$titleKey : '';
						}						
						
						if(!isset($return[$subkey]->data))
						{
							$return[$subkey]->data = array();
						}
						
						$return[$subkey]->data[$key] = $subvalue;
					}
					else
					{
						if(!isset($return[$subkey]))
						{
							$return[$subkey] = array();
						}
						
						$return[$subkey][$key] = $subvalue;						
					}
				}				
			}
		}
		
		if(!empty($titleKey))
		{
			if($forceNull && !empty($return))
			{
				foreach($return as &$item) 
				{
					if(isset($item->data) && !empty($item->data))
					{
						foreach($allKeys as $key)
						{
							if(!array_key_exists($key, $item->data))
							{
								$item->data[$key] = null;
							}							
						}
						
						$item->data = $this->sortArrayByArray($item->data, $allKeys);						
					}					
				}
			}			
		}
		else
		{
			if($forceNull && !empty($return))
			{
				foreach($return as &$out)
				{
					foreach($allKeys as $key)
					{
						if(!array_key_exists($key, $out))
						{
							$out[$key] = null;
						}						
					}
					
					$out = $this->sortArrayByArray($out, $allKeys);
				}
			}			
		}
		
	    return $return;
	}
	
	protected function sortArrayByArray(Array $array, Array $orderArray) 
	{
		$ordered = array();

		foreach($orderArray as $key) 
		{
			if(array_key_exists($key, $array)) 
			{
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}

		return $ordered + $array;
	}
}
