<?php
/**
 * reviews.php
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

jimport('joomla.application.component.modellist');

/**
* Methods supporting a list of Qazap records.
*/
class QazapModelReviews extends JModelList 
{
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
			'id', 'a.id',
			'ordering', 'a.ordering',
			'state', 'a.state',
			'created_by', 'a.created_by',
			'product', 'a.product',
			'comment', 'a.comment',
			'rating', 'a.rating',
			'product_name','pdd.product_name',
			'name', 'review_by.name',
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

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);


		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
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
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();

		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
		    $this->getState(
		            'list.select', 'a.*'
		    )
		);
		$query->from('`#__qazap_reviews` AS a');

		// Join over the user field review 'done_by'
		$query->select('review_by.name AS name');
		$query->join('LEFT', '#__users AS review_by ON review_by.id = a.user_id');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		$query->join('INNER', '#__qazap_products AS b ON b.product_id = a.product_id');
		if($multiple_language)
		{
			$query->select('CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END AS product_name');			
		
			$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = b.product_id AND pd.language = '.$db->Quote($present_language));
			$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = b.product_id AND pdd.language = '.$db->Quote($default_language));				
		}
		else
		{
			$query->select('pd.product_name');
			$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = b.product_id AND pd.language = '.$db->Quote($default_language));
		}
        

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) 
		{
			$query->where('a.state = ' . (int) $published);
		} 
		else if ($published === '') 
		{
			$query->where('(a.state IN (0, 1))');
		}

        // Filter by search in title
		$search = $this->getState('filter.search');
		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Add the clauses to the query.
			$query->where('pdd.product_name LIKE ' . $token . ' OR a.id LIKE ' . $token .' OR created_by.name LIKE '.$token);
		}

        

		//Filtering product
		$filter_product = $this->state->get("filter.product");
		if ($filter_product) 
		{
			$query->where("a.product = '".$db->escape($filter_product)."'");
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
		$items = parent::getItems();

		return $items;
	}

}
