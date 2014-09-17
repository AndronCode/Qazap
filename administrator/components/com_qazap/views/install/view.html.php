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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class QazapViewInstall extends JViewLegacy
{
	/**
	 * Array of PHP config options
     *
     * @var    array
	 * @since  3.1
	 */
	protected $options;
	
	protected $packages;
	
	protected $packagesOk;
	/**
	 * Array of PHP settings
     *
     * @var    array
	 * @since  3.1
	 */
	protected $phpSettings;
	
	/**
	 * Array of MySQL settings
     *
     * @var    array
	 * @since  3.1
	 */
	protected $MySQLSettings;	
	
	protected $canInstall;
	
	protected $actions;
	
	protected $stepValue;
	
	protected $steps;	
	
	protected $installSampleData;	
	

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->packages						= $this->get('PackageAvailability');
		$this->packagesOk					= $this->get('PackageSufficient');
		$this->options						= $this->get('PhpOptions');
		$this->phpSettings				= $this->get('PhpSettings');
		$this->MySQLSettings			= $this->get('MySQLSettings');
		$this->canInstall					= $this->get('PhpOptionsSufficient');	
		$this->actions						= $this->get('Actions');
		$this->stepValue					= $this->get('StepValue');
		$this->steps 							= $this->get('Steps');	
		$this->installSampleData	= $this->get('SampleData');	
		
		$app = JFactory::getApplication();
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			$errors = array_unique($errors);
			$app->enqueueMessage(implode("<br/>", $errors), 'error');
		}
		
		JToolBarHelper::title('<span>' . JText::_('COM_QAZAP') . '</span>', ' qzicon-stack-plus');
		
		if($this->canInstall)
		{
			$app->input->set('hidemainmenu', true);
		}
		
		parent::display($tpl);
	}

}
