<?php
/**
 * categories.php
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

/**
 * This models supports retrieving lists of article categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapModelCategories extends JModelList
{
	/**
	* Model context string.
	*
	* @var		string
	*/
	public $_context = 'com_qazap.categories';
	
	protected $_parent = null;
	
	protected $_items = null;
	
	protected $_featured_products = null;
	
	protected $_latest_products = null;
	
	protected $_topselling_products = null;
	
	protected $_random_products = null;
	
	protected $_product_lists = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Get the parent id if defined.
		$parentId = $app->input->getInt('category_id');
		$this->setState('filter.parentId', $parentId);

		// Vendor Filter
		$vendors = $app->input->get('vendor_id', null, 'array');
		$this->setState('filter.vendors', $vendors);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	* Method to get a store id based on model configuration state.
	*
	* This is necessary because the model is used by the component and
	* different modules that might need different sets of data or different
	* ordering requirements.
	*
	* @param   string  $id	A prefix for the store id.
	*
	* @return  string  A store id.
	*/
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.parentId');
		$id	.= ':'.$this->getState('filter.vendors');

		return parent::getStoreId($id);
	}

	/**
	* Redefine the function an add some properties to make the styling more easy
	*
	* @param   bool	$recursive	True if you want to return children recursively.
	*
	* @return  mixed  An array of data items on success, false on failure.
	* @since   1.0.0
	*/
	public function getItems($recursive = false)
	{
		if (empty($this->_items))
		{
			try
			{
				$params = $this->getState('params');
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_products_cat', 1) || !$params->get('show_empty_categories_cat', 0);
				$options['countSubcat'] = $params->get('categories_as_filter', 1);
				$categories = QZCategories::getInstance($options);
				$this->_parent = $categories->get($this->getState('filter.parentId', 'root'));
				if (is_object($this->_parent))
				{
					$this->_items = $this->_parent->getChildren($recursive);
          $categoryModel = QZApp::getModel('Category', array('ignore_request' => true), false);
          $categoryModel->clearUserStates();
          $categoryModel->clearUserStates((int) $this->_parent->category_id);
				}
				else {
					$this->_items = false;
				}				
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

		}

		return $this->_items;
	}
	
	

	public function getParent()
	{
		if (!is_object($this->_parent))
		{
			$this->getItems();
		}

		return $this->_parent;
	}
	
	
	
	public function getProductLists()
	{
		if($this->_product_lists === null)
		{
			$lists = array();
			$params = $this->getState('params');
			
			if($params->get('show_latest_products', 1))
			{
				$products = $this->getLatestProducts();
				
				if($products === false)
				{
					$this->setError($this->getError());
					return false;
				}
				
				$lists['latest'] = new stdClass;
				$lists['latest']->title = 'COM_QAZAP_LATEST_PRODUCTS';
				$lists['latest']->products = $products;	
			}			
			
			if($params->get('show_featured_products', 1))
			{
				$products = $this->getFeaturedProducts();
				
				if($products === false)
				{
					$this->setError($this->getError());
					return false;
				}
				
				$lists['featured'] = new stdClass;
				$lists['featured']->title = 'COM_QAZAP_FEATURED_PRODUCTS';
				$lists['featured']->products = $products;	
			}
			
			if($params->get('show_random_products', 1))
			{
				$products = $this->getRandomProducts();

				if($products === false)
				{
					$this->setError($this->getError());
					return false;
				}
				
				$lists['random'] = new stdClass;
				$lists['random']->title = 'COM_QAZAP_RANDOM_PRODUCTS';
				$lists['random']->products = $products;	
			}	
			
			if($params->get('show_topselling_products', 1))
			{
				$products = $this->getTopsellingProducts();

				if($products === false)
				{
					$this->setError($this->getError());
					return false;
				}
				
				$lists['topselling'] = new stdClass;
				$lists['topselling']->title = 'COM_QAZAP_TOP_SELLING_PRODUCTS';
				$lists['topselling']->products = $products;	
			}						
			
			$this->_product_lists = $lists;			
		}
		
		return $this->_product_lists;
	}	
	
	public function getFeaturedProducts()
	{
		if ($this->_featured_products === null)
		{
			$params = $this->getState('params');
			
			$options = array();
			$options['list_type']				= 'featured';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 			= false;
			$options['access'] 					= true;
			$options['limitstart'] 				= 0;
			$options['limit'] 					= $params->get('featured_products_limit', 4);

			$filters = array();
			$filters['vendors']					= $this->getState('filter.vendors');	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_featured_products = $helper->getList($this->getState('filter.parentId', 0));		
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
					$this->_featured_products = false;
				}
			}			
		}
		
		return $this->_featured_products;		
	}

	public function getLatestProducts()
	{
		if ($this->_latest_products === null)
		{
			$params = $this->getState('params');
			
			$options = array();
			$options['list_type']				= 'latest';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 			= false;
			$options['access'] 					= true;
			$options['limitstart'] 				= 0;
			$options['limit'] 					= $params->get('latest_products_limit', 4);

			$filters = array();
			$filters['vendors']					= $this->getState('filter.vendors');	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_latest_products = $helper->getList($this->getState('filter.parentId', 0));		
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
					$this->_latest_products = false;
				}
			}			
		}
		
		return $this->_latest_products;		
	}

	public function getTopsellingProducts()
	{
		if ($this->_topselling_products === null)
		{
			$params = $this->getState('params');
			
			$options = array();
			$options['list_type']				= 'topselling';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 			= false;
			$options['access'] 					= true;
			$options['limitstart'] 				= 0;
			$options['limit'] 					= $params->get('topselling_products_limit', 4);

			$filters = array();
			$filters['vendors']					= $this->getState('filter.vendors');	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_topselling_products = $helper->getList($this->getState('filter.parentId', 0));		
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
					$this->_topselling_products = false;
				}
			}			
		}
		
		return $this->_topselling_products;		
	}	

	public function getRandomProducts()
	{
		if ($this->_random_products === null)
		{
			$params = $this->getState('params');
			
			$options = array();
			$options['list_type']							= 'random';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 					= false;
			$options['access'] 								= true;
			$options['limitstart'] 						= 0;
			$options['limit'] 								= $params->get('random_products_limit', 4);

			$filters = array();
			$filters['vendors']								= $this->getState('filter.vendors');	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_random_products = $helper->getList($this->getState('filter.parentId', 0));		
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
					$this->_random_products = false;
				}
			}			
		}
		
		return $this->_random_products;		
	}		
	

}
