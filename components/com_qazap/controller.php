<?php
/**
 * controller.php
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

jimport('joomla.application.component.controller');

class QazapController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */	
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view name and format from the Request.
		$vName   = $this->input->getCmd('view', 'categories');
		$this->input->set('view', $vName);
		$config = QZApp::getConfig();
		$user = JFactory::getUser();

		if($config->get('shop_offline', 0))
		{
			if(!$user->get('isRoot'))
			{
				$this->input->set('view', 'offline');
			}
			elseif($vName == 'offline')
			{
				$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=categories', false));
				return;				
			}
		}
		elseif($vName == 'offline')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=categories', false));
			return;
		}
		
		$cachableViews = array(
											'product', 'category', 'categories', 'brands',
											'brand', 'shops', 'shop', 'tos'
											);											
		
		$safeurlparams = array('category_id' => 'INT', 'product_id' => 'INT', 'p_id' => 'ARRAY', 'id' => 'INT', 'download_id' => 'INT', 'passcode' => 'ALNUM', 'filter_search' => 'STRING', 'vendor_id' => 'ARRAY', 'brand_id' => 'ARRAY', 'attribute' => 'ARRAY', 'min_price' => 'FLOAT', 'max_price' => 'FLOAT', 'year' => 'INT', 'month' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT','showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'orderby' => 'CMD', 'order_dir' => 'CMD', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT');
		
		if(in_array($vName, $cachableViews) && $user->guest && $this->input->getMethod() != 'POST')
		{
			$cachable = true;
		}		
		
		if($vName == 'form' && !$this->checkEditId('com_qazap.edit.product', $this->input->getInt('product_id')))
		{
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $this->input->getInt('product_id')));					
		}
		elseif($vName == 'seller' || $vName == 'sellerform')
		{	
			$qzuser = QZUser::get();
			$task = $this->getTask();
			
			if($user->guest)
			{
				$return = $this->getReturnPageURL('seller');
				$this->setMessage(JText::_('COM_QAZAP_SELLER_LOGIN_MESSAGE'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return=' . $return, false));
				return;				
			}
			elseif($vName == 'seller' && !$qzuser->isVendor)
			{
				if($config->get('enable_vendor_registration', 1))
				{
					$this->setRedirect(JRoute::_('index.php?option=com_qazap&task=seller.add', false));
					return;					
				}
				else
				{
					return JError::raiseError(403, JText::_('COM_QAZAP_VENDOR_REGISTRATION_DISABLED'));
				}
			}
		}
		elseif($vName == 'profile')
		{	
			if($user->guest)
			{
				$return = $this->getReturnPageURL('profile');
				$this->setMessage(JText::_('COM_QAZAP_PROFILE_LOGIN_MESSAGE'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return=' . $return, false));
				return;				
			}
			
			$document = JFactory::getDocument();
			$vFormat = $document->getType();
			$lName   = $this->input->getCmd('layout', 'default');
			$listLayouts = array('orderlist', 'wishlist', 'waitinglist');
			
			if(in_array($lName, $listLayouts))
			{
				$view = $this->getView($vName, $vFormat);			
				$view->setModel($this->getModel('profilelists'), true);	
				$view->setLayout($lName);				
			}	
		}
		elseif($user->guest && ($vName == 'shippingmethods' || $vName == 'categorylist'))
		{
			return JError::raiseError(403, JText::_(''));
		}
		elseif($config->get('catalogue_only', 0) && ($vName == 'cart'))
		{
			return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_SHOP_IS_RUNNING_IN_CATALOGUE_MODE'));	
		}
		elseif(($config->get('compare_system', 1) != 1) && ($vName == 'compare'))
		{
			return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_COMPARISON_SYSTEM_DISABLED'));		
		}
		
		if($vName == 'form' || $vName == 'sellerform' || $vName == 'seller' || $vName == 'categories')
		{
			// Load backend language for product form and seller form. 
			// Not good. We need to find some other alternative
			JFactory::getLanguage()->load('com_qazap', JPATH_ADMINISTRATOR);			
		}
		
		parent::display($cachable, $safeurlparams);
		return $this;
	}
	
	protected function getReturnPageURL($view = 'profile')
	{
		$url = JRoute::_('index.php?option=com_qazap&view=' . $view);
		return urlencode(base64_encode($url));
	}
	

}