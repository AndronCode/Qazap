<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';
/**
 * States list controller class.
 */
class QazapControllerBrands extends QazapController
{
	/**
	* Proxy for getModel.
	* @since	1.6
	*/
	public function getModel($name = 'brand', $prefix = 'QazapModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}    
}