<?php
/**
 * shop.php
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
jimport('joomla.event.dispatcher');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelShop extends JModelItem 
{
	
	protected $item = null;
	
	protected $_featured_products = null;
	
	protected $_latest_products = null;
	
	protected $_topselling_products = null;
	
	protected $_random_products = null;
	
	protected $_product_lists = null;
	
	protected $_columns = null;	
	
	protected $_storeInfo = array();

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('vendor_id');
		$this->setState('vendor.id', $pk);				

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_qazap')) && (!$user->authorise('core.edit', 'com_qazap')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}
	
	/**
	* Method to get article data.
	*
	* @param   integer    The id of the article.
	*
	* @return  mixed  Menu item data object on success, false on failure.
	*/
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('vendor.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if(!$columns = $this->getColumns($alias = 'a'))
		{
			$this->setError($this->getError());
			return false;
		}

		if (!isset($this->_item[$pk]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
					->select($columns)
					->from('`#__qazap_vendor` AS a');
					
			$query->select('b.name AS editor')
					->join('LEFT', '#__users As b ON b.id = a.checked_out');
		
			$query->select('c.name AS vendor_admin')
					->join('INNER', '#__users As c ON c.id = a.vendor_admin');
			
			if(in_array('a.country', $columns))
			{
				$query->select('d.country_name')
							->join('LEFT', '#__qazap_countries AS d ON d.id = a.country');				
			}
			
			if(in_array('a.states', $columns))
			{
				$query->select('s.state_name')
							->join('LEFT', '#__qazap_states AS s ON s.id = a.states');				
			}
			
			$query->select('(SELECT COUNT(e.product_id) FROM #__qazap_products AS e WHERE e.vendor = a.id AND e.state = 1 AND e.block = 0) AS product_count');
		
		$subQuery = ' (SELECT AVG(g.rating) AS average_rating, COUNT(g.rating) AS rating_count, f.vendor, f.product_id FROM #__qazap_products AS f JOIN #__qazap_reviews AS g ON g.product_id = f.product_id WHERE f.state = 1 AND f.block = 0 AND g.state = 1 GROUP BY f.vendor, f.product_id) ';
		
		$query->select('rating.average_rating, rating.rating_count')
			->join('LEFT', $subQuery . 'AS rating ON rating.vendor = a.id');
					
			$query->where('a.id = '.$pk);
			
			try
			{
				$db->setQuery($query);
				$data = $db->loadObject();				
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
				
			if (empty($data))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_SHOP_NOT_FOUND'));
			}
			
			$data->category_list = (!empty($data->category_list) && is_string($data->category_list)) ? json_decode($data->category_list, true) : 0;
			$data->shipment_methods = (!empty($data->shipment_methods) && is_string($data->shipment_methods)) ? json_decode($data->shipment_methods, true) : 0;
			$data->image = (!empty($data->image) && is_string($data->image)) ? json_decode($data->image, true) : array();				
			
			$this->_item[$pk] = $data;
		}
		return $this->_item[$pk];
	}
	
	public function getProductLists()
	{
		if($this->_product_lists === null)
		{
			$lists = array();
			$params = $this->getState('params');
			
			if($params->get('show_latest_products_shop', 1))
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
			
			if($params->get('show_featured_products_shop', 1))
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
			
			if($params->get('show_random_products_shop', 1))
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
			
			if($params->get('show_topselling_products_shop', 1))
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
			$options['limit'] 					= $params->get('featured_products_limit_shop', 4);

			$filters = array();
			$filters['vendors']					= $this->getState('vendor.id');		

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_featured_products = $helper->getList(0);		
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
			$options['list_type']							= 'latest';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 					= false;
			$options['access'] 								= true;
			$options['limitstart'] 						= 0;
			$options['limit'] 								= $params->get('latest_products_limit_shop', 4);

			$filters = array();
			$filters['vendors']								= $this->getState('vendor.id');		

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_latest_products = $helper->getList(0);		
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
			$options['list_type']							= 'topselling';
			$options['categories_as_filter']	= $params->get('categories_as_filter', 1);
			$options['countresult'] 					= false;
			$options['access'] 								= true;
			$options['limitstart'] 						= 0;
			$options['limit'] 								= $params->get('topselling_products_limit_shop', 4);

			$filters = array();
			$filters['vendors']								= $this->getState('vendor.id');		

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_topselling_products = $helper->getList(0);		
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
			$options['limit'] 								= $params->get('random_products_limit_shop', 4);

			$filters = array();
			$filters['vendors']								= $this->getState('vendor.id');	

			try 
			{
				$helper = QZProducts::getInstance($options, $filters);			
				$this->_random_products = $helper->getList(0);		
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

	public function getStoreInfo()
	{
		$lang = JFactory::getLanguage();
		$language = !empty($language) ? $language : $lang->getTag();
		$default_language = $lang->getDefault();
		
		if (!isset($this->_storeInfo[$language]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('a.shop_id, a.lang, a.name, a.company, a.contact_person, a.address_1, ' .
									   'a.address_2, a.city, a.state, a.country, a.zip, a.phone_1, a.phone_2, ' .
									   'a.fax, a.mobile, a.vat, a.additional_info, a.tos, a.description, a.modified_time, ' .
									   'a.modified_by')
						->from('#__qazap_shop AS a')
						->select('b.country_name')
						->join('LEFT', '#__qazap_countries AS b ON b.id = a.country')		
						->select('c.state_name')
						->join('LEFT', '#__qazap_states AS c ON c.id = a.state')						
						->where('a.lang IN (' . $db->quote($language). ','. $db->quote('*'). ','.  $db->quote($default_language) . ')')
						->group('a.shop_id, a.lang, a.name, a.company, a.contact_person, a.address_1, ' .
									  'a.address_2, a.city, a.state, a.country, a.zip, a.phone_1, a.phone_2, ' .
									  'a.fax, a.mobile, a.vat, a.additional_info, a.tos, a.description, a.modified_time, ' .
									  'a.modified_by');		
			
			try
			{
				$db->setQuery($query);
				$datas = $db->loadObjectList('lang');			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
				
			if (empty($datas))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_SHOP_NOT_FOUND'));
			}
			
			if(isset($datas[$language]))
			{
				$shop = $datas[$language];
			}
			elseif(isset($datas['*']))
			{
				$shop = $datas['*'];
			}
			else
			{
				$shop = $datas[$default_language];
			}						
			
			$this->_storeInfo[$language] = $shop;
		}
		
		return $this->_storeInfo[$language];
	}

	public function getColumns($alias = null)
	{
		if($this->_columns === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				 					->select('field_title')
				 					->from('`#__qazap_vendorfields`')
				 					->where('state = 1')
				 					->where('field_type != ' . $db->quote('fieldset'))
									->order('ordering ASC');
			
			try
			{
				$db->setQuery($query);
				$results = $db->loadColumn();	
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$columns = array('id', 'vendor_admin', 'vendor_group_id', 'shop_name', 'category_list', 'shipment_methods', 'created_time');
			
			if(!empty($results))
			{
				$columns = array_merge($columns, $results);
			}
			
			if(!empty($alias))
			{
				$count = count($columns);
				
				for ($i = 0; $i < $count; $i++)
				{
					$columns[$i] = $alias . '.' . $columns[$i];
				}
			}
			return $columns;
		}
	}		
	
}