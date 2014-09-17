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

// Base this model on the backend version.
if(!class_exists('QazapModelFile'))
{
	require_once(QZPATH_MODEL_ADMIN . DS . 'file.php');
}


/**
* Methods supporting a download of Qazap products.
*/
class QazapModelDownload extends QazapModelFile
{

	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';	
	/**
	* Model typeAlias string. Used for version history.
	*
	* @var        string
	*/
	public $typeAlias = 'com_qazap.download';	
	
	/**
	* Constructor.
	*
	* @param			array    An optional associative array of configuration settings.
	* @see				JController
	* @since			1.0
	*/
	public function __construct($config = array()) 
	{
		parent::__construct($config);	 
	}
  
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
		
		// Get download id
		$download_id = $input->getInt('download_id', 0);
		$this->setState('com_qazap.download.id', $download_id);
		
		// Get passcode
		$passcode = $input->getAlnum('passcode', null);
		$this->setState('passcode', $passcode);
		
		$this->setState('layout', $app->input->getString('layout'));
	} 
	
	public function getValidationForm()
	{
		$filepath = JPATH_SITE . '/components/com_qazap/models/forms';
		
		jimport ('joomla.filesystem.file');
		
		if (!JFile::exists($filepath . DIRECTORY_SEPARATOR . 'download_validation.xml')) 
		{
			return null;
		}
		
		JForm::addFormPath($filepath);
		$form = JForm::getInstance('com_qazap.download.validation', 'download_validation', array('control' => 'qzform'));
		
		return $form;	
	}

	public function getDownload($download_id = null, $passcode = null)
	{
		$download_id = $download_id ? (int) $download_id : (int) $this->getState('com_qazap.download.id');
		$passcode = $passcode ? (string) $passcode : $this->getState('passcode');
		
		if(!$download_id || !$passcode)
		{
			return null;
		}

		if(!isset($this->_download[$download_id]) || $forceLoad)
		{
			$lang = JFactory::getLanguage();
			$multiple_language = JLanguageMultilang::isEnabled();
			$present_language = $lang->getTag();
			$default_language = $lang->getDefault();				
			
			$db = $this->getDbo();
			$query = $db->getQuery(true)
							->select('d.download_id, d.download_passcode, d.order_items_id, d.file_id, d.download_start_date, '.
											'd.download_count, d.last_download, d.download_block')
							->from('#__qazap_downloads AS d')
							->select('f.name, f.mime_type')
							->join('LEFT', '#__qazap_files AS f ON f.file_id = d.file_id')
							->join('LEFT', '#__qazap_product_file_map AS m ON m.file_id = f.file_id');

			if($multiple_language)
			{
				$query->select('CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END AS product_name');			
				$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = m.product_id AND pd.language = '.$db->quote($present_language));
				$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = m.product_id AND pdd.language = '.$db->quote($default_language));				
			}
			else
			{
				$query->select('pd.product_name');
				$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = m.product_id AND pd.language = '.$db->quote($present_language));
			}	
												
			$query->join('INNER', '#__qazap_order_items AS oi ON oi.order_items_id = d.order_items_id')
						->select('o.user_id')
						->join('INNER', '#__qazap_order AS o ON o.order_id = oi.order_id')							
						->where('d.download_id = ' . $download_id)
						->where('d.download_passcode = ' . $db->quote($passcode))
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
				$this->setError(JText::_('COM_QAZAP_ERROR_DOWNLOAD_NOT_FOUND'));
				return false;
			}
			else
			{
				$file_path = JPath::clean($this->_config->get('download_path') . DS . $download->name);
				
				if(is_file($file_path))
				{
					$download->file_size = $this->getSize($file_path);
					$this->_download[$download_id] = $download;
				}
				else
				{
					JError::raiseWarning(1, 'COM_QAZAP_ERROR_INVALID_DOWNLOAD_PATH');
					$this->_download[$download_id] = null;
				}
			}
		}
		
		return $this->_download[$download_id];
		
	}
	
	protected function getSize($path)
	{
		$bytes = (float) filesize($path);

		if ($bytes > 0)
		{
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB');

			if (array_key_exists($unit, $units) === true)
			{
				return sprintf('%s %s', number_format($bytes / pow(1024, $unit), 2), $units[$unit]);
			}
		}

		return $bytes;
	}	
	
	public function hitCount($download_id)
	{
		$download_id = (int) $download_id;
		
		if($download_id <= 0)
		{
			$this->setError('COM_QAZAP_DOWNLOAD_INVALID_DOWNLOAD_ID');
			return false;
		}
		
		$db = $this->getDbo();
		$date = JFactory::getDate();
		$fields = array(
			$db->quoteName('download_count') . ' = download_count + 1', 
			$db->quoteName('last_download') . ' = ' . $db->quote($date->toSQL())
		);		
		
		$query = $db->getQuery(true)
					->update($db->quoteName('#__qazap_downloads'))
					->set($fields)
					->where($db->quoteName('download_id') . ' = ' . $download_id);
		try
		{
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
}
