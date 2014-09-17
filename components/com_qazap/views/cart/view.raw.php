<?php
/**
 * view.raw.php
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewCart extends JViewLegacy
{
	protected $state;	
	protected $cart;
	protected $params;
	protected $isEmpty;	
	protected $result;	
	protected $errors;
	
	protected $continue_url;
	protected $cart_url;
	protected $product;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{		
		$this->_prepareCartView();
				
    // Check for errors.
    if (count($errors = $this->get('Errors'))) 
    {
			$this->errors = implode('<br/>', $errors);
    }			
		
		parent::display($tpl);
	}
	
	protected function _prepareCartView()
	{
		$app		= JFactory::getApplication();
		$module	= $app->input->getCmd('module');
		$layout	= $this->getLayout();

		$lastvisited_category_id = (int) $app->getUserState('com_qazap.category.lastvisted.id', 0);

		if($lastvisited_category_id > 0)
		{
			$this->continue_url = JRoute::_(QazapHelperRoute::getCategoryRoute($lastvisited_category_id));
		}
		else
		{
			$this->continue_url = JUri::base();
		}
		
		$this->cart_url = JRoute::_(QazapHelperRoute::getCartRoute());

		if(!empty($module) && JModuleHelper::isEnabled($module))
		{
			if($module = JModuleHelper::getModule($module))
			{
				$context = JModuleHelper::renderModule($module);
				$this->_return($context, $app);			
			}
		}		
		elseif($layout != 'ajaxpopup')
		{
	    $this->cart		= $this->get('Cart');
			$this->params	= QZApp::getConfig();
						
			$products 		= $this->cart->getProducts();
			
			if(empty($products))
			{
				$this->isEmpty = true;
			}
			else
			{
				$this->isEmpty = false;
			}
		}		
	}
	
	protected function _return($context, $app)
	{
		JResponse::setHeader('Pragma', 'no-cache', true);
		JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
		JResponse::setHeader('Expires', 0, true);
		
		echo $context;
		
		$app->close();
	}

}