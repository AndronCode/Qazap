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
class QazapModelProduct extends JModelItem 
{

	protected $_relatedCategories = array();
	protected $_reviews = array();
	protected $_reviewDone = null;
	protected $_selection = array();
	protected $_hasVarients = array();

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0.0
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('product_id');
		$this->setState('product.id', $pk);
		
		$category_id = $app->input->getInt('category_id');
		$this->setState('product.category_id', $category_id);
		
		$vendor_id = $app->input->getInt('vendor_id');
		$this->setState('product.vendor_id', $vendor_id);				

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

		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0.0
	 */
	public function getTable($type = 'Product', $prefix = 'QazapTable', $config = array())
	{
		// Include admin tables path
		JTable::addIncludePath(QZPATH_TABLE_ADMIN);
		return JTable::getInstance($type, $prefix, $config);
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
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');
		$category_id = (int) $this->getState('product.category_id');
		$vendor_id = (int) $this->getState('product.vendor_id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (!isset($this->_item[$pk]))
		{
			try
			{
				//$p = JProfiler::getInstance('Application');
				$published = $this->getState('filter.published');
				$options = array();
				$options['access'] = true;
				//$options['rating'] = false;
				$filters = array();
				$filters['state'] = !$published ? null : $published;
				$filters['vendors'] = !$vendor_id ? null : $vendor_id;
				//$p->mark('Start');
				$helper = QZProducts::getInstance($options, $filters);
				$data = $helper->get($pk, $category_id);				
				//$p->mark('Stop');
				//qzdump($p->getBuffer());exit;

				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_PRODUCT_NOT_FOUND'));
				}
				
				if($data->related_products)
				{
					$data->related_products = $data->getRelatedProducts();
				}
				
				if($data->related_categories && is_string($data->related_categories))
				{
					$data->related_categories = json_decode($data->related_categories);
					$data->related_categories = $this->getRelatedCategories($data->related_categories);
					if($data->related_categories === false && $this->getError())
					{
						$this->setError($this->getError());
					}
				}
				
				if($data->membership)
				{
					$data->membership = $data->getMemberships();
				}
				
				if($data->attributes)
				{
					$data->attributes = $data->getAttributes();
				}
				
				if($data->custom_fields)
				{
					$data->custom_fields = $data->getCustomfields();
				}	
				
				if($data->params)
				{
					$data->params = $data->getParams();
				}							
				
				// Check for published state if filter set.
				if (is_numeric($published) && $data->state != $published)
				{
					return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_PRODUCT_NOT_FOUND'));
				}

				$temp = clone $this->getState('params');
				$temp->merge($data->params);
				$data->params = $temp;

				//QZApp::dump($data);exit;

				// Compute selected asset permissions.
				$user = JFactory::getUser();
				//print_r($data);exit;
				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_qazap.category.' . $data->category_id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
				}
				
				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();
					if ($data->product_id == 0)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
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
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the product.
	 *
	 * @param   integer  $pk  Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

			$table = $this->getTable();
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}

	/**
	* Method to get related categories of a product
	* 
	* @param array $category_ids Array of related category ids
	* 
	* @return mixed (array/boolean) Array category objects or false in case of failure
	* @since	1.0.0
	*/
	public function getRelatedCategories($category_ids = array())
	{		
		$category_ids = (array) $category_ids;
		$category_ids = array_filter(array_map('intval', $category_ids));
		
		// Remove root category id 1 is exists in teh array
		if(($key = array_search(1, $category_ids)) !== false) 
		{
		    unset($category_ids[$key]);
		}
		
		$hash = md5(serialize($category_ids));
		
		if(!count($category_ids) && !isset($this->_relatedCategories[$hash]))
		{
			$this->_relatedCategories[$hash] = false;			
		}

		if(isset($this->_relatedCategories[$hash]))
		{
			return $this->_relatedCategories[$hash];
		}
		
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();
		$config = QZApp::getConfig();
		
		$option = array();
		$options['access'] = true;
		$options['published'] = true;
		$options['countItems'] = true;
		$options['countSubcat'] = true;
			
		$query = $db->getQuery(true);
		// Select c for categories table and left join d for category_details page.
		$query->select('c.category_id, c.asset_id, d.title, d.category_details_id, c.access, d.alias, '. 
									'd.description, c.images, c.hits, d.language, c.params, c.parent_id, d.path');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('d.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('d.category_id');
		$case_when .= $query->concatenate(array($c_id, 'd.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when);
		
		$query->from('#__qazap_categories as c');
		
		if($multiple_language)
		{
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = c.category_id AND d.language = '.$db->quote($present_language));				
		}
		else
		{
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = c.category_id AND d.language = '.$db->quote($default_language));
		}

		if ($options['access'])
		{
			$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		if ($options['published'] == 1)
		{
			$query->where('c.published = 1');
		}
		
		if(count($category_ids) == 1)
		{
			$query->where('c.category_id = ' . (int) $category_ids[0]);
		}
		else
		{
			$query->where('c.category_id IN (' . implode(',', $category_ids) . ')');
		}
		
		$subQuery = ' (SELECT cat.category_id as category_id FROM #__qazap_categories AS cat JOIN #__qazap_categories AS parent ' .
			'ON cat.lft BETWEEN parent.lft AND parent.rgt WHERE parent.published != 1 GROUP BY cat.category_id) ';
		$query->join('LEFT', $subQuery . 'AS badcats ON badcats.category_id = c.category_id')
			->where('badcats.category_id is null');

		// Note: i for item
		if ($options['countItems'])
		{
			if ($options['published'] == 1)
			{
				if($options['countSubcat'])
				{
					$countSubquery = ' (SELECT subcat.category_id as category_id FROM #__qazap_categories AS subcat WHERE subcat.lft BETWEEN c.lft AND c.rgt AND subcat.published = 1 GROUP BY subcat.category_id) ';
					$query->join(
						'LEFT',
						$db->quoteName('#__qazap_products') . ' AS i ON i.' . $db->quoteName('category_id') . ' IN '.$countSubquery.' AND i.state = 1 AND i.parent_id = 0'
					);
					//echo $query;exit;			
				}
				else
				{
					$query->join(
						'LEFT',
						$db->quoteName('#__qazap_products') . ' AS i ON i.' . $db->quoteName('category_id') . ' = c.category_id AND i.state = 1  AND i.parent_id = 0'
					);					
				}

			}
			else
			{
				if($options['countSubcat'])
				{
					$countSubquery = ' (SELECT subcat.category_id as category_id FROM #__qazap_categories AS subcat WHERE subcat.lft BETWEEN c.lft AND c.rgt GROUP BY subcat.category_id) ';
					$query->join('LEFT', $db->quoteName('#__qazap_products') . ' AS i ON i.' . $db->quoteName('category_id') . ' IN '.$countSubquery .' AND i.parent_id = 0');
				}
				else
				{
					$query->join('LEFT', $db->quoteName('#__qazap_products') . ' AS i ON i.' . $db->quoteName('category_id') . ' = c.category_id  AND i.parent_id = 0');
				}
			}
			$query->select('COUNT(i.' . $db->quoteName('product_id') . ') AS numitems');
		}
		
		// Group by
		$query->group('c.category_id, c.asset_id, c.access, c.images, c.hits, c.params, c.parent_id');

		try 
		{
			// Get the results
			$db->setQuery($query);
			$results = $db->loadObjectList('category_id');
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			$this->_relatedCategories[$hash] = false;
			return $this->_relatedCategories[$hash];
		}
		
		if(!empty($results))
		{
			$results = QZHelper::sortArrayByArray($results, $category_ids);
			foreach($results as &$result)
			{
				if($result->images && is_string($result->images))
				{
					$result->images = json_decode($result->images);
				}
				
				$tmp = new JRegistry;
				$tmp->loadString($result->params);
				$result->params = $tmp;					
			}
			
			$this->_relatedCategories[$hash] = $results;
		}
		else
		{
			$this->_relatedCategories[$hash] = false;
		}
		
		return $this->_relatedCategories[$hash];
	}
	
	/**
	* Method to get all reviews of a product
	* 
	* @param integer $product_id ID of the product
	* 
	* @return	array
	* @since	1.0.0
	*/
	public function getReviews($product_id = null)
	{
		$product_id = $product_id ? $product_id : $this->getState('product.id');
		
		if(!isset($this->_reviews[$product_id]))
		{			
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
				 ->select('a.id, a.user_id, a.comment, a.rating, a.created_by_time AS review_date')
				 ->from('#__qazap_reviews AS a')
				 ->select('u.name, u.username, u.email')
				 ->join('LEFT', '#__users AS u ON u.id = a.user_id')
				 ->where('a.product_id = ' . (int) $product_id)
				 ->where('a.state = 1')
				 ->where('u.block = 0');
				 
			try
			{
				$db->setQuery($sql);
				$reviews = $db->loadObjectlist();			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				$this->_reviews[$product_id] = array();
				return $this->_reviews[$product_id];
			}
			
			if(count($reviews))
			{
				$this->_reviews[$product_id] = $reviews;
			}
			else
			{
				$this->_reviews[$product_id] = array();
			}
		}

		return $this->_reviews[$product_id];
	}
	
	public function getUserReviewDone()
	{
		if($this->_reviewDone === null)
		{
			$user = JFactory::getUser();
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
				 ->select('COUNT(id)')
				 ->from('#__qazap_reviews')
				 ->where('user_id = ' . (int) $user->get('id'))
				 ->where('product_id = ' . (int) $this->getState('product.id'));
				 
			try
			{
				$db->setQuery($sql);
				$count = $db->loadResult();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				$this->_reviewDone = false;
			}	
			
			$this->_reviewDone = ($count && (int) $count > 0) ? true : false;		
		}

		return $this->_reviewDone;
	}

	/**
	* Method to save a Wishlist
	* 
	* @param array $data Wishlist form data $data['user_id'], $data['product_id']
	* 
	* @return	boolean
	* @since	1.0.0
	*/
	public function saveWishlist($data)
	{
		// Check for duplicate wishlist
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->select('count(id)')
			 ->from('#__qazap_wishlist')
			 ->where('user_id = ' . $db->quote($data['user_id']))
			 ->where('product_id = ' . (int) $data['product_id']);
			 
		try
		{
			$db->setQuery($sql);
			$results = $db->loadResult();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}
		
		if($results)
		{
			$this->setError(JText::_('COM_QAZAP_DUPLICATE_WISHLIST'));
			return false;
		}
		
		// Get Wishlist Table
		$table = $this->getTable('Wishlist');
		
		if(!$table->save($data))
		{
			$this->setError($table->getError());
			return false;			
		}
	
		return true;		
	}
	
	/**
	* Method to set Ask A Question and Contact for Price Mail
	* 
	* @param array $data	Form data array
	* 
	* @return boolean
	* @since	1.0.0
	*/
	public function askQuestion($data)
	{		
		if(!QZHelper::validateEmail($data['user_email']))
		{
			$this->setError(JText::_('COM_QAZAP_ENTER_VALID_EMAIL'));
			return false;
		}
		
		$data['question'] = strip_tags($data['question']);
		
		if(empty($data['question']))
		{
			$this->setError(JText::_('COM_QAZAP_ENTER_SOME_QUESTION'));
			return false;			
		}

		$mailModel = QZApp::getModel('Mail', array('ignore_request'=>true, 'display_message' => false));
		
		if(!$mailModel->send('question', $data))
		{
			$this->setError($mailModel->getError());
			return false;
		}

		return true;		
	}

	/**
	* Method to get a selected product details
	* 
	* @param array $selectedProductArray Array of selected product_id, membership_id, attributes ids and quantity 
	* 
	* @return mixed (object/false) Product details object or false in case of failure
	* @since	1.0.0
	*/
	public function getSelection($selectedProductArray)
	{
		$cartModel = QZApp::getModel('Cart', array(), $admin = false);
		list($product_id, $attr_ids, $membership_id, $quantity) = $cartModel->getVars($selectedProductArray);
		$group_id = $cartModel->getGroupID($product_id, $attr_ids, $membership_id);
		$hash = md5('group_id:' . $group_id . '.quantity:' . $quantity);
		
		if(!isset($this->_selection[$hash]))
		{
			try 
			{			
				$selection = QZSelectedProduct::getItem($product_id, $attr_ids, $membership_id, $quantity);	
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
					$this->_selection[$hash] = false;
				}
			}
			
			$this->_selection[$hash] = $selection;	
		}	
		
		return $this->_selection[$hash];
	}	

}