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
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

class QazapController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{		
		$app = JFactory::getApplication();
		$view = $app->input->getCmd('view', 'home');		
		$app->input->set('view', $view);
		
		if($view == 'shops' && !JLanguageMultilang::isEnabled() && !$this->shopIsMultilingual())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap', false));
			return false;
		}
		
		parent::display($cachable, $urlparams);

		return $this;
	}
	
	protected function shopIsMultilingual()
	{
		$db = JFactory::getDbo();
		
		$sql = $db->getQuery(true)
					->select('shop_id')
					->from('#__qazap_shop')
					->where('lang = '. $db->quote('*'));
					
		$db->setQuery($sql);
		$result = $db->loadResult();
		
		if(!empty($result))
		{
			return false;
		}
		
		return true;
	}	
}
