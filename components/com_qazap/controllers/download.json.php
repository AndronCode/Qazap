<?php
/**
 * download.json.php
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

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Shoppergroups list controller class.
 */
class QazapControllerDownload extends QazapController
{
	public function getStatus()
	{
		$config = QZApp::getConfig();
		$model = $this->getModel();
		$download_id = $this->input->getInt('download_id');
		$passcode = $this->input->getAlnum('passcode');	
		$download = $model->getDownload($download_id, $passcode);
		$return = array('error' => 0);
		if(!$download)
		{
			$return['error'] = 1; 
		}
		else
		{
			$limit = (int) $config->get('download_limit', 0);
			if($limit > 0) 
			{
				$download_left = ($limit - (int) $download->download_count);
			}
			else
			{
				$download_left = JText::_('COM_QAZAP_DOWNLOAD_NOLIMIT');
			}			
			
			$return['download_count'] = $download->download_count;
			$return['last_download'] = $download->last_download;
			$return['download_left'] = $download_left;
		}
		
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
	
	public function download()
	{		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$config = QZApp::getConfig();
		$model = $this->getModel();
		$download_id = $this->input->getInt('download_id');
		$passcode = $this->input->post->getAlnum('passcode');
		
		$download = $model->getDownload($download_id, $passcode);
		
		if(!$download)
		{
			$this->setMessage(JText::_('COM_QAZAP_ERROR_INVALID_DOWNLOAD'));
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=download&dowload_id='.$download_id.'&passcode='. $passcode, false));
			return;			
		}

		$file = JPath::clean($config->get('download_path') . DS . $download->name);
		
		if(is_file($file)) 
		{
			if(!$model->hitCount($download_id))
			{
				$this->setMessage($this->getError());
				$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=download&dowload_id=' . $download_id . '&passcode=' . $passcode, false));
				return;
			}
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			header('Content-Type: application/force-download');  
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			$obLevel = ob_get_level();
			if($obLevel)
			{
				while ($obLevel > 0 ) 
				{
					ob_end_clean();
					$obLevel --;
				}
			}
			else
			{
				ob_clean();
			}
			flush();
			readfile($file);
			$app->close();
		}
		
		$this->setMessage(JText::_('COM_QAZAP_ERROR_INVALID_DOWNLOAD_PATH'));
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=download&dowload_id='.$download_id.'&passcode='.$passcode, false));
	}


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.5
	 */
	public function getModel($name = 'Download', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
		
	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}

}