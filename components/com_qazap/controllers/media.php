<?php
/**
 * media.php
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
 * Vendors list controller class.
 */
class QazapControllerMedia extends QazapController
{	
	/**
	* Method to upload files
	* 
	*/		
	public function upload()
	{	
		// Check for request forgeries
		JSession::checkToken() or $this->returnError(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$qzmedia = $app->input->post->get('qzmedia', array(), 'array');

		if(!isset($qzmedia['param_name']))
		{
			$this->returnError('No field name found in form data');
		}	

		$param_name = $qzmedia['param_name'];
		$params = $qzmedia[$param_name];
		$params['param_name'] = $param_name;

		$app->setUserState('com_qazap.media.params', $params);

		$files = $app->input->files->get('qzmedia');

		if(!isset($files[$param_name]))
		{
			$this->returnError('File data index not found');
		}

		$model = $this->getModel();

		if(!$return = $model->upload($files[$param_name]))
		{
			$this->returnError($model->getError());
		}

		$this->returnSuccess($return);
	}		

	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or $this->returnError(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		
		$qzmedia = $app->input->post->get('qzmedia', array(), 'array');
		
		if(!isset($qzmedia['param_name']))
		{
			$this->returnError('No field name found in form data');
		}	
		
		$param_name = $qzmedia['param_name'];
		$params = $qzmedia[$param_name];
		$params['param_name'] = $param_name;
		
		$app->setUserState('com_qazap.media.params', $params);
			
		if(!isset($qzmedia['remove_file']))
		{
			$this->returnError('Remove file name not found');
		}
		$model = $this->getModel('Media', 'QazapModel');
		
		if(!$return = $model->remove($qzmedia['remove_file']))
		{
			$this->returnError($model->getError());
		}
		
		$this->returnSuccess($return);		
	}
  
	/**
	* Method return json encoded data
	* 
	* @param (array) $data Data to be returned
	* 
	*/
	protected function returnSuccess($context)
	{
		$model = $this->getModel();
		
		$this->setHead();
		
		if (isset($_SERVER['HTTP_CONTENT_RANGE'])) 
		{
			$files = isset($context[$model->options['param_name']]) ? $context[$model->options['param_name']] : null;
			if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) 
			{
				JResponse::setHeader('Range', '0-'.($this->fix_integer_overflow(intval($files[0]->size)) - 1), true);
			}
		}
			
		$this->setBody($context);		
    	JFactory::getApplication()->close();
	}



	/**
	* Method return json encoded error message
	* 
	* @param (string) $msg Error message
	* 
	*/	
	protected function returnError($msg = NULL)
	{
		$this->setHead();	
			
		$context = array('error'=>1, 'msg'=>$msg);
		
		$this->setBody($context);		
    	JFactory::getApplication()->close();				
	}
	
	
	protected function setHead()
	{
		JResponse::setHeader('Pragma', 'no-cache', true);
		JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
		JResponse::setHeader('Content-Disposition', 'inline; filename="medias.json"', true);
		// Prevent Internet Explorer from MIME-sniffing the content-type:
		JResponse::setHeader('X-Content-Type-Options', 'nosniff', true);		
	JResponse::setHeader('Vary', 'Accept-Encoding', true);
		
		if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) 
		{
			JFactory::getDocument()->setMimeEncoding('application/json');
		} 
		else 
		{
			JFactory::getDocument()->setMimeEncoding('text/plain');
		}				
	}
	
	protected function setBody($context)
	{
		echo json_encode($context);
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
	* @since   1.0.0
	*/
	public function getModel($name = 'mediafe', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}