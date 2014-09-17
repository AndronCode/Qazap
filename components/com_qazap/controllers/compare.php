<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Compare list controller class.
 */
class QazapControllerCompare extends QazapController
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('display', 'view');		
	}	
	
	public function add()
	{
		$product_id = $this->input->post->get('product_id', 0, 'int');
		$product_name = $this->input->post->get('product_name', null, 'base64');
		$return_url = $this->getReturnPage();

		if(!$product_id)
		{
			$this->setError('Invalid product');
			return false;
		}

		$model = $this->getModel();

		if(!$product_ids = $model->add($product_id)) 
		{
			$this->setMessage($model->getError());
			$this->setRedirect(JRoute::_($return_url, false));
			return;	
		}	

		$config = QZApp::getConfig();

		if($config->get('compare_add_redirect', 0))
		{
			$this->setRedirect(JRoute::_($this->getURL($product_ids), false), JText::sprintf('COM_QAZAP_PRODUCT_ADDED_TO_COMPARISON', base64_decode($product_name)));
		}
		else
		{
			$this->setRedirect(JRoute::_($return_url, false), JText::sprintf('COM_QAZAP_PRODUCT_ADDED_TO_COMPARISON', base64_decode($product_name)));
		}

		return true;
	}
	

	public function view()
	{
		$this->setRedirect(JRoute::_($this->getURL(), false));
		return true;
	}	
	
	public function remove()
	{
		$product_id = $this->input->post->getInt('product_id', 0);
		$product_name = $this->input->post->get('product_name', null, 'base64');

		$model = $this->getModel();

		if(!$product_id)
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_PRODUCT'));
			return false;
		}

		if(!$model->remove($product_id)) 
		{
			$this->setMessage($model->getError());
			$this->setRedirect(JRoute::_($this->getURL(), false));
			return;	
		}

		$this->setMessage(JText::sprintf('COM_QAZAP_PRODUCT_REMOVED_FROM_COMPARE', base64_decode($product_name)), 'Success');
		$this->setRedirect(JRoute::_($this->getURL(), false));
		return;
	}
	
	public function removeAll()
	{
		$model = $this->getModel();

		if(!$model->removeAll()) 
		{
			$this->setMessage($model->getError());
			$this->setRedirect(JRoute::_($this->getURL(), false));
			return;	
		}

		$this->setMessage(JText::sprintf('COM_QAZAP_ALL_PRODUCT_REMOVED_FROM_COMPARE'), 'Success');
		$this->setRedirect(JRoute::_($this->getURL(), false));
		return;
	}
	
	protected function getURL()
	{
		$config = QZApp::getConfig();

		if($config->get('sef_compare_page', 1))
		{
			$model = $this->getModel();
			$product_ids = $model->getCompareSession();		
			$product_ids = array_filter((array) $product_ids);

			if(count($product_ids))
			{
				$url  = QazapHelperRoute::getCompareRoute($product_ids);
			}
			else
			{
				$url  = QazapHelperRoute::getCompareRoute();
			}						
		}
		else
		{
			$url  = QazapHelperRoute::getCompareRoute();
		}

		return $url;
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
		$return = $this->input->post->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
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
	public function getModel($name = 'compare', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}		
}