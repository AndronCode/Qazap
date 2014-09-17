<?php
/**
 * router.php
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

if(!class_exists('QZApp'))
{
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');
	// Setup Qazap for autload classes
	QZApp::setup();
}

if(!class_exists('QazapRouterBase'))
{
	require (QZPATH_HELPER . DS . 'router' . DS . 'base.php');
}
/**
 * Routing class from com_qazap
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       3.3
 */
class QazapRouter extends QazapRouterBase
{
	
	protected function getViewKeyMap()
	{
		$map = array(
						'product' => 'product_id',
						'category' => 'category_id',
						'categories' => 'category_id',
						'shop' => 'vendor_id',
						'cart' => '',
						'profile' => '',
						'compare' => '',
						'seller' => '',
						'sellerform' => '',
						'brand' => 'brand_id',
						'orderdetails' => 'ordergroup_id'
					);
					
		return $map;
	}
	
	protected function getKey($view)
	{
		$map = $this->getViewKeyMap();
		
		if(isset($map[$view]))
		{
			return $map[$view];
		}
		
		return null;
	}
	/**
	 * Build the route for the com_qazap component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{			
			$menuItem = $this->_item;
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $this->_menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}
		
		$menuQuery = $this->getQueryFromLink($menuItem);

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_qazap')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}
		
		// Consider all categories if no category id is mentioned
		if($query['view'] == 'category' && !isset($query['category_id']))
		{
			$query['category_id'] = 0;
		}
		
		$key = $this->getKey($view);
		
		$keyCheck = (($menuItem instanceof stdClass) && isset($query[$key]) && isset($menuItem->query[$key])) ? ($menuItem->query[$key] == (int) $query[$key]) : true;
		$hasMenu = false;

		if (!empty($menuQuery) && ($menuQuery['view'] == $query['view']) && $keyCheck)
		{			
			$doReturn = true;
			$hasMenu = true;
			
			if(isset($query[$key]))
			{
				unset($query[$key]);
			}			

			if (isset($query['category_id']))
			{
				unset($query['category_id']);
			}
      
			if (isset($query['vendor_id']))
			{
				unset($query['vendor_id']);
			}

			if (isset($query['layout']))
			{
				if(isset($menuItem->query['layout']) && ($menuItem->query['layout'] == $query['layout']))
				{
					unset($query['layout']);
				}
				else
				{
					$doReturn = false;
				}
			}	
			
			if(isset($query['p_id']))
			{
				$doReturn = false;
			}
			
			if($doReturn)
			{
				unset($query['view']);
				return $segments;
			}			
		}

		$viewKeyMap = $this->getViewKeyMap();
		$qazapViews = array_keys($viewKeyMap);
    
    if(!in_array($view, $qazapViews))
    {
      return $segments;
    }
    
		if (!$menuItemGiven)
		{
			$segments[] = $view;
		}

		unset($query['view']);    

    switch ($view) 
    {
      case 'product':
      
				if (isset($query['product_id']) && isset($query['category_id']) && $query['category_id'])
				{
					$category_id = $query['category_id'];
          
    			if ($menuItemGiven && isset($menuItem->query['category_id']))
    			{
    				$mCatid = $menuItem->query['category_id'];
    			}
    			else
    			{
    				$mCatid = 0;
    			} 
                   
    			$categories = QZCategories::getInstance();
    			$category = $categories->get($category_id);
    			$path = array_reverse($category->getPath());

    			$array = array();

    			foreach ($path as $id)
    			{
    				if ((int) $id == (int) $mCatid)
    				{
    					break;
    				}

    				list($tmp, $id) = explode(':', $id, 2);

    				$array[] = $id;
    			}

    			$array = array_reverse($array);

    			if (!$this->_advanced && count($array))
    			{
    				$array[0] = (int) $category_id . ':' . $array[0];
    			}

    			$segments = array_merge($segments, $array);          
          
					// Make sure we have the id and the alias
					if (strpos($query['product_id'], ':') === false)
					{						
						$alias = QazapHelperRoute::getProductAlias($query['product_id']);
						$query['product_id'] = $query['product_id'] . ':' . $alias;
					}
          
          unset($query['category_id']);
				}
				else
				{
					// We should have these two set for this view.  If we don't, it is an error
					return $segments;
				}
        
				if(isset($query['parent_id']) && strpos($query['parent_id'], ':') === false)
				{
					$parentAlias = QazapHelperRoute::getProductAlias($query['parent_id']);
					$segments[] = $query['parent_id'] . ':' . $parentAlias;
          unset($query['parent_id']);
				}	        

				if ($this->_advanced)
				{
					list($tmp, $product_id) = explode(':', $query['product_id'], 2);
				}
				else
				{
					$product_id = $query['product_id'];
				}
        
        unset($query['product_id']);

				$segments[] = $product_id;
              
        break;
        
      case 'category':       
	
   			if (isset($query['vendor_id']))
  			{
  				if ($menuItemGiven && isset($menuItem->query['vendor_id']))
  				{
  					if ($query['vendor_id'] == $menuItem->query['vendor_id'])
  					{
  						unset($query['vendor_id']);
  					}
  				}
  				else
  				{
  					$vendor_id = $query['vendor_id'];
  					
  					if(is_array($vendor_id))
  					{
							$vendor_aliases = QazapHelperRoute::getVendorAlias($vendor_id, true);
							
							if(!empty($vendor_aliases))
							{
								$segments[] = implode(',', $vendor_aliases);
							}
							else
							{
								$segments[] = implode(',', $vendor_id);
							}
						}
  					else
  					{
	  					$vendor_alias = QazapHelperRoute::getVendorAlias($vendor_id);
	            
	  					if(!empty($vendor_alias))
	  					{
	  						$segments[] = $vendor_alias;
	  					}
	  					else
	  					{
	  						$segments[] = $vendor_id;
	  					}							
						}
  					
  					unset($query['vendor_id']);				
  				}
  			}	  			

				if (isset($query['category_id']))
				{
    			if ($menuItemGiven && isset($menuItem->query['category_id']))
    			{
    				$mCatid = $menuItem->query['category_id'];
    			}
    			else
    			{
    				$mCatid = 0;
    			}
                 
    			$categories = QZCategories::getInstance();
          $category_id = $query['category_id'];
          
          if($category_id > 0)
          {
	    			$category = $categories->get($category_id);

	    			if (!$category)
	    			{
	    				// We couldn't find the category we were given.  Bail.
	    				return $segments;
	    			}

	    			$path = array_reverse($category->getPath());

	    			$array = array();

	    			foreach ($path as $id)
	    			{
	    				if ((int) $id == (int) $mCatid)
	    				{
	    					break;
	    				}

	    				list($tmp, $id) = explode(':', $id, 2);

	    				$array[] = $id;
	    			}

	    			$array = array_reverse($array);

	    			if (!$this->_advanced && count($array))
	    			{
	    				$array[0] = (int) $category_id . ':' . $array[0];
	    			}

	    			$segments = array_merge($segments, $array); 						
					}
					else
					{
						$segments[] = $this->getSEFLang('category_root');
					} 
				}
				else
				{
					// We should have id set for this view.  If we don't, it is an error
					return $segments;
				}        	      
    		
    		unset($query['category_id']);
    		
   			if (isset($query['brand_id']))
  			{
					$brand_id = $query['brand_id'];
					
					if(is_array($brand_id))
					{
						$brand_aliases = QazapHelperRoute::getBrandAlias($brand_id, true);
						
						if(!empty($brand_aliases))
						{
							$segments[] = implode(',', $brand_aliases);
						}
					}
					else
					{
						$brand_alias = QazapHelperRoute::getBrandAlias($brand_id);
		          
						if(!empty($brand_alias))
						{
							$segments[] = (string) $brand_alias;
						}
					}
					
					unset($query['brand_id']);
  			}	  			 

      	if(isset($query['min_price']) && isset($query['max_price']))
      	{
					$min_price = (float) $query['min_price'];
					$max_price = (float) $query['max_price'];
					$segments[] = $this->getSEFLang('price') . $this->suffix . $min_price . '-' . $max_price;
					
					unset($query['min_price']);
					unset($query['max_price']);
				}
				elseif(isset($query['min_price']))
				{
					$min_price = (float) $query['min_price'];
					$segments[] = $this->getSEFLang('price') . $this->suffix . $min_price . '-' . $this->getSEFLang('na');
					unset($query['min_price']);
				}
				elseif(isset($query['max_price'])) 
				{
					$max_price = (float) $query['max_price'];
					$segments[] = $this->getSEFLang('price') . $this->suffix . $this->getSEFLang('na') . '-' . $max_price;	
					unset($query['max_price']);				
				}
				
				if(isset($query['attribute'])) 
				{
					$attributes = (array) $query['attribute'];
					$attribute_names = QazapHelperRoute::getAttributeAliases($attributes);
					
					if(!empty($attribute_names))
					{
						$segments[] = $this->getSEFLang('attribute') . $this->suffix . implode(',', $attribute_names);	
					}

					unset($query['attribute']);				
				}	
				
				if(isset($query['only_in_stock'])) 
				{
					$only_in_stock = (int) $query['only_in_stock'];
					$segments[] = $this->getSEFLang('only_in_stock');
					unset($query['only_in_stock']);				
				}
				
				if(isset($query['orderby'])) 
				{
					$orderby = (string) $query['orderby'];
					$segments[] = $this->getSEFLang('orderby') . $this->suffix . str_replace('_', '-', $orderby);
					unset($query['orderby']);				
				}		
				
				if(isset($query['order_dir'])) 
				{
					$order_dir = (string) $query['order_dir'];
					
					if(strtolower($order_dir) == 'desc')
					{
						$segments[] = $this->getSEFLang('order_dir_desc');
					}
					else
					{
						$segments[] = $this->getSEFLang('order_dir_asc');
					}

					unset($query['order_dir']);				
				}																	
    		
        break;
              
      case 'cart':
      
        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('cart');
				}        
        
        if(isset($query['layout']))
        {
					$layoutFile = QZHelper::getLayoutFile($query['layout'], 'cart');
					
					if(!empty($layoutFile))
					{
						 $segments[] = $this->getSEFLang($query['layout']);
						 unset($query['layout']);
					}
				}
				
        break;
        
      case 'profile':

        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('profile');
				}        
        
        if(isset($query['layout']))
        {
					$layoutFile = QZHelper::getLayoutFile($query['layout'], 'profile');
					
					if(!empty($layoutFile))
					{
						$layoutName = $this->getSEFLang($query['layout']);
						unset($query['layout']);
						
						if(isset($query['type']))
						{
							$layoutName .= ':' . $this->getSEFLang($query['type']); 
							unset($query['type']);
						}
						
						if(isset($query['id']))
						{
							$layoutName .= ':' . $this->getSEFLang($query['id']); 
							unset($query['id']);
						}
						
						$segments[] = $layoutName;
					}
				}
				
        break;   
        
      case 'seller':

        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('seller');
				}        
        
        if(isset($query['layout']))
        {
					$layoutFile = QZHelper::getLayoutFile($query['layout'], 'seller');
					
					if(!empty($layoutFile))
					{
						 $segments[] = $this->getSEFLang($query['layout']);
						 unset($query['layout']);
					}
				}
				
        break;
        
      case 'sellerform':

        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('sellerform');
				}        
        
        if(isset($query['layout']))
        {
					$layoutFile = QZHelper::getLayoutFile($query['layout'], 'sellerform');
					
					if(!empty($layoutFile))
					{
						 unset($query['layout']);
					}
				}
				
        break;                      
        
      case 'brand' :
      	
      	// We need to have a brand id otherwise it is an invalid query
   			if (!isset($query['brand_id']))
  			{
  				return $segments;	
  			}  			

				$brand_alias = QazapHelperRoute::getBrandAlias($query['brand_id']);
          
				if(!empty($brand_alias))
				{
					$segments[] = (string) $brand_alias;
					unset($query['brand_id']);
				}			
							      
      	break;
      	
      case 'compare' :

        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('compare');
				} 
				      	
      	// We need to have a brand id otherwise it is an invalid query
   			if (!isset($query['p_id']))
  			{
  				return $segments;	
  			} 
        
        $ids = (array) $query['p_id'];
        
				if(!empty($ids))
				{
					$segments[] = (string) implode(',', $ids);
					unset($query['p_id']);
				}			
							      
      	break;      	
      	
      case 'shop' :
      	
      	// We need to have a brand id otherwise it is an invalid query
   			if (!isset($query['vendor_id']))
  			{
  				return $segments;	
  			}  			

				$vendor_alias = QazapHelperRoute::getVendorAlias($query['vendor_id']);
          
				if(!empty($vendor_alias))
				{
					$segments[] = (string) $vendor_alias;
					unset($query['vendor_id']);
				}			
							      
      	break; 
      
      case 'orderdetails' :
      
        if($hasMenu)
        {
					unset($query['view']);
				}
				else
				{
					$segments[] = $this->getSEFLang('orderdetails');
				}
				
				if(!isset($query['ordergroup_id']))
				{
					return $segments;
				}
				
				$orderdetails_alias = QazapHelperRoute::getOrderDetailsAlias($query['ordergroup_id']);
          
				if(!empty($orderdetails_alias))
				{
					$segments[] = (string) $orderdetails_alias;
					unset($query['ordergroup_id']);
				}
								
      	break;     	
    }

		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}
		
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$menu = $this->_item;
		$menuView = isset($menu->query['view']) ? $menu->query['view'] : '';
		$vars = array();

		// Count route segments	
		$count = count($segments);	
		
		/*
		 * Standard routing. If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the product or category.
		 */
		if (!isset($this->_item))
		{
			$vars['view'] = $segments[0];
			
			switch ($vars['view'])
			{
				case 'product' :
				
					$productHelper = QZProducts::getInstance();
					$product = $productHelper->getByAlias($segments[$count - 1]);
					
					if(!empty($product))
					{
						$vars['product_id'] = $product->product_id;
					}	
								
					break;
					
				case 'category' :
				
					$categories = QZCategories::getInstance();
					$category = $categories->get(0);
					
					if (!empty($category))
					{
						$categories = $category->getChildren();
						$vars['category_id'] = 0;
						$found = 0;

						foreach ($segments as $key => $segment)
						{
							foreach ($categories as $category)
							{
								if ($category->alias == $segment)
								{
									$vars['category_id'] = $category->category_id;
									$vars['view'] = 'category';
									$categories = $category->getChildren();
									break;
								}
							}	
						}
					}	
							
					break;
				
			}

			return $vars;
		}		

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}
	
		$layoutView = null;		
		$matchFound = false;
		$specialLayoutViews = array('cart', 'profile', 'seller');
		
		if($count == 1 && in_array($menuView, $specialLayoutViews))
		{
			$layoutView = $menuView;
			$layout = $segments[0];
		}

		if($count == 2)
		{
			if($this->isThisKey($segments[0], 'cart'))
			{
				$layoutView = 'cart';
				$layout = $segments[1];
			}
			elseif($this->isThisKey($segments[0], 'profile'))
			{
				$layoutView = 'profile';			
				$layout = $segments[1];
			}
			elseif($this->isThisKey($segments[0], 'seller'))
			{
				$layoutView = 'seller';
				$layout = $segments[1];
			}
			elseif($this->isThisKey($segments[0], 'compare') && (preg_match('/^[1-9,][0-9,]*$/', $segments[1])))
			{
				$vars['view'] = 'compare';
				$vars['p_id'] = is_numeric($segments[1]) ? array($segments[1]) : explode(',', $segments[1]);
				return $vars;
			}
			elseif($this->isThisKey($segments[0], 'orderdetails'))
			{
				$vars['view'] = 'orderdetails';
				$ordergroup_number = preg_replace('/:/', '-', $segments[1], 1);
				$vars['ordergroup_id'] = QazapHelperRoute::getOrdergroupIDByAlias($ordergroup_number);
				return $vars;
			}									
		}
		
		if(!empty($layoutView))
		{				
			$segment = str_replace('-', ':', $layout);
			
			if(strpos($segment, ':') !== false)
			{
				$segment = explode(':', $segment);
				
				if($this->isThisKey($segment[0], 'edit'))
				{
					$vars['view'] = 'profile';
					$vars['layout'] = 'edit';
					$vars['type'] = $segment[1];
					
					if(isset($segment[2]))
					{
						$vars['id'] = $segment[2];
					}
					
					return $vars;
				}				
			}	
							
			$layout = $this->getLayoutByLang($layout, $layoutView);
			
			if(!empty($layout))
			{
				$vars['view'] = $layoutView;
				$vars['layout'] = $layout;
				return $vars;
			}						
		}
		
		if($count == 1)
		{
			// Check for cart page
			if($this->isThisKey($segments[0], 'cart'))
			{
				$vars['view'] = 'cart';
				return $vars;
			}
			elseif($this->isThisKey($segments[0], 'profile'))
			{
				$vars['view'] = 'profile';
				return $vars;
			}			
			elseif($this->isThisKey($segments[0], 'seller'))
			{
				$vars['view'] = 'seller';
				return $vars;				
			}
			elseif($this->isThisKey($segments[0], 'sellerform'))
			{
				$vars['view'] = 'sellerform';
				$vars['layout'] = 'edit';
				return $vars;				
			}			
			elseif($this->isThisKey($segments[0], 'compare'))
			{
				$vars['view'] = 'compare';
				return $vars;				
			}		
			elseif(preg_match('/^[1-9,][0-9,]*$/', $segments[0]))
			{
				$vars['view'] = 'compare';
				$vars['p_id'] = is_numeric($segments[0]) ? array($segments[0]) : explode(',', $segments[0]);
				return $vars;				
			}
			
			$segment = str_replace('-', ':', $segments[0]);
			
			if(strpos($segment, ':') !== false)
			{
				$segment = explode(':', $segment);
				
				if($this->isThisKey($segment[0], 'edit'))
				{
					$vars['view'] = 'profile';
					$vars['layout'] = 'edit';
					$vars['type'] = $segment[1];
					
					if(isset($segment[2]))
					{
						$vars['id'] = $segment[2];
					}
					
					return $vars;
				}				
			}			
			
			$segment = preg_replace('/:/', '-', $segments[0], 1);	
			// Check for vendor page
			$vendor_id = QazapHelperRoute::getVendorID($segment);
			
			if(!empty($vendor_id))
			{
				$vars['view'] = 'shop';
				$vars['vendor_id'] = (int) $vendor_id;
				return $vars;			
			}
						
			// Check for brand page
			$brand_id = QazapHelperRoute::getBrandByAlias($segment);
			
			if(!empty($brand_id))
			{
				$vars['view'] = 'brand';
				$vars['brand_id'] = (int) $brand_id;
				return $vars;			
			}			
		}
		
		if($matchFound == true)
		{
			return $vars;
		}
		
		$lastSplit = $this->getLastSplitted($segments);
		
		if($this->isThisKey($lastSplit[0], 'order_dir_desc'))
		{
			$vars['view'] = 'category';
			$vars['order_dir'] = 'desc';
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);				
		}
		elseif($this->isThisKey($lastSplit[0], 'order_dir_asc'))
		{
			$vars['view'] = 'category';
			$vars['order_dir'] = 'asc';
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);			
		}
		
		if($this->isThisKey($lastSplit[0], 'orderby'))
		{
			$vars['view'] = 'category';
			$vars['orderby'] = str_replace('-', '_', $lastSplit[1]);
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);				
		}		
		
		if($this->isThisKey($lastSplit[0], 'only_in_stock'))
		{
			$vars['view'] = 'category';
			$vars['only_in_stock'] = $lastSplit[1];
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);	
		}	
		
		if($this->isThisKey($lastSplit[0], 'attribute'))
		{
			$vars['view'] = 'category';
			
			if(strpos($lastSplit[1], ','))
			{
				$lastSplit[1] = explode(',', $lastSplit[1]);
			}
			
			$values = $this->parseArrayValues($lastSplit[1], true);
			
			foreach($values as $key => $value)
			{
				$vars['attribute'][$key] = $value;
			}

			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);	
		}

		if($this->isThisKey($lastSplit[0], 'price') && strpos($lastSplit[1], '-') !== false)
		{
			$vars['view'] = 'category';			
			$splitted = explode('-', $lastSplit[1], 2);
			
			if($splitted[0] != 'na')
			{
				$vars['min_price'] = (float) $splitted[0];
			}
			
			if($splitted[1] != 'na')
			{
				$vars['max_price'] = (float) $splitted[1];
			}

			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);	
		}
		
		$count = count($segments);
		$lastSegment = $segments[$count - 1];
		$lastSegment = str_replace(':', '-', $lastSegment);
		
		if(strpos($lastSegment, ',') !== false)
		{
			$vars['view'] = 'category';
			$splitted = explode(',', $lastSegment);
			
			// Check if brands/manufactuers
			$brands = QazapHelperRoute::getBrandByAlias($splitted, true);
			if(!empty($brands))
			{
				foreach($brands as $key => $brand)
				{
					$vars['brand_id'][$key] = (int) $brand;
				}
			}
						
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);			
			$count = count($segments);
			$lastSegment = $segments[$count - 1];			
		}

		// Check if brand/manufactuer
		$brand = QazapHelperRoute::getBrandByAlias($lastSegment);			
		if(!empty($brand))
		{
			$vars['brand_id'] = (int) $brand;
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);			
			$count = count($segments);
			$lastSegment = $segments[$count - 1];				
		}		
		
		// Check if vendor/seller
		$vendor_id = QazapHelperRoute::getVendorID(str_replace(':', '-', $segments[0]));
		
		if(!empty($vendor_id))
		{
			$vars['vendor_id'] = (int) $vendor_id;
		}			
		
		// Check if all category view
		if($this->isThisKey($lastSplit[0], 'category_root'))
		{
			$vars['view'] = 'category';
			$vars['category_id'] = 0;
			array_pop($segments);
			if(empty($segments))
			{
				return $vars;
			}			
			$lastSplit = $this->getLastSplitted($segments);			
			$count = count($segments);
			$lastSegment = $segments[$count - 1];				
		}
		
		// Check if product
		//$productHelper = QZProducts::getInstance();
		//$product = $productHelper->getByAlias($segment, $vars['category_id']);		
		
		// Check if category
		// We get the category id from the menu item and search from there
		$id = isset($this->_item->query['category_id']) ? $this->_item->query['category_id'] : 0;
		$categories = QZCategories::getInstance();
		$category = $categories->get($id);
		
		if (!empty($category))
		{
			$categories = $category->getChildren();
			$vars['category_id'] = $id;
			$found = 0;

			foreach ($segments as $key => $segment)
			{
				$segment = str_replace(':', '-', $segment);

				foreach ($categories as $category)
				{
					if ($category->alias == $segment)
					{
						$vars['category_id'] = $category->category_id;
						$vars['view'] = 'category';
						$categories = $category->getChildren();
						$found = 1;
						break;
					}
				}

				if ($found == 0)
				{
					$productHelper = QZProducts::getInstance();
					$product = $productHelper->getByAlias($segment, $vars['category_id']);
					
					if(!empty($product))
					{
						$vars['view'] = 'product';
						$vars['product_id'] = $product->product_id;
					}
				}
				
				$found = 0;
			}
		}

		return $vars;
	}
}

/**
 * Qazap router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function QazapBuildRoute(&$query)
{
	$router = QazapRouter::getInstance();

	return $router->build($query);
}

function QazapParseRoute($segments)
{
	$router = QazapRouter::getInstance();

	return $router->parse($segments);
}
