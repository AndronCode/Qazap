<?php
/**
 * offline.php
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

jimport('joomla.application.component.modellist');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelOffline extends JModelLegacy
{
	
	protected $returnError = true;
	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0
	*/
	protected function populateState() 
	{
		$app = JFactory::getApplication();		
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

	}

	public function getStoreInfo()
	{
		$model = QZApp::getModel('shop', array('ignore_request' => true), false);
		
		if(!$store = $model->getStoreInfo())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return $store;
	}
}