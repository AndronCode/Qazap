<?php
/**
 * helper.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Categories Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/administrator/components/com_qazap/helpers/categories.php';
require_once JPATH_SITE . '/components/com_qazap/helpers/route.php';


abstract class ModQazapCategoriesHelper
{
	/**
	 * Get list of articles
	 *
	 * @param   JRegistry  &$params  module parameters
	 *
	 * @return array
	 */
	public static function getList(&$params)
	{
		$config = QZApp::getConfig();
		$options = array();
		$options['countItems'] = $params->get('numitems', 0);
		$options['countSubcat'] = $config->get('categories_as_filter', 1);
		
		$categories = QZCategories::getInstance($options);
		$category = $categories->get($params->get('parent', 'root'));

		if ($category != null)
		{
			$items = $category->getChildren();

			if ($params->get('count', 0) > 0 && count($items) > $params->get('count', 0))
			{
				$items = array_slice($items, 0, $params->get('count', 0));
			}
		
			return $items;
		}
	}
}
