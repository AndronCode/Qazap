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

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_qazap/models/product.php';

/**
 * Content Component Qazap Model
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapModelForm extends QazapModelProduct
{
	/**
	* Model typeAlias string. Used for version history.
	*
	* @var        string
	*/
	public $typeAlias = 'com_qazap.product';

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
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);

		$this->setState('product.category_id', $app->input->getInt('category_id'));

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
		
		// Load User
		$user = QZUser::get();
		$this->setState('qzuser', $user);
		
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		// Get the pk of new Attribute and new Custom Field
		$format = $app->input->getCmd('format');
		$layout = $app->input->getCmd('layout');
		if($format == 'json' && ($layout == 'attribute' || $layout == 'field')) 
		{			
			$typeid = $app->input->getInt('typeid', 0);
			$this->setState('com_qazap.new.'.$layout.'.typeid', $typeid);
			$ordering = $app->input->getInt('ordering', 0);
			$this->setState('com_qazap.new.'.$layout.'.ordering', $ordering);
		}

		$this->setState('layout', $layout);		
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
		$form = $this->loadForm('com_qazap.product', 'product', array('control' => 'qzform', 'load_data' => $loadData));
		
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$user = QZUser::get();
		$form->setValue('vendor', null, $user->get('vendor_id', 0));
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
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('product.id');
				
		return parent::getItem($itemId);
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
