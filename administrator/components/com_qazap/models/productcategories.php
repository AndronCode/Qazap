<?php

/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelProductcategories extends JModelList 
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) 
	{
			if (empty($config['filter_fields'])) 
			{
				$config['filter_fields'] = array(
							'id', 'a.id',
							'ordering', 'a.ordering',
							'state', 'a.state',
							'category_name', 'd.category_name',
							'sef_alias', 'a.sef_alias',
							'description', 'a.description',
							'order', 'a.order',
							'category_ordering', 'a.category_ordering',
							'number_of_products_per_row', 'a.number_of_products_per_row',
							'initially_number_of_listed_items', 'a.initially_number_of_listed_items',
							'page_title', 'a.page_title',
							'meta_keywords', 'a.meta_keywords',
							'meta_description', 'a.meta_description',
							'image_alt', 'a.image_alt',
							'used_url', 'a.used_url',
							'used_thumb_url', 'a.used_thumb_url',
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
			$app = JFactory::getApplication('administrator');

			// Load the filter state.
			$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
			$this->setState('filter.search', $search);

			$search_field = $app->getUserStateFromRequest($this->context . '.filter.search_field', 'filter_search_field');
			$this->setState('filter.search_field', $search_field);

			$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
			$this->setState('filter.state', $published);

			// Load the parameters.
			$params = JComponentHelper::getParams('com_qazap');
			$this->setState('params', $params);

			// List state information.
			parent::populateState('a.id', 'asc');
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
     * @since	1.6
     */
    protected function getStoreId($id = '') 
		{
			// Compile the store id.
			$id.= ':' . $this->getState('filter.search');
			$id.= ':' . $this->getState('filter.state');
			return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() 
		{		
			$lang = JFactory::getLanguage();
			$defaultLang = $lang->getDefault();        
			// Create a new query object.
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
							'list.select', array('a.*','d.category_name AS parent_name','COUNT(c.product_id) AS product_count', 'b.category_name')
						)
					);
			$query->from('`#__qazap_productcategories` AS a');
			
			// Join Category Details Table.
			$query->join('LEFT OUTER', '`#__qazap_categoryinfo` AS b ON b.category_id = a.id AND b.language = '.$db->Quote($defaultLang));
			
			// Join Product Table
			$query->join('LEFT OUTER', '`#__qazap_product` AS c ON a.id = c.product_categories');
			
			//Join Parent Category Table
			$query->join('LEFT OUTER','`#__qazap_categoryinfo` AS d ON a.category_ordering = d.category_id AND d.language = '.$db->Quote($defaultLang));
			
			// Always group by primary key
			$query->group('a.id');        
        
			// Filter by published state
			$published = $this->getState('filter.state');
			if (is_numeric($published)) {
				$query->where('a.state = '.(int) $published);
			} else if ($published === '') {
				$query->where('(a.state IN (0, 1))');
			}    

			// Filter by search in title
			$search_field = $this->getState('filter.search_field');
			$search = $this->getState('filter.search');
			if (!empty($search) && !empty($search_field)) {
				// Escape the search token.
				$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
				// Add the clauses to the query.
				$query->where($search_field.' LIKE ' . $token);
			}

			// Add the list ordering clause.
			$orderCol = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');
			if ($orderCol && $orderDirn) {
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}
			return $query;
    }

    public function getItems() 
		{
			$items = parent::getItems();
			return $items;
    }	

}
