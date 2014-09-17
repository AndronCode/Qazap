<?php
/**
 * install.php
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

jimport('joomla.application.component.controllerlegacy');

/**
 * Cartattributestype controller class.
 */
class QazapControllerInstall extends JControllerLegacy
{
	public function preinstall()
	{
		JSession::checkToken('get') or $this->throwError(JText::_('JINVALID_TOKEN'));
		
		$model = $this->getModel();
		$view = $this->getView('install', 'raw');
		$view->setModel($model, true);
		$view->setLayout('install');
		$this->displayJSON($view);
	}
	
	protected function displayJSON($view)
	{
		ob_end_clean();
		$this->getJSONHeader();
		$view->display();
		$html = ob_get_clean();		
		echo json_encode(array('error' => 0, 'html' => $html));
		flush();
		jexit();		
	}
	
	protected function throwError($msg)
	{
		ob_end_clean();
		$this->getJSONHeader();
		echo json_encode(array('error' => 1, 'html' => (string) $msg));
		flush();
		jexit();		
	}
	
	protected function returnTrue($html = null)
	{
		ob_end_clean();
		$this->getJSONHeader();
		echo json_encode(array('error' => 0, 'html' => $html));
		flush();
		jexit();		
	}	
	
	protected function getJSONHeader()
	{
		header('Content-type: application/json');	
		header('Content-type: application/json');	
		header('Cache-Control: public,max-age=1,must-revalidate');
		header('Expires: '.gmdate('D, d M Y H:i:s',($_SERVER['REQUEST_TIME']+1)).' GMT');
		header('Last-modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT');	
		if(function_exists('header_remove')) 
    {
			header_remove('Pragma');
		}			
	}
	
	public function getsteps($active = null, $failed = null)
	{
		JSession::checkToken('get') or $this->throwError(JText::_('JINVALID_TOKEN'));
		
		$active = !empty($active) ? $active : $this->input->getCmd('active', null);
		$failed = !empty($failed) ? $failed : $this->input->getCmd('failed', null);
		$model = $this->getModel();
		$processes = (array) $model->getActions();
		$processes[] = 'all-done';

		if(empty($active) && empty($failed))
		{
			$this->throwError('No process variable passed');
		}
		elseif(!empty($active) && !in_array(strtolower($active), $processes))
		{
			$this->throwError('No process passed or invalid process1');
		}		
		elseif(!empty($failed) && !in_array(strtolower($failed), $processes))
		{
			$this->throwError('No process passed or invalid process2');
		}			

		$model->set('activeStep', $active);
		$model->set('failedStep', $failed);
		
		$view = $this->getView('install', 'raw');
		$view->setModel($model, true);
		$view->setLayout('install');
		$this->displayJSON($view);		
	}
	
	public function run()
	{
		JSession::checkToken('get') or $this->throwError(JText::_('JINVALID_TOKEN'));
		
		$process = $this->input->getCmd('process', null);
		$model = $this->getModel();
		$processes = $model->getActions();
		
		if(empty($process) || !in_array(strtolower($process), $processes))
		{
			$this->throwError('No process passed or invalid process');
		}
		
		if(!$model->run($process))
		{
			$this->throwError($model->getError());			
		}
		
		$this->returnTrue();
	}
	
	public function installSampleData()
	{
		JSession::checkToken('get') or $this->throwError(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		
		if(!$model->installSampleData())
		{
			$this->throwError($model->getError());			
		}
		
		$this->returnTrue(JText::_('COM_QAZAP_INSTL_SAMPLE_DATA_INSTALLED_MSG'));
	}	
	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'install', $prefix = 'QazapModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}	
}