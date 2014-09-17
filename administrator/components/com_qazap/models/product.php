<?php
/**
 * product.php
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

jimport('joomla.application.component.modeladmin');

/**
 * Qazap Product model.
 */
class QazapModelProduct extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */	
	protected $text_prefix = 'COM_QAZAP';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_qazap.product';
	
	/**
	* @var		string	Primary key name for product details database table
	* @since	1.0
	*/
	protected $detailsPKname = 'product_details_id';
	
	/**
	* @var		string	Database table name product details
	* @since	1.0
	*/	
	protected $details_table = '#__qazap_product_details';
	
	/**
	* @var		string	Main primary key of product
	* @since	1.0
	*/	
	protected $mainPKname = 'product_id';
	
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelAdmin
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$this->config = QZApp::getConfig();
		parent::__construct($config);
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
	public function getTable($type = 'Product', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
		
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
		// Get the form.
		$form = $this->loadForm('com_qazap.product', 'product', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) 
		{
			return false;
		}
		
		return $form;
	}
	
	/**
	 * Method to get language specific dynamic name, alias, short description and descritpion form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */	
	protected function getProductDetailsForm()
	{
		$columns = array('product_name'=>'text', 'product_alias'=>'text', 'short_description'=>'textarea', 'product_description'=>'editor', 'language'=>'hidden');
		$app = JFactory::getApplication();
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		$form = new SimpleXMLElement('<form />');	
		
		foreach($columns as $name => $type)
		{
			$fields = $form->addChild('fields');
			$fields->addAttribute('name', $name);
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', $name.'_set');
			$fieldset->addAttribute('label', 'COM_QAZAP_FORM_LBL_'.strtoupper($name));
				
			foreach($languages as $tag => $language)
			{				
				$field = $fieldset->addChild('field');
				$field->addAttribute('name', $tag);
				if($name == 'product_name')
				{
					if($app->isAdmin())
					{
						$field->addAttribute('class', 'inputbox input-xxlarge input-large-text');
					}
					else
					{
						$field->addAttribute('class', 'span12');
					}
					$field->addAttribute('required', 'required');					
					$field->addAttribute('size', '40');
				}
				elseif($name == 'product_alias')
				{
					$field->addAttribute('hint', 'COM_QAZAP_FORM_ALIAS_PLACEHOLDER');
					$field->addAttribute('size', '40');
				}
				elseif($name == 'short_description')
				{
					$field->addAttribute('class', 'span12');
					$field->addAttribute('filter', 'JComponentHelper::filterText');
				}
				elseif($name == 'product_description')
				{
					$field->addAttribute('filter', 'JComponentHelper::filterText');
				}
				$field->addAttribute('type', $type);
				$field->addAttribute('language', $tag);
				$field->addAttribute('language_name', $language->title);
				$field->addAttribute('label', 'COM_QAZAP_FORM_LBL_'.strtoupper($name));
				$field->addAttribute('description', 'COM_QAZAP_FORM_DESC_'.strtoupper($name));				
			}
		}
		return $form;
	}

	/**
	* Method to get language specific meta name, meta description and meta keyword form.
	*
	* @param	array	$data		An optional array of data for the form to interogate.
	* @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	* @return	JForm	A JForm object on success, false on failure
	* @since	1.0.0
	*/	
	protected function getProductMetaForm()
	{
		$columns = array('page_title'=>'text', 'metakey'=>'textarea', 'metadesc'=>'textarea', 'robots'=>'text', 'author'=>'text', 'rights'=>'textarea', 'xreference'=>'text');
		$app = JFactory::getApplication();
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		$form = new SimpleXMLElement('<form />');
		
		foreach($columns as $name => $type)
		{
			$fields = $form->addChild('fields');
			$fields->addAttribute('name', $name);
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', $name.'_set');
			$fieldset->addAttribute('label', 'COM_QAZAP_FORM_LBL_'.strtoupper($name));
					
			foreach($languages as $tag => $language)
			{				
				$field = $fieldset->addChild('field');
				$field->addAttribute('name', $tag);
				$field->addAttribute('type', $type);
				$field->addAttribute('language', $tag);
				$field->addAttribute('language_name', $language->title);
				$field->addAttribute('label', 'COM_QAZAP_FORM_LBL_'.strtoupper($name));
				$field->addAttribute('description', 'COM_QAZAP_FORM_DESC_'.strtoupper($name));
				$field->addAttribute('class', $language->title);
			}
		}
		return $form;
	}

	/**
	* Method to get the data that should be injected in the form.
	*
	* @return	mixed	The data for the form.
	* @since	1.0.0
	*/
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.product.data', array());
		
		$detailsFields = array('product_name', 'product_alias', 'short_description', 'product_description', 'language', 'page_title', 'metakey', 'metadesc', 'robots', 'author', 'rights', 'xreference');
		foreach($data as $k=>&$v)
		{
			if(in_array($k, $detailsFields))
			{
				$v = (object) $v;
			}
		}
		
		if (empty($data)) 
		{
			$data = $this->getItem();			
			// Support for 'multiple' field
			$data->related_categories = isset($data->related_categories) ? json_decode($data->related_categories) : '';
			$data->related_products = isset($data->related_products) ? json_decode($data->related_products) : '';
			$data->membership = isset($data->membership) ? json_decode($data->membership) : '';
		}
		
		$this->preprocessData('com_qazap.product', $data);
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{			
			// Convert the created and modified dates to local user time for display in the form.
			$tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if ((int) $item->created_time)
			{
				$date = new JDate($item->created_time);
				$date->setTimezone($tz);
				$item->created_time = $date->toSql(true);
			}
			else
			{
				$item->created_time = null;
			}

			if ((int) $item->modified_time)
			{
				$date = new JDate($item->modified_time);
				$date->setTimezone($tz);
				$item->modified_time = $date->toSql(true);
			}
			else
			{
				$item->modified_time = null;
			}		
				
			if(!empty($item->product_id))
			{
				if(!$this->loadDetails($item->product_id, $item))
				{
					$this->setError($this->getError());
					return false;
				}
				if(!$this->LoadUsergroupPrices($item->product_id, $item))
				{
					$this->setError($this->getError());
					return false;					
				}
				if(!$this->LoadQuantityPrices($item->product_id, $item))
				{
					$this->setError($this->getError());
					return false;					
				}
				if($this->config->get('downloadable'))
				{
					$fileModel = QZApp::getModel('File', array('ignore_request'=>true));
					$downloadable = $fileModel->getFileByProduct($item->product_id);
					
					if($downloadable === false && $fileModel->getError())
					{
						$this->setError($fileModel->getError());
						return false;
					}
					
					if(!empty($downloadable))
					{
						$item->downloadable_file = $downloadable->name;	
					}									
				}
						
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->product_id, 'com_qazap.product');						
			}
		}
				
		return $item;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$productDetailsForm = $this->getProductDetailsForm();
		$form->load($productDetailsForm, false);
		
		$productMetaForm = $this->getProductMetaForm();
		$form->load($productMetaForm, false);
		
		if($this->config->get('downloadable'))
		{
			$download_path = $this->config->get('download_path');
			if(!is_dir($download_path))
			{
				JError::raiseWarning(1, 'COM_QAZAP_ERROR_INVALID_DOWNLOAD_PATH');
			}
			else
			{
				$form->setFieldAttribute('downloadable_file', 'directory', $download_path, null);
				$form->setFieldAttribute('downloadable_file', 'required', 'true', null);
			}			
		}
		else
		{
			$form->removeField('downloadable_file', null);
		}
		
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_product');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	/**
	* 
	* Get custom Fields for a product
	*  
	*/
	public function getCustomField() 
	{
		$app = JFactory::getApplication();	
		$typeid = $this->getState('com_qazap.new.field.typeid', 0);
		$ordering = $this->getState('com_qazap.new.field.ordering', 0);
		
		if(!$typeid) 
		{
			$return =  array('error'=>1, 'html'=>JText::_('COM_QAZAP_PRODUCT_INVALID_CUSTOMFIELD_ID'));
			return json_encode($return);
		}	
			
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.id AS typeid', 'a.type AS plugin_id','a.title', 'a.show_title', 'a.description', 'a.tooltip', 'a.layout_position', 'a.hidden', 'a.params',  'e.element'));
		$query->from('`#__qazap_customfieldtype` as a');
		$query->join('INNER', '#__extensions as e ON e.extension_id = a.type');
		$query->where('a.id = ' . (int) $typeid); 
		
		try 
		{
			$db->setQuery($query);
			$data = $db->loadObject();			
		} 
		catch (Exception $e) 
		{
			$return =  array('error'=>1, 'html'=>$e->getMessage());
			return json_encode($return);			
		}

		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcustomfields');	
			
		$data->id = 0;
		$data->value = '';
		$data->ordering = $ordering;
		$html = '';
		$result = $dispatcher->trigger('onDisplayProductAdmin', array($data, &$html));
		
		$return =  array('error'=>0, 'title'=>$data->title, 'html'=>$html);
		
		return json_encode($return);
	}
	
	/**
	* Method to display saved Cart Attributes of the product
	* 
	* @return html content
	*/	
	public function getSavedCustomFields()
	{
		$product_id = $this->getState($this->getName() . '.id', 0);
		$datas = JFactory::getApplication()->getUserState('com_qazap.edit.product.'.$product_id.'.fields.data', array());

		if(empty($datas))
		{	
			$db = $this->getDbo();		
			$query = $db->getQuery(true)
					->select(array('a.*','b.type AS plugin_id','b.title', 'e.element', 'e.folder'))
					->from('`#__qazap_customfield` as a')
					->join('INNER', '`#__qazap_customfieldtype` as b ON b.id = a.typeid')
					->join('INNER', '#__extensions as e ON e.extension_id = b.type')	   
					->where('a.product_id = ' . (int) $product_id)
					->order('a.ordering ASC');
			try 
			{
				$db->setQuery($query);
				$datas = $db->loadAssocList();			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}			
		}

		if(empty($datas))
		{
			return null;
		}		
		
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcustomfields');
		$return = array();			
		foreach($datas as $data) 
		{
			// Convert Array to Object
			if(is_array($data) && !is_object($data))
			{
				$tmp = $data;
				$data = new stdClass;
				foreach($tmp as $k=>$v)
				{
					$data->$k = $v;
				}
				unset($tmp);
			}
			$html = '';			
			$result = $dispatcher->trigger('onDisplayProductAdmin', array($data, &$html));
			$return[] = array('title'=>$data->title, 'html'=>$html);	
		}
		
		return $return;		
	}	
	
	/**
	* Method to get all attributes of the product
	* 
	* @return mixed (string/boolean)	JSON encodes HTML string for display or false if not found
	*/	
	public function getAttribute() 
	{
		$app = JFactory::getApplication();	
		$typeid = $this->getState('com_qazap.new.attribute.typeid', 0);
		$ordering = $this->getState('com_qazap.new.attribute.ordering', 0);
		
		if(!$typeid) 
		{
			$return =  array('error'=>1, 'html'=>JText::_('COM_QAZAP_PRODUCT_INVALID_CARTATTRIBUTE_ID'));
			return json_encode($return);
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.id AS typeid', 'a.type AS plugin_id','a.title', 'a.show_title', 'a.description', 'a.tooltip', 'a.hidden', 'a.check_stock', 'a.params',  'e.element'));
		$query->from('`#__qazap_cartattributestype` as a');
		$query->join('INNER', '#__extensions as e ON e.extension_id = a.type');
		$query->where('a.id = '. (int) $typeid); 
		
		try 
		{
			$db->setQuery($query);
			$data = $db->loadObject();			
		} 
		catch (Exception $e) 
		{
			$return =  array('error'=>1, 'html'=>$e->getMessage());
			return json_encode($return);			
		}

		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcartattributes');	
			
		$data->id = 0;
		$data->value = '';
		$data->price = 0.0000000000;
		$data->stock = 0;
		$data->ordered = 0;
		$data->booked_order = 0;
		$data->ordering = $ordering;
		$header = array();
		$html = array();
		$result = $dispatcher->trigger('onDisplayProductAdmin', array($data, &$header, &$html));
		
		$colCount = count($header);
		
		$title  = '<tr class="info">';
		$title .=	'<td colspan="'.($colCount - 1).'"><span class="attribute-name">'.$data->title.'</span></td>';
		$title .=	'<td><span class="field-sortable-handler"><i class="icon-menu"></i></span></td>';
		$title .= '</tr>';
		
		$hHTML  = '<tr>';
		foreach($header as $name) 
		{
			$hHTML .=	'<td>'.JText::_($name).'</td>';
		}
		$hHTML .= '</tr>';
		
		$cHTML  = '<tr class="html-row">';
		foreach($html as $content) 
		{
			$cHTML .=	'<td>'.$content.'</td>';
		}
		$cHTML .= '</tr>';
		
		$return =  array('error'=>0, 'title'=>$title, 'header'=>$hHTML, 'html'=>$cHTML);
		
		return json_encode($return);
	}
	
	/**
	* Method to display saved Cart Attributes of the product
	* 
	* @return html content
	*/	
	public function getSavedAttributes()
	{
		$product_id = $this->getState($this->getName() . '.id', 0);
		$datas = JFactory::getApplication()->getUserState('com_qazap.edit.product.'.$product_id.'.qzattribute.data', array());

		if(empty($datas))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
					->select(array('a.*','b.type AS plugin_id','b.title', 'e.element', 'e.folder'))
					->from('`#__qazap_cartattributes` as a')
					->join('INNER', '`#__qazap_cartattributestype` as b ON b.id = a.typeid')
					->join('INNER', '#__extensions as e ON e.extension_id = b.type')	   
					->where('a.product_id = ' . (int) $product_id)
					->order('a.ordering ASC');
			try 
			{
				$db->setQuery($query);
				$datas = $db->loadAssocList();			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}			
		}

		if(empty($datas))
		{
			return null;
		}		
		
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcartattributes');
		$return = array();			
		foreach($datas as $data) 
		{
			$header = array();
			$content = array();
			// Convert Array to Object
			if(is_array($data) && !is_object($data))
			{
				$tmp = $data;
				$data = new stdClass;
				foreach($tmp as $k=>$v)
				{
					$data->$k = $v;
				}
				unset($tmp);
			}			
			$result = $dispatcher->trigger('onDisplayProductAdmin', array($data, &$header, &$content));
			$typeID = $data->typeid;
			if(array_key_exists($data->typeid, $return))
			{
				$return[$data->typeid]->rows[] = $content;
			}
			else
			{
				$return[$data->typeid] = new stdClass;
				$return[$data->typeid]->title = $data->title;
				$return[$data->typeid]->columns = count($header);
				$return[$data->typeid]->header = $header;
				$return[$data->typeid]->rows = array();
				$return[$data->typeid]->rows[] = $content;
			}			
		}
		
		return $return;		
	}
	
	
	/**
	* Method to save a product with all additional data
	* 
	* @param	array	$data Form post data in array format
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function save($data)	
	{	
		if($this->config->get('downloadable') && (!isset($data['downloadable_file']) || $data['downloadable_file'] == -1))
		{
			$this->setError(JText::_('COM_QAZAP_PRODUCT_ERROR_INVALID_DOWNLOADABLE_FILE'));
			return false;
		}
		
		$app = JFactory::getApplication();
		$attribute_data = $app->input->post->get('qzattribute', array(), 'array');		
		$customfield_data = $app->input->post->get('qzfield', array(), 'array');
		$product_id = $this->getState($this->getName() . '.id', 0);
		$app->setUserState('com_qazap.edit.product.'.$product_id.'.qzattribute.data', $attribute_data);
		$app->setUserState('com_qazap.edit.product.'.$product_id.'.qzfield.data', $customfield_data);
		$save2copy = false;
		
		if(!$this->checkData($data))
		{
			$this->setError($this->getError());
			return false;
		}		
		
		$mediaModel = $this->getInstance('Media', 'QazapModel');
		
		if(isset($data['images']) && !empty($data['images']))
		{
			foreach($data['images'] as &$image)
			{
				if($image['filetype'] == 'MANUAL')
				{
					$result = $mediaModel->getFileInfo($image['url']);
					$image['filetype'] = $result['filetype'];
					$image['type'] = $result['type'];
				}
			}
		}
		
		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			$save2copy = true;
					
			foreach($languages as $tag => $language)
			{
				$result = $this->generateNewTitleAlias($data['category_id'], $tag, $data['product_alias'][$tag], $data['product_name'][$tag]);
				if($result === false)
				{
					$this->setError($this->getError());
					return false;
				}
				
				list($title, $alias) = $result;
				$data['product_name'][$tag] = $title;
				$data['product_alias'][$tag] = $alias;
				$data['language'][$tag] = '';
				$data['state'] = 0;	
			}
		}
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');
		
		$table = $this->getTable();

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;		
				
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}				
			
			if($isNew && $app->isSite())
			{
				if($this->config->get('product_approval', 1) == 1)
				{
					$data['block'] = 1;
				}
				
				if(!isset($data['vendor']) || empty($data['vendor']))
				{
					$data['vendor'] = QZUser::get()->get('vendor_id', 0);
				}				
			}

			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('product', &$data, $isNew));
			
			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}

			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			$this->prepareTable($table);

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}		
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			if(!$this->saveDetails($data, $table->product_id))
			{
				$this->setError($this->getError());
				return false;				
			}

			if($data['multiple_pricing'] == 1 && !$this->saveUsergroupPrices($data, $table->product_id))
			{
				$this->setError($this->getError());
				return false;
			}
			
			if($data['multiple_pricing'] == 2 && !$this->saveQuantityPrices($data, $table->product_id))
			{
				$this->setError($this->getError());
				return false;
			}			
			
			// Save Custom Fields
			$tableColumns = array('id', 'typeid', 'value', 'product_id', 'ordering');		
			if(!empty($customfield_data) && !$this->saveExtras($customfield_data, '#__qazap_customfield', $tableColumns, $table->product_id, 'qzfield', $save2copy))
			{
				$this->setError($this->getError());
				return false;				
			}	
			
			// Save Cart Attributes
			$tableColumns = array('id', 'typeid', 'value', 'price', 'stock', 'ordered', 'booked_order', 'product_id', 'ordering');
			if(!empty($attribute_data) && !$this->saveExtras($attribute_data, '#__qazap_cartattributes', $tableColumns, $table->product_id, 'qzattribute', $save2copy))
			{
				$this->setError($this->getError());
				return false;				
			}
			
			if($this->config->get('downloadable'))
			{
				$fileModel = QZApp::getModel('File', array('ignore_request'=>true));
				
				if(!$fileModel->saveProduct($data))
				{
					$this->setError($fileModel->getError());
					return false;
				}
			}			
			
			$this->cleanCache();
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('product', $data, $isNew));		
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) 
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		
		$this->setState($this->getName() . '.new', $isNew);
		
		// Call notify model function. Product available notification email will
		// sent by Notify model using its notify method if required
		if(!$isNew)
		{
			$notifyModel = QZApp::getModel('Notify', array('ignore_request'=>true));
			if(!$notifyModel->notify($this->getState($this->getName() . '.id')))
			{
				$this->setError($notifyModel->getError());
				return false;
			}		
		}

		return true;
	}
	
	/**
	* Method to save Custom Fields and Attributes of a product
	* 
	* @param    array       $data               Form submission post array
	* @param    string      $tableName          Name of the database table
	* @param    array       $tableColumns       Name of the table columns to be saved
	* @param    integer     $product_id         ID of the product
	* @param    string      $formName           Name of the specific part of the form
	* @param    string      $primaryKey         Name of the primary key column
	* 
	* @return	boolean
	* @since	1.0
	*/	
	protected function saveExtras($data, $tableName, $tableColumns, $product_id, $formName, $save2copy = false, $primaryKey = 'id')
	{
		$insert = false;
		$update = false;
		$delete = false;
		$insertData = array();
		$updateData = array();
		$deleteIds = array();
		$db = $this->getDbo();
		
		foreach($data as $field) 
		{	
			if(isset($field['deleteID']))
			{
				if(!$save2copy && $field['deleteID'])
				{
					$deleteIds[] = 	$field['deleteID'];
					$delete = true;							
				}		
			}			
			elseif(isset($field[$primaryKey]) && ($save2copy || ($field[$primaryKey] == 0))) 
			{			
				$field[$primaryKey] = 0;
				$insert = true;
				$tmp = array();
				$tmp[$primaryKey] = 0;
				
				foreach($tableColumns as $column)
				{
					if($column == $primaryKey)
					{
						$tmp[$column] = 0;
					}
					elseif($column == $this->mainPKname)
					{
						$tmp[$column] = $product_id;
					}
					else 
					{
						$tmp[$column] = isset($field[$column]) ? $field[$column] : '';
					}					
				}				
				$insertData[] = implode(',', $db->quote($tmp));
				unset($tmp);
			} 
			elseif(isset($field[$primaryKey]) && $field[$primaryKey] > 0) 
			{
				$update = true;
				$id = $field[$primaryKey];
				foreach($tableColumns as $column)
				{
					if($column == $primaryKey)
					{
						continue;
					}					
					if(!isset($updateData[$column]))
					{
						$updateData[$column] = array();
					}
					if($column == $this->mainPKname)
					{
						$updateData[$column][$id] = $product_id;
					}
					else
					{
						$updateData[$column][$id] = isset($field[$column]) ? $field[$column] : '';
					}					
				}					
			}
		}	
		
		if(!empty($update))
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName($tableName));
			$ids = array();
			foreach($updateData as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $id => $value) 
				{
					if(!in_array($id, $ids)){
						$ids[] = $id;
					}
					$when .= sprintf('WHEN %d THEN %s ', $id, $db->quote($value));
				}
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName($primaryKey).' '.$when.' END');
			}
			$query->where($db->quoteName($primaryKey).' IN ('.implode(',', $ids).')');
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}	

		if(!empty($insert)) 
		{
			$query = $db->getQuery(true);
			$query->insert($db->quoteName($tableName));
			$query->columns($db->quoteName($tableColumns));	
			$query->values(implode('),(', $insertData));
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}							
		}
		
		if(!empty($delete))
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName($tableName));
			$query->where('id IN (' . implode(',', $deleteIds) . ')');
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}	
		}
		
		$product_id = $this->getState($this->getName() . '.id', 0);
		JFactory::getApplication()->setUserState('com_qazap.edit.product.'.$product_id.'.'.$formName.'.data', array());
		
		return true;		
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{	
		$ids = $pks;
		
		if($return = parent::delete($pks)) 
		{
			if(!$this->deleteDetails($ids))
			{
				$this->setError($this->getError());
				return false;				
			}
			
			if(!$this->deleteAttributes($ids))
			{
				$this->setError($this->getError());
				return false;				
			}
			
			if(!$this->deleteCustomfields($ids))
			{
				$this->setError($this->getError());
				return false;				
			}
			
			if(!$this->deleteUsergroupPrices($ids))
			{
				$this->setError($this->getError());
				return false;				
			}	
			
			if(!$this->deleteQuantityPrices($ids))
			{
				$this->setError($this->getError());
				return false;				
			}												
		}
		
		return $return;
	}
	
	/**
	* Delete Product Details
	* 
	* @pks array of product ids
	* 
	* @return boolean (true/false)
	*/
	protected function deleteDetails($pks)
	{	
		$pks = (array) $pks;
		$db = $this->getDbo();
		$sql = $db->getQuery(true);
		$sql->delete($db->quoteName('#__qazap_product_details'));
		$sql->where($db->quoteName($this->mainPKname).' IN ('.implode(',', $pks).')');	
					
		try 
		{
			$db->setQuery($sql);
			$db->execute();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}	
	
	/**
	* Delete Cart Attributes
	* 
	* @pks array of product ids
	* 
	* @return boolean (true/false)
	*/
	protected function deleteAttributes($pks)
	{	
		$pks = (array) $pks;
		$db = $this->getDbo();
		$sql = $db->getQuery(true);
		$sql->delete($db->quoteName('#__qazap_cartattributes'));
		$sql->where($db->quoteName($this->mainPKname).' IN ('.implode(',', $pks).')');
						
		try 
		{
			$db->setQuery($sql);
			$db->execute();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}		
	
	/**
	* Delete Custom Fields
	* 
	* @pks array of product ids
	* 
	* @return boolean (true/false)
	*/
	protected function deleteCustomfields($pks)
	{	
		$pks = (array) $pks;
		$db = $this->getDbo();
		$sql = $db->getQuery(true);
		$sql->delete($db->quoteName('#__qazap_customfield'));
		$sql->where($db->quoteName($this->mainPKname).' IN ('.implode(',', $pks).')');				
		try 
		{
			$db->setQuery($sql);
			$db->execute();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	/**
	* Delete Usergroup Prices
	* 
	* @pks array of product ids
	* 
	* @return boolean (true/false)
	*/
	protected function deleteUsergroupPrices($pks)
	{	
		$pks = (array) $pks;
		$db = $this->getDbo();
		$sql = $db->getQuery(true);
		$sql->delete($db->quoteName('#__qazap_product_user_price'));
		$sql->where($db->quoteName($this->mainPKname).' IN ('.implode(',', $pks).')');				
		try 
		{
			$db->setQuery($sql);
			$db->execute();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}	
	
	/**
	* Delete Usergroup Prices
	* 
	* @pks array of product ids
	* 
	* @return boolean (true/false)
	*/
	protected function deleteQuantityPrices($pks)
	{	
		$pks = (array) $pks;
		$db = $this->getDbo();
		$sql = $db->getQuery(true);
		$sql->delete($db->quoteName('#__qazap_product_quantity_price'));
		$sql->where($db->quoteName($this->mainPKname).' IN ('.implode(',', $pks).')');				
		try 
		{
			$db->setQuery($sql);
			$db->query();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}				
	
	/**
	 * Batch tag a list of item.
	 *
	 * @param   integer  $value     The value of the new tag.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  void.
	 *
	 * @since   3.1
	 */
	protected function batchTag($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$tags = array($value);

				/**
				 * @var  JTableObserverTags  $tagsObserver
				 */
				$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
				$result = $tagsObserver->setNewTags($tags, false);
				if (!$result)
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}	
	
	/**
	* Activate or block products
	* 
	* @param primary key or Array of primary keys. $pks
	* @param active = 0, block = 1. $value
	* 
	* @return boolean (true/false)
	*/	
	public function activate(&$pks, $value = 1)
	{
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->activate($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}


	/**
	* Featured or unfeatured products
	* 
	* @param primary key or Array of primary keys. $pks
	* @param active = 1, block = 0. $value
	* 
	* @return boolean (true/false)
	*/	
	public function featured(&$pks, $value = 1)
	{
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->featured($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());
			return false;
		}

		$context = $this->option . '.' . $this->name;

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());
			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	* Load all details for a product.
	* 
	* @param product_id Product id (int)
	* @param item Already loaded product data (JObject)
	* 
	* @return	boolean
	* @since	1.0
	*/
	protected function loadDetails($product_id = 0, &$item)
	{		
		if(!$product_id || !is_object($item))
		{
			return false;
		}
		$tags = JLanguageHelper::getLanguages('lang_code');
		$tags = array_keys($tags);
		// Initialise the query.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName($this->details_table));
		$query->where($db->quoteName($this->mainPKname) . ' = ' . $db->quote($product_id));
		$query->where($db->quoteName('language') . ' IN (' . implode(',', $db->quote($tags)) . ')');
		
		try 
		{
			$db->setQuery($query);
			$rows = $db->loadAssocList('language');
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		// Check that we have a result.
		if (empty($rows))
		{
			return true;
		}
		else
		{
			foreach($rows as $tag=>$row) 
			{
				$ignore = array($this->mainPKname, 'language');
				foreach($row as $k => $v)
				{	
					if($k == $this->detailsPKname)
					{
						if(!isset($item->language))
						{
							$item->language = new stdClass;
						}
						$item->language->$tag = $v;
					}
					elseif($k == 'metadata')
					{
						$metadata = json_decode($v);
						if(!is_array($metadata) && empty($metadata)) continue;
						foreach($metadata as $mk => $mv)
						{
							if(!isset($item->$mk))
							{
								$item->$mk = new stdClass;
							}
							$item->$mk->$tag = $mv;
						}
					}
					elseif(!in_array($k, $ignore))
					{
						if(!isset($item->$k))
						{
							$item->$k = new stdClass;
						}
						$item->$k->$tag = $v;;
					}			
				}				
			}
			
			return true;			
		}		
	}

	/*
	* Bind and save Product Details
	* 
	* @data For submission data array.
	* @product_id - Product ID (integar)
	*/
	protected function saveDetails($data, $product_id = 0)
	{
		$product_id = (int) $product_id;
		
		if(empty($data) || empty($product_id))
		{
			$this->setError('Invalid data saveDetails().');
			return false;
		}
		
		// Unset language data if exists
		//if(isset($data['language'])) unset($data['language']);
		$languages = JLanguageHelper::getLanguages('lang_code');
		// Process Details Fields
		$metaDataFields = array('page_title', 'author', 'robots');
		$data['metadata'] = array();
		
		foreach($metaDataFields as $metaDataField)
		{
			if(isset($data[$metaDataField])) 
			{
				foreach($languages as $tag => $language)
				{
					$data['metadata'][$tag][$metaDataField] = $data[$metaDataField][$tag];
				}				
			}
		}
		
		$fields = array($this->mainPKname, 'product_name', 'product_alias', 'short_description', 'product_description', 'metadesc', 'metakey', 'metadata');
		
		$update = false;
		$updateCase = array();
		$insert = false;
		$insertColumns = array('language');
		$insertData = array();
		$db = $this->getDbo();
		
		foreach ($languages as $tag => $language)
		{	
			// Check and prepare data for Update and Insert		
			if(isset($data['language'][$tag]) && $data['language'][$tag] > 0) 
			{				
				$id = $data['language'][$tag];
				$updateCase['language'][$id] = $tag;
				foreach($data as $name => $value)
				{
					if(!in_array($name, $fields)) {continue;}
					if(!isset($updateCase[$name])) 
					{
						$updateCase[$name] = array();
					}
					if($name == $this->mainPKname) {
						$updateCase[$name][$id] = $value;
					} else {
						$updateCase[$name][$id] = $value[$tag];
					}					
				}
				$update = true;
			}
			else
			{
				$tmp = array();				
				$tmp['language'] = $tag;				
				foreach($data as $name => $value)
				{
					if(!in_array($name, $fields)) {continue;}
					if(!in_array($name, $insertColumns))
					{
						$insertColumns[] = $name;
					}
					if($name == $this->mainPKname) {
						$tmp[$name] = $product_id;
					} else {					
						$tmp[$name] = $value[$tag];
					}
				}
				$tmp['metadata'] = json_encode($tmp['metadata']);
				$insert = true;
				$insertData[] = implode(',', $db->Quote($tmp));
				unset($tmp);				
			}
		}		

		if($update)
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName($this->details_table));
			$ids = array();
			//print_r($updateCase);exit;
			foreach($updateCase as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $id => $value) {
					if(!in_array($id, $ids)){
						$ids[] = $id;
					}
					if($field_name == 'metadata')
					{
						$value = json_encode($value);
					}
					$when .= sprintf('WHEN %d THEN %s ', $id, $db->quote($value));
				}
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName($this->detailsPKname).' '.$when.' END');
			}
			$query->where($db->quoteName($this->detailsPKname).' IN ('.implode(',', $ids).')');
			$db->setQuery($query);
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		
		if($insert)
		{
			$query = $db->getQuery(true);
			$query->insert($db->quoteName($this->details_table));
			$query->columns($db->quoteName($insertColumns));
			$query->values(implode('),(', $insertData));
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		return true;
	}

	/**
	* Load Usergroup based prices
	* 
	* @param Product ID $product_id
	* @param JObject Product Data called in getItem() function. $item
	* 
	* @return Boolean (true/false)
	*/

	protected function LoadUsergroupPrices($product_id = 0, &$item)
	{		
		if(!$product_id || !is_object($item))
		{
			return false;
		}
		$db = $this->getDbo();
		// Initialise the query.
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__qazap_product_user_price'));
		$query->where($db->quoteName($this->mainPKname) . ' = ' . $db->quote($product_id));
		try 
		{
			$db->setQuery($query);
			$rows = $db->loadAssocList('usergroup_id');			
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		if(empty($rows))
		{
			return true;
		}
		$item->product_user_price = $rows;
		return true;		
	}

	/**
	* Save Usergroup based product prices
	* 
	* @param form submit array. $data
	* @param Product table primary key. $product_id
	* 
	* @return Boolean (true/false)
	*/

	protected function saveUsergroupPrices($data, $product_id = 0)
	{
		if(empty($data) || !$product_id)
		{
			$this->setError('Invalid data saveUsergroupPrices().');
			return false;
		}
		
		if(!is_array($data['product_user_price']) || empty($data['product_user_price']))
		{
			$this->setError('Invalid data saveUsergroupPrices().');
			return false;
		}		
		
		//$columns = array('user_price_id', 'product_id', 'usergroup_id', 'product_baseprice', 'product_baseprice');		
		$update = false;
		$updateIDs = array();
		$updateData = array();
		$insert = false;
		$insertColumns = array();
		$insertData = array();
		$db = $this->getDbo();
		
		foreach ($data['product_user_price'] as $id => $fields)
		{	
			$fields['usergroup_id'] = $id;
			$fields['product_id'] = $product_id;
			
			if($fields['user_price_id'] > 0)
			{
				$user_price_id = $fields['user_price_id'];
				$updateIDs[] = $user_price_id;
				foreach($fields as $name => $value)
				{
					if(!isset($updateData[$name])) 
					{
						$updateData[$name] = array();
					}					
					$updateData[$name][$user_price_id] = $value;
				}
				$update = true;				 
			}
			else 
			{
				$insertColumns = array_keys($fields);
				$insertData[] = implode(',', $db->quote($fields));
				$insert = true;
			}
		}
		
		if($update)
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__qazap_product_user_price'));
			foreach($updateData as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $user_price_id => $value) {
					$when .= sprintf('WHEN %d THEN %s ', $user_price_id, $db->quote($value));
				}
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName('user_price_id').' '.$when.' END');
			}
			$query->where($db->quoteName('user_price_id').' IN ('.implode(',', $updateIDs).')');
			$db->setQuery($query);
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		
		if($insert)
		{
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__qazap_product_user_price'));
			$query->columns($db->quoteName($insertColumns));
			$query->values(implode('),(', $insertData));
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		return true;
	}
	
	/**
	* Load Quantity based product prices
	* 
	* @param $product_id - Product ID
	* @param $item - JObject Product Data called in getItem() function. 
	* 
	* @return Boolean (true/false)
	*/

	protected function LoadQuantityPrices($product_id = 0, &$item)
	{		
		if(!$product_id || !is_object($item))
		{
			return false;
		}
		// Initialise the query.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__qazap_product_quantity_price'));
		$query->where($db->quoteName($this->mainPKname) . ' = ' . $db->quote($product_id));
		try 
		{
			$db->setQuery($query);
			$rows = $db->loadAssocList();			
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		if(empty($rows))
		{
			return true;
		}
		$item->product_quantity_price = $rows;
		return true;		
	}	
	
	/**
	* Save Quantity based Product Pricing
	* @param $data - Form subnmit data array
	* @param $product_id - Product ID
	* 
	* @return Boolean (true/false)
	*/
	protected function saveQuantityPrices($data, $product_id = 0)
	{
		if(empty($data) || !$product_id)
		{
			$this->setError('Invalid data saveQuantityPrices().');
			return false;
		}
		
		if(!is_array($data['product_quantity_price']) || empty($data['product_quantity_price']))
		{
			$this->setError('Invalid data saveQuantityPrices().');
			return false;
		}		
		
		$columns = array('quantity_price_id', 'product_id', 'min_quantity', 'max_quantity', 'product_baseprice', 'product_customprice');		
		$delete = false;
		$deleteIds = array();
		$update = false;
		$updateIDs = array();
		$updateData = array();
		$insert = false;
		$insertData = array();
		$db = $this->getDbo();
		
		foreach ($data['product_quantity_price'] as $row)
		{	
			if(isset($row['deleteID']))
			{
				if($row['deleteID']) {
					$deleteIds[] = 	$row['deleteID'];
					$delete = true;							
				}		
			}						
			elseif($row['quantity_price_id'] > 0)
			{
				$quantity_price_id = $row['quantity_price_id'];
				$updateIDs[] = $quantity_price_id;
				foreach($columns as $column) 
				{
					if(!isset($updateData[$column])) 
					{
						$updateData[$column] = array();
					}
					if($column == $this->mainPKname)
					{
						$updateData[$column][$quantity_price_id] = $product_id;
					} 
					else 
					{
						$updateData[$column][$quantity_price_id] = isset($row[$column]) ? $row[$column] : '';	
					}					
					$update = true;				
				}
			}
			else 
			{
				$tmp = array();
				foreach($columns as $column) 
				{
					if($column == $this->mainPKname)
					{
						$tmp[$column] = $product_id;
					}
					else 
					{
						$tmp[$column] = isset($row[$column]) ? $row[$column] : '';
					}					
				}
				$insertData[] = implode(',', $db->quote($tmp));
				unset($tmp);
				$insert = true;
			}
		}
		
		if($update)
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__qazap_product_quantity_price'));
			foreach($updateData as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $user_price_id => $value) {
					$when .= sprintf('WHEN %d THEN %s ', $user_price_id, $db->quote($value));
				}
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName('quantity_price_id').' '.$when.' END');
			}
			$query->where($db->quoteName('quantity_price_id').' IN ('.implode(',', $updateIDs).')');
			$db->setQuery($query);
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		
		if($insert)
		{
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__qazap_product_quantity_price'));
			$query->columns($db->quoteName($columns));
			$query->values(implode('),(', $insertData));
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		
		if($delete)
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__qazap_product_quantity_price'));
			$query->where($db->quoteName('quantity_price_id').' IN (' . implode(',', $deleteIds) . ')');
			$db->setQuery($query);
			
			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}	
		}		
		
		return true;
	}	
	
	protected function checkData(&$data)
	{		
		if(!isset($data['membership']))
		{
			$data['membership'] = array();
		}
		
		if(!isset($data['related_categories']))
		{
			$data['related_categories'] = array();
		}
		
		if(!isset($data['related_products']))
		{
			$data['related_products'] = array();
		}				

		$languages = JLanguageHelper::getLanguages('lang_code');
		$existingAliases = $this->getExistingAliases($data['product_id']);
		
		if($existingAliases === false)
		{
			$this->setError($this->getError());
			return false;
		}		

		foreach($languages as $tag => $language)
		{
			if (trim($data['product_name'][$tag]) == '')
			{
				$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));
				return false;
			}

			if (!isset($data['product_alias'][$tag]) || trim($data['product_alias'][$tag]) == '')
			{
				$data['product_alias'][$tag] = $data['product_name'][$tag];
			}

			$data['product_alias'][$tag] = JApplication::stringURLSafe($data['product_alias'][$tag]);
			
			if(isset($existingAliases->$tag) && in_array($data['product_alias'][$tag], $existingAliases->$tag))
			{
				$data['product_alias'][$tag] = JString::increment($data['product_alias'][$tag], 'dash');
			}

			if (trim(str_replace('-', '', $data['product_alias'][$tag])) == '')
			{
				$data['product_alias'][$tag] = JFactory::getDate()->format('Y-m-d-H-i-s');
			}
			
			if (trim(str_replace('&nbsp;', '', $data['short_description'][$tag])) == '')
			{
				$data['short_description'][$tag] = '';
			}	
			
			if (trim(str_replace('&nbsp;', '', $data['product_description'][$tag])) == '')
			{
				$data['product_description'][$tag] = '';
			}			
		}
		
		return true;			
	}
	
	/**
	* Get existing Alias List 
	* 
	* @param For which table $type
	* @param Alias field name $field
	* @param language specific $langauge
	* 
	* @return
	*/	
	protected function getExistingAliases($skipID = false, $langauge = true)
	{
		$tableName = $this->details_table;
		$fieldName = 'product_alias';
		$parentField = $this->mainPKname;
		
		$db = $this->getDbo();
		
		$query = $db->getQuery(true);
		
		if($langauge)
		{
			$query->select(array($db->quoteName($fieldName), 'language'));
		}
		else
		{
			$query->select($db->quoteName($fieldName));
		}
		
		$query->from($db->quoteName($tableName));
		
		if($skipID)
		{
			$query->where($db->quoteName($parentField).' != '.$db->quote($skipID));
		}
		
		try 
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(empty($result))
		{
			return array();
		}
		
		$return = new stdClass;
		
		foreach($result as $value)
		{
			$lang = isset($value->language) ? $value->language : false;
			
			if($lang)
			{
				if(!isset($return->$lang))
				{
					$return->$lang = array();					
				}
				
				array_push($return->$lang, $value->$fieldName);
			}
			else
			{
				if(!isset($return->$fieldName))
				{
					$return->$fieldName = array();					
				}
				array_push($return->$fieldName, $value->$fieldName);
			}
		}
		
		return $return;
	}	

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array    Contains the modified title and alias.
	 *
	 * @since   1.0.0
	 */
	protected function generateNewTitleAlias($category_id, $language, $alias, $title)
	{
		// Alter the title & alias
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(d.product_alias)')
					->from($db->quoteName('#__qazap_products').' AS p')
					->leftJoin($db->quoteName($this->details_table) . ' AS d ON d.product_id = p.product_id')
					->where('p.category_id = '. (int) $category_id)
					->where('d.language = '. $db->quote($language))
					->where('d.product_alias = ' . $db->quote($alias));
						
		try 
		{
			$db->setQuery($query);
			$result = $db->loadResult();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}	

		if(!empty($result))
		{
			//$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}
		
		return array($title, $alias);
	}

	/**
	 * Custom clean the cache of com_qazap and qazap modules
	 *
	 * @since   1.0.0
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_qazap');
		parent::cleanCache('mod_qazap_categories');
		parent::cleanCache('mod_qazap_search');
		parent::cleanCache('mod_qazap_filters');
	}		
}