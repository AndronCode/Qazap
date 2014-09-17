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

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Shoppergroups list controller class.
 */
class QazapControllerCategory extends QazapController
{
	
	public function clearFilter()
	{
		$category_id = $this->input->getInt('category_id', 0);
		$filter = $this->input->getCmd('filter', null);
		$model = $this->getModel();
		
		if(strtoupper($filter) == 'PRICES')
		{
			// Clear both price filers userstates
			$model->clearUserStates($category_id, 'min_price');
			$model->clearUserStates($category_id, 'max_price');
		}
		else
		{
			// Clear the userstate
			$model->clearUserStates($category_id, $filter);			
		}
		
		// Redirect back to the page
		$this->setRedirect($this->getReturnPage());
	}	
	
	public function filter()
	{
		$catid = $this->input->getInt('category_id', 0);
		$data = $this->input->post->get('qzform', array(), 'array');
		$input = JFilterInput::getInstance();

		$filter = array();
		$filter['filter_search']	= $this->input->get('filter_search', null, 'string');
		$filter['searchphrase']		= $this->input->get('searchphrase', null, 'string');
		$filter['vendor_id']			= $this->input->get('vendor_id', null, 'array'); 
		$filter['brand_id']				= isset($data['brand_id']) ? $input->clean($data['brand_id'], 'array') : null;
		$filter['attribute']			= isset($data['attribute']) ? $input->clean($data['attribute'], 'array') : null;
		$filter['min_price']			= isset($data['min_price']) ? $input->clean($data['min_price'], 'float') : null;
		$filter['max_price']			= isset($data['max_price']) ? $input->clean($data['max_price'], 'float') : null;
		
		$min_price_unfiltered	= isset($data['min_price_unfiltered']) ? $input->clean($data['min_price_unfiltered'], 'float') : null;
		$max_price_unfiltered	= isset($data['max_price_unfiltered']) ? $input->clean($data['max_price_unfiltered'], 'float') : null;
		
		if($min_price_unfiltered == $filter['min_price'])
		{
			$filter['min_price'] = null;
		}
		
		if($max_price_unfiltered == $filter['max_price'])
		{
			$filter['max_price'] = null;
		}
		
		$url = QazapHelperRoute::getCategoryRoute($catid, $order = 0, $dir = 0, $start = '', $limit = '', $filter, $language = 0);

		if(!empty($url))
		{
			$model = $this->getModel();
			// Clear the userstate
			$model->clearUserStates($catid);		
			$this->setRedirect(JRoute::_($url, false));			
		}
		else
		{
			// Redirect back to the page
			$this->setRedirect($this->getReturnPage());			
		}

	}
	
	public function search()
	{
		$app = JFactory::getApplication();
		$catid  = $this->input->post->getInt('searchcategory', 0);
		$model = $this->getModel();
		$model->setStateFromUserState($catid);
				
		$badchars = array('#', '>', '<', '\\');
		$searchword = trim(str_replace($badchars, '', $this->input->getString('searchword', null, 'post')));
		$searchphrase = trim($this->input->getString('searchphrase', 'any', 'post'));		
		// if searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"')
		{
			$searchword = substr($searchword, 1, -1);
			$model->setState('filter.searchphrase', 'exact');
		}
		else
		{
			$model->setState('filter.searchphrase', $searchphrase);
		}	

		$url = $model->getURL($catid, 'filter_search', $searchword, $skip = array());
		$this->setRedirect(JRoute::_($url, false));
	}

	protected function getReturnPage()
	{
		$return = $this->input->post->get('return', null, 'base64');
		$category_id = $this->input->getInt('category_id', 0);
		
		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			$url = QazapHelperRoute::getCategoryRoute($category_id);
			return JRoute::_($url, false);
		}
		else
		{
			return base64_decode($return);
		}
	}	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Category', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}