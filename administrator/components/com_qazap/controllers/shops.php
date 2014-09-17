<?php
/**
 * shops.php
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

jimport('joomla.application.component.controlleradmin');

/**
* Shops list controller class.
*/
class QazapControllerShops extends JControllerAdmin
{	
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('disableMultiple', 'createMultiple');
		$this->registerTask('recreateMultiple', 'createMultiple');			
	}
	
	/**
	* Proxy for getModel.
	* @since	1.6
	*/
	public function getModel($name = 'shop', $prefix = 'QazapModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	public function createMultiple()
	{
		$task = $this->getTask();
		$action = 'create';
		
		if($task == 'disableMultiple')
		{
			$action = 'disable';
		}
		
		$model = $this->getModel();
		
		$result = $model->createMultiple($action);
		
		if($result && $action == 'create')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=shops', false));
		}
		elseif($result && $action == 'disable')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=shop&layout=edit', false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=shop&layout=edit', false));
		}
	}
	
	public function show()
	{
		$multiple_language = JLanguageMultilang::isEnabled();
		$model = $this->getModel();
		
		if($multiple_language)
		{
			if(!$model->createMultiple($action='create'))
			{
				$this->setRedirect(JRoute::_('index.php?option=com_qazap', false));
				return false;
			}
			
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=shops', false));
		}
		else
		{			
			if(!$model->createMultiple($action='delete'))
			{
				$this->setRedirect(JRoute::_('index.php?option=com_qazap', false));
				return false;
			}
			
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=shop&layout=edit', false));
		}
	}
}