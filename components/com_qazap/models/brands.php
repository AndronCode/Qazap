<?php
/**
 * brands.php
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

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelBrands extends JModelList 
{
	
	protected $title = null;
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JModel
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
											'id', 'a.id',
											'ordering', 'a.ordering',
											'state', 'a.state'
											);
		}

		parent::__construct($config);
	}

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		
		$brand_category_id = $app->input->getInt('brand_category_id', 0);
		$this->setState('brand_category_id', $brand_category_id);
		
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', $app->getCfg('list_limit', 0), 'uint');
		$limitstart = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'uint');		

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'a.ordering', 'string');	
		$orderCol = !empty($orderCol) ? $orderCol : $defaultOrder;
		$orderDir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'DESC', 'string');	
		$orderDir = !empty($orderDir) ? $orderDir : 'DESC';
		$this->setState('list.ordering', $orderCol);
		$this->setState('list.direction', $orderDir);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	/**
	* Method to get a store id based on model configuration state.
	*
	* This is necessary because the model is used by the component and
	* different modules that might need different sets of data or different
	* ordering requirements.
	*
	* @param	string		$id	A prefix for the store id.
	* @return	string		A store id.
	* @since	1.0.0
	*/
	protected function getStoreId($id = '') 
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id.= ':' . $this->getState('brand_category_id');
		$id.= ':' . $this->getState('list.ordering');
		$id.= ':' . $this->getState('list.direction');
		
		return parent::getStoreId($id);
	}

	/**
	* Build an SQL query to load the list data.
	*
	* @return	JDatabaseQuery
	* @since	1.0.0
	*/
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
		  $this->getState(
		          'list.select', array('a.*')
		  )
		);
		$query->from('`#__qazap_manufacturers` AS a');

		// Filter by published state
		$published = $this->getState('filter.state');
		
		if (is_numeric($published))
		{
			$query->where('a.state = '.(int) $published);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		
		// Filter the items over the search string if set.
		if ($search)
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');

			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.state_name LIKE ' . $token;
			$searches[] = 'b.country_name LIKE ' . $token;
			$searches[] = 'a.state_3_code LIKE ' . $token;
			$searches[] = 'a.state_2_code LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}  
		
		$brand_category_id = $this->getState('brand_category_id');
		
		if($brand_category_id > 0)
		{
			$query->where('manufacturer_category = ' . (int) $brand_category_id);
		}

		// Add the list ordering clause.
		$listOrder	= $this->getState('list.ordering');
		$listDirn	= $this->getState('list.direction');
		
		if($listOrder && $listDirn)
		{
			$query->order($db->escape($listOrder . ' ' .$listDirn));
		}

		return $query;
	}

	public function getItems() 
	{
		if($items = parent::getItems())
		{
			if(!empty($items))
			{
				foreach($items as &$item)
				{
					if(isset($item->images))
					{
						$item->images = (!empty($item->images) && is_string($item->images)) ? json_decode($item->images) : array();
					}
				}
			}
		}
		
		return $items;
	}
	
	public function getTitle()
	{
		$brand_category_id = $this->getState('brand_category_id');
		
		if(empty($brand_category_id))
		{
			return null;
		}
		
		if($this->title === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
									->select('manufacturer_category_name')
									->from('#__qazap_manufacturercategories')
									->where('id = ' . (int) $brand_category_id);
			
			try
			{
				$db->setQuery($query);
				$title = $db->loadResult();
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
					$this->title = false;
				}
			}
			
			if(empty($title))
			{
				JError::raiseError(404, JText::_('COM_QAZAP_BRAND_CATEGORY_NOT_FOUND'));
			}
			
			$this->title = $title;
		}
		
		return $this->title;
	}
    
}
