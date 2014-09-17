<?php
/**
 * notify.php
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

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 */
class QazapControllerNotify extends JControllerForm
{
	public function activate()
	{
		$input = JFactory::getApplication()->input;
		$key = $token = $input->getAlnum('key');

		// Attemp to activate Notify
		$model = QZApp::getModel('Notify', array());

		if(!$model->activate($key))
		{
			$this->setMessage($model->getError());
		}
		else
		{
			$this->setMessage('COM_QAZAP_PRODUCT_NOTIFICATION_ACTIVATED', 'success');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_qazap', false));
	}
}
