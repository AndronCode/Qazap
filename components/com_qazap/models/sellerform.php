<?php
/**
 * sellerform.php
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

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_qazap/models/vendor.php';

/**
 * Content Component Qazap Model
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.5
 */
class QazapModelSellerForm extends QazapModelvendor
{
	/**
	* Model typeAlias string. Used for version history.
	*
	* @var        string
	*/
	public $typeAlias = 'com_qazap.vendor';

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @return  void
	*
	* @since   1.0.0
	*/
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		//$pk = $app->input->getInt('product_id');
		$user = QZUser::get();
		$vendor_id = $user->get('vendor_id', 0);
		$this->setState('vendor.id', $vendor_id);

		$this->setState('product.category_id', $app->input->getInt('category_id'));

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->getString('layout'));
	}


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable($type = 'Vendor', $prefix = 'QazapTable', $config = array())
	{
		// Include admin tables path
		JTable::addIncludePath(QZPATH_TABLE_ADMIN);
		return JTable::getInstance($type, $prefix, $config);
	}	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'forms');
		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields');		
		// Get the form.
		$form = $this->loadForm('com_qazap.sellerform', 'sellerform', array('control' => 'jform', 'load_data' => $loadData));
			
		if (empty($form)) 
		{
			return false;
		}
		
		$config = QZApp::getConfig();
		$user = JFactory::getUser();
		//$form->setFieldAttribute('vendor_admin', 'type', 'hidden', null);
		//$form->setFieldAttribute('shop_name', 'class', '', null);

		$form->setValue('vendor_admin', null, $user->get('id'));
		
/*		if($config->get('vendor_admin_approval'))
		{
			$form->setValue('state', null, 0);		
		}
		else
		{
			$form->setValue('state', null, 1);
		}	*/	
		
		return $form;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$params = $app->getParams();
		
		if($params->get('display_group')!= 1)
		{
			$form->setFieldAttribute('vendor_group_id', 'required', 'false', null);
		}
		if($params->get('display_categories')!= 1)
		{
			$form->setFieldAttribute('category_list', 'required', 'false', null);
		}
		if($params->get('display_shipments')!= 1)
		{
			$form->setFieldAttribute('shipment_methods', 'required', 'false', null);
		}
		$form->setFieldAttribute('vendor_group_id', 'default', $params->get('default_vendor_group') , null);
			
				
		parent::preprocessForm($form, $data, $group);
	}	
	/**
	 * Method to get article data.
	 *
	 * @param   integer  $itemId  The id of the article.
	 *
	 * @return  mixed  Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		
		if($user->guest)
		{
			$return = JRoute::_('index.php?option=com_qazap&view=sellerform&layout=edit');
			$app->enqueueMessage('');
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($return)));
			return false;
		}
		
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('vendor.id');

		return parent::getItem($itemId);
	}
	
	public function getGroup()
	{
		if(!$vendor = $this->getItem())
		{
			$this->setError($this->getError());
			return false;
		}
		
		$group_id = !empty($vendor->vendor_group_id) ? $vendor->vendor_group_id : null; 
		
		$table = $this->getTable('vendor_group');
		
		if($group_id > 0)
		{
			$return = $table->load($group_id);
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}			
		}
		
		return $table;		
	}
	/**
	 * Get the return URL.
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.0.0
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	* Method to save the form data.
	*
	* @param   array  $data  The form data.
	*
	* @return  boolean  True on success.
	*
	* @since   1.0.0
	*/
	public function save($data)
	{

		return parent::save($data);
	}
}
