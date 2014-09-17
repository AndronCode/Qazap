<?php
/**
 * view.html.php
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

jimport('joomla.application.component.view');

/**
* View class for a list of Qazap.
*/
class QazapViewHome extends JViewLegacy
{
	protected $state;
  protected $orders;
  protected $latest_products;
  protected $topselling_products;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state                = $this->get('State');
		$this->orders            	  = $this->get('Orders');
		$this->latest_products      = $this->get('LatestProducts');
		$this->topselling_products  = $this->get('TopsellingProducts');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}
        
		QazapHelper::addSubmenu('home');

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_HOME_TITLE'), 'home-2');

		$this->sidebar = QZHtmlSidebar::render();
		parent::display($tpl);
	}
}