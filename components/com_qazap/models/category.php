<?php
/**
 * category.php
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

jimport('joomla.application.component.modellist');
jimport('joomla.filesystem.file');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelCategory extends JModelList 
{
	
	/**
	* Category items data
	*
	* @var array
	*/
	protected $_item = null;

	protected $_products = null;
	
	protected $_productcount = null;

	protected $_siblings = null;

	protected $_children = null;

	protected $_parent = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $context = null;

	/**
	* The category that applies.
	*
	* @access	protected
	* @var		object
	*/
	protected $_category = null;

	/**
	 * The list of other newfeed categories.
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_categories = null;
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JModelList
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if($this->context === null)
		{
	  		$app = JFactory::getApplication();
	  		$category_id = $app->input->getInt('category_id', 0);
	  		$this->context = $this->getContext($category_id);			
		}
		parent::__construct($config);
	}
  
	public function getContext($category_id)
	{
		return 'com_qazap.category.' . $category_id . '.products';
	}
	
	public function clearUserStates($category_id = null, $filter = null)
	{
		$category_id = is_numeric($category_id) ? $category_id : $this->getLastVisited();

		if($category_id >= 0)
		{
			$context = $this->getContext($category_id);
			$params = QZApp::getConfig();
			$app = JFactory::getApplication();
			
			if(empty($filter))
			{
				// Clear all states
				$app->setUserState($context . '.filter.vendors', null);
				$app->setUserState($context . '.filter.manufacturers', null);
				$app->setUserState($context . '.filter.attributes', null);
				$app->setUserState($context . '.filter.min_price', null);
				$app->setUserState($context . '.filter.max_price', null);
				$app->setUserState($context . '.filter.only_in_stock', null);
				$app->setUserState($context . '.filter.orderby', null);
				$app->setUserState($context . '.filter.order_dir', null);
				$app->setUserState($context . '.list.limitstart', 0);
				$app->setUserState($context . '.list.limit', $params->get('product_list_limit'));					
			}
			elseif($filter == 'limitstart')
			{
				// Clear limit start state
				$app->setUserState($context . '.list.limitstart', 0);
			}
			elseif($filter == 'limit')
			{
				// Clear limit state
				$app->setUserState($context . '.list.limit', $params->get('product_list_limit'));
			}
			else
			{
				// Clear the desired filter state
				$app->setUserState($context . '.filter.' . $filter, null);
			}		
		}
	}
  
	public function getLastVisited()
	{
		$app = JFactory::getApplication();
		$last_visited = $app->getUserState('com_qazap.category.lastvisted.id', 0, 'int'); 

		return $last_visited;
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
		// Initialise variables.
		$app = JFactory::getApplication();
		
		// Get the parent id if defined.
		$category_id = $app->input->getInt('category_id', 0);
		$this->setState('category.id', $category_id);	
		
		$last_visited = $app->getUserState('com_qazap.category.lastvisted.id', 0, 'int');
		$app->setUserState('com_qazap.category.lastvisted.id', $category_id);
		
		if(($last_visited >= 0) && ($last_visited != $category_id))
		{			
			$this->clearUserStates($last_visited);
		}
		
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('filter.published',	1);
		$this->setState('filter.access', true);

		// Optional filter text
		$filter_search = $app->input->getString('filter_search', null);
		$this->setState('filter.search', $filter_search); 
		
		// Optional filter search phrase
		$searchphrase = $app->input->getString('searchphrase', null);
		$this->setState('filter.searchphrase', $searchphrase);		
		
		// Vendor Filter
		$vendors = $app->input->get('vendor_id', null, 'array');
		$this->setState('filter.vendor_id', $vendors);

		// Manufacturer Filter
		$manufacturers = $app->getUserStateFromRequest($this->context . '.filter.manufacturers', 'brand_id', null, 'array');
		$this->setState('filter.manufacturers', $app->input->get('brand_id', $manufacturers, 'array'));
		
		// Attribute Filter
		$attributes = $app->getUserStateFromRequest($this->context . '.filter.attributes', 'attribute', null, 'array');
		$this->setState('filter.attributes', $app->input->get('attribute', $attributes, 'array'));

		// Minimum Price Filter
		$min_price = $app->getUserStateFromRequest($this->context . '.filter.min_price', 'min_price', null, 'float');
		$this->setState('filter.min_price', $app->input->get('min_price', $min_price, 'float'));	
		
		// Maximum Price Filter
		$max_price = $app->getUserStateFromRequest($this->context . '.filter.max_price', 'max_price', null, 'float');
		$this->setState('filter.max_price', $app->input->get('max_price', $max_price, 'float'));
		
		// Filter Only In Stock
		$only_in_stock = $app->getUserStateFromRequest($this->context . '.filter.only_in_stock', 'only_in_stock', 0, 'uint');
		$this->setState('filter.only_in_stock', $app->input->getInt('only_in_stock', $only_in_stock));				

		// Order By
		$input_orderby = $app->input->getString('orderby', null);
		$orderby = $app->getUserStateFromRequest($this->context . '.filter.orderby', 'orderby', null, 'string');
		$order_options = QZProducts::getOrderingFields(false);

		if($input_orderby && in_array(strtolower($input_orderby), $order_options))
		{
			$orderby = strtolower($input_orderby);
		}

		$this->setState('filter.ordering', $orderby);		
		
		// Direction of ordering
		$input_order_dir = $app->input->getString('order_dir', null);
		$order_dir = $app->getUserStateFromRequest($this->context . '.filter.order_dir', 'order_dir', null, 'string');
		
		if($input_order_dir && in_array(strtolower($input_order_dir), array('asc', 'desc', '')))
		{
			$order_dir = strtolower($input_order_dir);
		}

		$this->setState('filter.direction', $order_dir);
		
		if(!$app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
		{
			$app->setUserState($this->context . '.list.limitstart', 0);
			$app->setUserState($this->context . '.list.limit', $params->get('product_list_limit'));
		}

		// Limit start
		$limitstart = $app->getUserStateFromRequest($this->context . '.list.limitstart', 'limitstart', 0, 'uint');
		$this->setState('list.limitstart', $app->input->get('limitstart', $limitstart, 'uint'));
		
		// Set page limit
		$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'listlimit', $params->get('product_list_limit'), 'uint');		
		$this->setState('list.limit', $limit);
		
		// Set layout
		$this->setState('layout', $app->input->getString('layout'));		 
		// List state information.

		parent::populateState($ordering, $direction);
	}
	
	
	public function setStateFromUserState($category_id)
	{
		$app = JFactory::getApplication();
		$context = $this->getContext($category_id);

		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('filter.manufacturers', $app->getUserState($context . '.filter.manufacturers', null));
		$this->setState('filter.attributes', $app->getUserState($context . '.filter.attributes', null));
		$this->setState('filter.min_price', $app->getUserState($context . '.filter.min_price', null));
		$this->setState('filter.max_price', $app->getUserState($context . '.filter.max_price', null));
		$this->setState('filter.only_in_stock', $app->getUserState($context . '.filter.only_in_stock', null));
		$this->setState('filter.orderby', $app->getUserState($context . '.filter.orderby', null));
		$this->setState('filter.order_dir', $app->getUserState($context . '.filter.order_dir', null));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.searchphrase');
		$id .= ':' . serialize($this->getState('filter.vendor_id'));
		$id .= ':' . serialize($this->getState('filter.manufacturers'));
		$id .= ':' . serialize($this->getState('filter.attributes'));
		$id .= ':' . $this->getState('filter.min_price');
		$id .= ':' . $this->getState('filter.max_price');
		$id .= ':' . $this->getState('filter.only_in_stock');

		return parent::getStoreId($id);
	}
	
	/**
	 * Method to get an array of products.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getItems()
	{
		if ($this->_products === null && ($category = $this->getCategory()))
		{
			$params = $this->getState('params');
			
			$options = array();
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 		= true;
			$options['access'] 					= true;
			$options['category_params'] = $category->getParams();
			$options['limitstart'] 			= $this->getState('list.limitstart');
			$options['limit'] 					= $this->getState('list.limit');

			$filters = array();
			$filters['search']					= $this->getState('filter.search');	
			$filters['searchphrase']		= $this->getState('filter.searchphrase');
			$filters['vendors']					= $this->getState('filter.vendor_id');	
			$filters['ordering']				= $this->getState('filter.ordering');	
			$filters['direction']				= $this->getState('filter.direction');	
			$filters['manufacturers']		= $this->getState('filter.manufacturers');	
			$filters['attributes']			= $this->getState('filter.attributes');	
			$filters['min_price']				= $this->getState('filter.min_price');	
			$filters['max_price']				= $this->getState('filter.max_price');
			$filters['only_in_stock']		= $this->getState('filter.only_in_stock') ? true : null;	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);
				$this->_products = $helper->getList($this->getState('category.id', 0));				
				$this->_productcount = $helper->getCount();						
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_products = false;
					$this->_productcount = 0;
				}
			}			
		}
		
		return $this->_products;
	}	

	
	
	/**
	 * Method to get category data for the current category
	 *
	 * @param   integer  An optional ID
	 *
	 * @return  object
	 * @since   1.5
	 */
	public function getCategory()
	{
		if (!is_object($this->_item))
		{
			if (isset($this->state->params))
			{
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_subcategory_num_products', 1) || !$params->get('show_empty_subcategories', 0);
			}
			else 
			{
				$options['countItems'] = 0;
			}
			
			try 
			{
				$categories = QZCategories::getInstance($options);			
				$this->_item = $categories->get($this->getState('category.id', 0));
				
				// Compute selected asset permissions.
				if (is_object($this->_item))
				{
					// TODO: Why aren't we lazy loading the children and siblings?
					$this->_children = $this->_item->getChildren();
					$this->_parent = false;

					if ($this->_item->getParent())
					{
						$this->_parent = $this->_item->getParent();
					}

					$category_params = $this->_item->getParams();
					$params = QZApp::getConfig(true, $category_params);
					$this->setState('params', $params);				
				}
				else 
				{
					$this->_children = false;
					$this->_parent = false;
				}				
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item = false;
					$this->_children = false;
					$this->_parent = false;
				}
			}
			
			if($this->_children)
			{
				$this->setChildrenProductOrdering();
			}			
		}
		
		return $this->_item;
	}

	/**
	 * Get the parent category.
	 *
	 * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 * @since   1.0.0
	 */
	public function getParent()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 * @since   1.0.0
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}
		
		$this->_leftsibling = $this->_item->getSibling(false);
		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 * @since   1.0.0
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}
		
		$this->_rightsibling = $this->_item->getSibling();				
		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 * @since   1.0.0
	 */
	function &getChildren()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		// Order subcategories
		if (count($this->_children))
		{
			$params = $this->getState()->get('params');
			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha')
			{
				jimport('joomla.utilities.arrayhelper');
				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
			}
		}

		return $this->_children;
	}

	/**
	 * Increment the hit counter for the category.
	 *
	 * @param   int  $pk  Optional primary key of the category to increment.
	 *
	 * @return  boolean True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

			$table = JTable::getInstance('Category', 'QazapTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
	
	protected function setChildrenProductOrdering()
	{
		$params = $this->getState('params');
		
		if($params->get('categories_as_filter', 1))
		{
			$app = JFactory::getApplication();			
			foreach($this->_children as $childCat)
			{
				$childContext = $this->getContext($childCat->category_id);
				$app->setUserState($childContext . '.filter.orderby', $this->getState('filter.ordering'));
				$app->setUserState($childContext . '.filter.order_dir', $this->getState('filter.direction'));
			}
		}		
	}
	
	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getStart()
	{
		return $this->getState('list.limitstart');
	}	
	
	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getTotal()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $this->_productcount;

		return $this->cache[$store];
	}	
	
	
	public function getURL($category_id, $variableName = '', $value = null, $skip = array())
	{
				//echo $this->getState('category_id');exit;
		$list = array();
		$list['orderby'] 			= $this->getState('filter.ordering');
		$list['order_dir'] 		= $this->getState('filter.direction');
		$list['limitstart'] 	= $this->getState('list.limitstart');
		$params 							= $this->getState('params');
		$list['listlimit'] 		= ($this->getState('list.listlimit') != $params->get('product_list_limit')) ? $this->getState('list.listlimit') : '';

		$filter = array();
		$filter['filter_search'] 	= $this->getState('filter.search');
		$filter['searchphrase'] 	= $this->getState('filter.searchphrase');
		$filter['vendor_id'] 		= $this->getState('filter.vendor_id');
		$filter['brand_id'] 		= $this->getState('filter.manufacturers');
		$filter['attribute'] 		= $this->getState('filter.attributes');
		$filter['min_price']		= $this->getState('filter.min_price');
		$filter['max_price'] 		= $this->getState('filter.max_price');
		
		if(array_key_exists($variableName, $list) && $value)
		{
			$list[$variableName] = trim($value);
			
			if($variableName != 'limitstart' || $variableName != 'limit')
			{
				$list['limitstart'] = $list['limitstart'] ? 0 : $list['limitstart'];
			}
		}		
		elseif(array_key_exists($variableName, $filter) && $value)
		{
			$filter[$variableName] = trim($value);
			if($variableName != 'limitstart' || $variableName != 'limit')
			{
				$list['limitstart'] = $list['limitstart'] ? 0 : $list['limitstart'];
			}			
		}
		
		if(count($skip))
		{
			foreach($skip as $var)
			{
				if(array_key_exists($var, $list)) 
				{
					$list[$var] = '';
				}
				elseif(array_key_exists($var, $filter)) 
				{
					unset($filter[$var]);
				}
			}
			
		}	

		return QazapHelperRoute::getCategoryRoute($category_id, $list['orderby'], $list['order_dir'], $list['limitstart'], $list['listlimit'], $filter);
	}
  
  public function getFilterState()
  {
    $filters = array();
    $filters[] = $this->getState('filter.search');
    $filters[] = $this->getState('filter.searchphrase');
    $filters[] = $this->getState('filter.vendor_id');
    $filters[] = $this->getState('filter.manufacturers');
    $filters[] = $this->getState('filter.attributes');
    $filters[] = $this->getState('filter.min_price');
    $filters[] = $this->getState('filter.max_price');
    $filters[] = $this->getState('filter.only_in_stock');
    
    $filters = array_filter($filters);
    
    if(!empty($filters))
    {
      return true;
    }
    
    return false;
  }

}
