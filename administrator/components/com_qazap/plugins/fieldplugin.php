<?php
/**
 * fieldplugin.php
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
defined('_JEXEC') or die();


abstract class QZFieldPlugin extends QZPlugin
{	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}

	/**
	* This event is called by cart model when user confirms as order
	* 
	* @param	object	$ordergroup		QZCart object of present order for which the order confirmation is requested
	* @param	object	$carModel			QazapModelCart object of cart model
	* 
	* @return	boolean	Return false in case of any error. 
	* @note		Plugin can redirect to some other pages if needed. Eg. Payment gateways for payment processing
	*/
	public function onGetOrderConfirmation(QZCart $ordergroup, QazapModelCart $cartModel)
	{
		return;
	}

}