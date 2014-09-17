<?php
/**
 * download.php
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
	
	public function getDownload($doReturn = false)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$user = JFactory::getUser();
		$isAdmin = $user->get('isRoot');
		$date = JFactory::getDate();
		$config = QZApp::getConfig();
		$model = $this->getModel();
		$form = $this->input->post->get('qzform', array(), 'array');
		
		if(!empty($form))
		{
			$download_id = (int) isset($form['download_id']) ? $form['download_id'] : 0;
			$passcode = (string) isset($form['passcode']) ? $form['passcode'] : '';
		}
		else
		{
			$download_id = $this->input->getInt('download_id', 0);
			$passcode = $this->input->post->getAlnum('passcode', '');			
		}
		
		if(empty($download_id))
		{
			$app->enqueueMessage(JText::_('COM_QAZAP_ERROR_EMPTY_DOWNLOAD_ID'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute(), false));
			return false;				
		}
		
		if(empty($passcode))
		{
			$app->enqueueMessage(JText::_('COM_QAZAP_ERROR_EMPTY_DOWNLOAD_PASSCODE'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute(), false));
			return false;				
		}		
		
		$download = $model->getDownload($download_id, $passcode);
		
		if(!$download)
		{
			$app->enqueueMessage(JText::_('COM_QAZAP_ERROR_INVALID_DOWNLOAD'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute(), false));
			return false;			
		}
		
		if($download->user_id > 0)
		{
			if($user->guest)
			{
				$return = JRoute::_('index.php?option=com_qazap&view=download&download_id='.$download_id.'&passcode='. $passcode, false);
				$app->enqueueMessage(JText::_('COM_QAZAP_DOWNLOAD_NEED_LOGIN_MSG'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return='. base64_encode($return), false));
				return false;				
			}
			elseif($download->user_id != $user->get('id') && !$isAdmin)
			{
				$app->enqueueMessage(JText::_('COM_QAZAP_DOWNLOAD_DO_NOT_HAVE_ACCESS'));
				$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute(), false));
				return false;
			}			
		}
		
		$validity = (int) $config->get('download_validity', 0);
		$expiry_date = JFactory::getDate(($download->download_start_date . '+ ' . $validity . ' days'), 'UTC');
		
		if($validity && $date > $expiry_date)
		{
			$app->enqueueMessage(JText::_('COM_QAZAP_DOWNLOAD_HAS_EXPIRED'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute($download_id, $passcode), false));
			return false;
		}
		
		$limit = (int) $config->get('download_limit', 0);
		
		if($limit && $download->download_count >= $limit)
		{
			$app->enqueueMessage(JText::_('COM_QAZAP_DOWNLOAD_LIMIT_HAS_REACHED'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute($download_id, $passcode), false));
			return false;			
		}
		
		if($doReturn)
		{
			return $download;
		}
		
		$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute($download_id, $passcode), false));		
	}
	
	public function download()
	{		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$user = JFactory::getUser();
		$isAdmin = $user->get('isRoot');
		$config = QZApp::getConfig();
		$download_id = $this->input->getInt('download_id');
		$passcode = $this->input->post->getAlnum('passcode');		
		
		if(!$download = $this->getDownload(true))
		{
			return;
		}

		$file = JPath::clean($config->get('download_path') . DS . $download->name);
		$model = $this->getModel();
		
		if(is_file($file)) 
		{
			if(!$isAdmin && !$model->hitCount($download_id))
			{
				$this->setMessage($model->getError());
				$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute($download_id, $passcode), false));
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
		
		$app->enqueueMessage(JText::_('COM_QAZAP_ERROR_INVALID_DOWNLOAD_PATH'));
		$this->setRedirect(JRoute::_(QazapHelperRoute::getDownloadRoute($download_id, $passcode), false));
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

}