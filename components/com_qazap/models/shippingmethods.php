<?php
/**
 * shippingmethods.php
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

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_qazap/models/shipmentmethods.php';
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelShippingmethods extends QazapModelshipmentmethods 
{

	protected $_relatedCategories = array();
	protected $_reviews = array();
	protected $_reviewDone = null;
	protected $_selection = array();

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

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		parent::populateState('a.ordering', 'asc');
	}


	
	/**
	* Method to get article data.
	*
	* @param   integer    The id of the article.
	*
	* @return  mixed  Menu item data object on success, false on failure.
	*/
	public function getItem()
	{
		return parent::getItems();
	}

	
}