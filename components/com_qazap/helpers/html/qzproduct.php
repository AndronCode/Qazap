<?php
/**
 * qzproduct.php
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
defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.0.0
 */
abstract class JHtmlQZProduct
{
	static $loadJS = true;
	static $user;
	
	public static function display(QZProductNode $product, $global_params, $options = array())
	{
		$options = (array) $options;
		$params = clone($global_params);
		$params = ($params instanceof JRegistry) ? $params : QZApp::getConfig();
		$params->merge($product->getParams());
		
		$data = array();
		$data['product'] = $product;
		$data['params'] = $params;	
		$data['url'] = !empty($url) ? $url : QazapHelperRoute::getProductRoute($product->product_id, $product->category_id);
		$data['product_url'] = QazapHelperRoute::getProductRoute($product->product_id, $product->category_id);
		$data['load_js'] = static::$loadJS;
		$data['user'] = empty(static::$user) ? JFactory::getUser() : static::$user;

		$layout = new JLayoutFile('qazap.products.product', null, $options);
		
		if(static::$loadJS)
		{
			static::$loadJS = false;
		}
				
		return $layout->render($data);		
				
	}
	
	public static function addtocart($product, $global_params, $url = null, $options = array())
	{
		$options = (array) $options;
		$reload_params = isset($options['reload_params']) ? $options['reload_params'] : true;
		$params = clone($global_params);
		
		if($reload_params)
		{
			$params = ($params instanceof JRegistry) ? $params : QZApp::getConfig();
			$params->merge($product->getParams());					
		}
		
		// Decide if add to cart button to be displayed
		$buy_action = 'addtocart';

		if($product->in_stock - $product->booked_order <= 0 && $params->get('enablestockcheck'))
		{
			$stockout_handle = $params->get('stockout_action', 'notify');
			
			if($stockout_handle == 'notify')
			{
				$buy_action = 'notify';
			}
			elseif($stockout_handle == 'hide')
			{
				$buy_action = null;
			}	
		}
		
		$data = array();
		$data['product'] = $product;
		$data['params'] = $params;	
		$data['buy_action'] = $buy_action;
		$data['url'] = !empty($url) ? $url : QazapHelperRoute::getProductRoute($product->product_id, $product->category_id);

		$layout = new JLayoutFile('qazap.products.addtocart', null, $options);
		return $layout->render($data);		
	}

}
