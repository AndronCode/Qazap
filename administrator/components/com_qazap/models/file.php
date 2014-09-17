<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Anik Saha <anik.saha.2007@gmail.com> - http://www.virtueplanet.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Qazap model.
 */
class QazapModelFile extends JModelLegacy
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QAZAP';
	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_qazap.file';
	
	protected $_files = array();
	
	protected $_multi_files = array();
	
	protected $_downloads = array();

	protected $_config = null;

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
		$this->_config = QZApp::getConfig();
		parent::__construct($config);
	}

	public function saveProduct($data)
	{
		$product_id = isset($data['product_id']) ? $data['product_id'] : 0;
		$file = isset($data['downloadable_file']) ? $data['downloadable_file'] : null;
		
		if(!$file)
		{
			$this->setError(JText::_('COM_QAZAP_PRODUCT_ERROR_INVALID_DOWNLOADABLE_FILE'));
			return false;
		}
		
		if($product_id > 0)
		{
			$isNew = false;
			$db = $this->getDbo();
			
			$savedFile = $this->getFileByProduct($product_id);
			
			if($savedFile === false && $this->getError())
			{
				$this->setError($this->getError());
			}

			if($savedFile === null)
			{
				$isNew = true;
			}			
			
			if(!$fileData = $this->getFileData($file))
			{
				$this->setError($this->getError());
				return false;
			}
			
			if($isNew)
			{
				$result = $db->insertObject('#__qazap_files', $fileData, 'file_id');
			}
			else
			{
				$fileData->file_id = $savedFile->file_id;
				$result = $db->updateObject('#__qazap_files', $fileData, 'file_id', false);
			}
			
			if($result)
			{
				$mapData = new stdClass;
				$mapData->product_id = $product_id;
				$mapData->file_id = $fileData->file_id;
				
				if($isNew)
				{
					$result = $db->insertObject('#__qazap_product_file_map', $mapData);
				}
				else
				{
					$result = $db->updateObject('#__qazap_product_file_map', $mapData, 'product_id', false);
				}
			}
			
			return $result;
		}
		
		return true;
	}
	
	public function getFileByProduct($product_id)
	{
		if(!isset($this->_files[$product_id]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
							->select('map.product_id, map.file_id')
							->from('#__qazap_product_file_map AS map')
							->select('f.name, f.mime_type')
							->join('LEFT', '#__qazap_files AS f ON f.file_id = map.file_id')
							->where('map.product_id = ' . (int) $product_id);
			try
			{
				$db->setQuery($query);
				$file = $db->loadObject();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(!empty($file))
			{
				$this->_files[$product_id] = $file;
			}
			else
			{
				$this->_files[$product_id] = null;
			}
		}
		
		return $this->_files[$product_id];
	}
	
	
	
	protected function getFileData($filename)
	{
		jimport('joomla.filesystem.path');
		
		$path = $this->_config->get('download_path');
		if(substr($path, -1) == '/' || substr($path, -1) == '\\')
		{
    	$path = substr($path, 0, -1);
		}
		
		$filepath = JPath::clean($path . DS . $filename);
		
		if(!is_file($filepath))
		{
			$this->setError('Could not find the file in the server');
			return false;
		}
		
		$return = new stdClass;
		$return->file_id = 0;
		$return->name = $filename;
		
		try
		{
			if(function_exists('finfo_file'))
			{
				$return->mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
			}
			else
			{
				$return->mime_type = '';
			}
			
		}
		catch(RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return $return;		
	}
	
	public function updateDownloadableFile(QZCart &$ordergroup)
	{
		$ordergroup = clone($ordergroup);
		$order_items = $ordergroup->getProducts(true);
		
		if(empty($order_items))
		{
			// Nothing to update
			return true;
		}
		
		$product_ids = array();
		$order_items_ids = array();
		$download_ids = array();
		$order_statuses = array();
		
		foreach($order_items as $order_item)
		{
			$product_ids[] = (int) $order_item->product_id;
			$order_items_ids[$order_item->product_id] = (int) $order_item->order_items_id;
			$order_statuses[$order_item->product_id] = $order_item->order_status;
			
			if($order_item->download_id > 0)
			{
				$download_ids[$order_item->product_id] = $order_item->download_id;				
			}				
		}		
							
		$files = $this->getFilesByProducts($product_ids);
		
		if($files === false && $this->getError())
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!$files)
		{
			// Nothing to do further
			return true;
		}
		
		// Initialize the variables
		$activation_statuses = $this->_config->get('download_activation_status', array());		
		$updateData = array();
		$updateColumns = array('file_id', 'download_block');
		$insertData = array();
		$insertColumns = array('download_id', 'download_passcode', 'order_items_id', 'file_id', 'download_start_date', 'download_count', 'last_download', 'download_block');
		$date = JFactory::getDate();
		$db = $this->getDbo();
		$query = $db->getQuery(true);		
		
		foreach($files as $file)
		{
			$block = 0;
			if(!in_array($order_statuses[$file->product_id], $activation_statuses))
			{
				$block = 1;
			}			
			
			if(array_key_exists($file->product_id, $download_ids))
			{
				$updateData = $this->buildUpdateArray($updateData, $updateColumns);
				$download_id = $download_ids[$file->product_id];
				$updateData['file_id'][$download_id] = $file->file_id;
				$updateData['download_block'][$download_id] = $block;
			}
			elseif($block == 0)
			{
				$temp = array(
										'download_id' => 0,
										'download_passcode' => $db->quote($this->getNewPasscode()),
										'order_items_id' => (int) $order_items_ids[$file->product_id],
										'file_id' => (int) $file->file_id,
										'download_start_date' => $db->quote($date->toSql()),
										'download_count' => 0,
										'last_download' => $db->quote('0000-00-00 00:00:00'),
										'download_block' => 0
									);
				$insertData[] = implode(',', $temp);
			}
		}
		
		if(!empty($updateData))
		{
			$query->clear()->update($db->quoteName('#__qazap_downloads'));
			
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
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName('download_id').' '.$when.' END');
			}
			
			$query->where($db->quoteName('download_id').' IN ('.implode(',', $ids).')');
						
			try 
			{
				$db->setQuery($query);
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}			
		}
		
		if(!empty($insertData))
		{
			$query->clear()->insert($db->quoteName('#__qazap_downloads'));
			$query->columns($db->quoteName($insertColumns));	
			$query->values(implode('),(', $insertData));
						
			try 
			{
				$db->setQuery($query);
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
	
	public function getFilesByProducts($product_ids)
	{
		$product_ids = (array) $product_ids;
		
		if(empty($product_ids))
		{
			return null;
		}
		
		$hash = md5(serialize($product_ids));
		
		if(!isset($this->_multi_files[$hash]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
							->select('map.product_id, map.file_id')
							->from('#__qazap_product_file_map AS map')
							->select('f.name, f.mime_type')
							->join('LEFT', '#__qazap_files AS f ON f.file_id = map.file_id')
							->where('map.product_id IN (' . implode(',', $product_ids) . ')')
							->group('map.product_id, map.file_id');
			try
			{
				$db->setQuery($query);
				$files = $db->loadObjectList('product_id');
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($files))
			{
				$this->_multi_files[$hash] = null;
			}
			else
			{
				$this->_multi_files[$hash] = $files;
			}
		}
		
		return $this->_multi_files[$hash];
	}
	
	
	
	public function getDownload($download_id = null, $forceLoad = false)
	{
		$download_id = (int) $download_id;
		
		if(!$download_id)
		{
			return null;
		}
		
		if(!isset($this->_download[$download_id]) || $forceLoad)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
							->select('d.download_id, d.download_passcode, d.order_items_id, d.file_id, d.download_start_date, '.
											'd.download_count, d.last_download, d.download_block')
							->from('#__qazap_downloads AS d')
							->join('INNER', '#__qazap_order_items AS oi ON oi.order_items_id = d.order_items_id')
							->select('o.user_id')
							->join('LEFT', '#__qazap_order AS o ON o.order_id = oi.order_id')							
							->where('d.download_id = ' . $download_id)
							->group('d.download_id, d.download_passcode, d.order_items_id, d.file_id, d.download_start_date, '.
											'd.download_count, d.last_download, d.download_block');
			try
			{
				$db->setQuery($query);
				$download = $db->loadObject();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($download))
			{
				$this->_download[$download_id] = null;
			}
			else
			{
				$this->_download[$download_id] = $download;
			}
		}
		
		return $this->_download[$download_id];
		
	}
	
	protected function buildUpdateArray(&$updateData, $updateColumns)
	{
		$updateData = array();		
		
		foreach($updateColumns as $column)
		{
			if(!isset($updateData[$column]))
			{
				$updateData[$column] = array();
			}
		}		
	}
	
	protected function getNewPasscode()
	{
		return JApplication::getHash(JUserHelper::genRandomPassword());
	}

}