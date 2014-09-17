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
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
/**
 * Qazap model.
 */
class QazapModelInstall extends JModelLegacy
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QAZAP';
	protected $fileConfig;
	protected $activeStep;
	protected $failedStep;
	protected $backendPath;
	protected $frontendPath;
	protected $usergroup_level = null;
	protected $plugin_ids = null;

	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->backendPath	= JPATH_ROOT . '/administrator/components/com_qazap/';
		$this->frontendPath	= JPATH_ROOT . '/components/com_qazap/';
		$this->fileConfig	=	null;
	}
	/**
	 * Checks the availability of the parse_ini_file and parse_ini_string functions.
	 *
	 * @return	boolean  True if the method exists
	 *
	 * @since	3.1
	 */
	public function getGDAvailability()
	{
		$functions = array(
											'imagecreatefromjpeg',
											'imagecolortransparent',
											'imagecolorallocate',
											'imagecreatefromgif',
											'imagealphablending',
											'imagesavealpha',
											'imagecreatefrompng',
											'imagecreatefromgd',
											'imagecreatefromgd2',
											'imagecopyresampled',
											'imagedestroy',
											'getimagesize',
											'imagerotate'
											);
		
		$result = array();
											
		foreach($functions as $function)
		{
			if(!function_exists($function))
			{
				$result[] = $function;
			}
		}

		return $result;
	}
	
	/**
	 * Gets PHP options.
	 *
	 * @return	array  Array of PHP config options
	 *
	 * @since   1.0
	 */
	public function getPhpOptions()
	{
		$options = array();

		// Check the PHP Version.
		$option = new stdClass;
		$option->label  = JText::_('COM_QAZAP_INSTL_PHP_VERSION') . ' >= 5.3.10';
		$option->state  = version_compare(PHP_VERSION, '5.3.10', '>=');
		$option->notice = null;
		$options[] = $option;

		// Check for magic quotes gpc.
		$option = new stdClass;
		$option->label  = JText::_('COM_QAZAP_INSTL_IMAGE_HANDLER');
		$missingFunctions = $this->getGDAvailability();
		$option->state  = (empty($missingFunctions));
		$option->notice = !empty($missingFunctions) ? JText::sprintf('COM_QAZAP_INSTL_MISSING_FUNCTION', implode(', ', $missingFunctions)) : null;
		$options[] = $option;

		// Check for register globals.
		$option = new stdClass;
		$option->label  = JText::_('COM_QAZAP_INSTL_CURL');
		$option->state  = (in_array('curl', get_loaded_extensions()));
		$option->notice = null;
		$options[] = $option;

		return $options;
	}
	/**
	 * Checks if all of the mandatory PHP options are met
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	public function getPhpOptionsSufficient()
	{
		$result  = true;
		$options = $this->getPhpOptions();

		foreach ($options as $option)
		{
			$result = ($result && $option->state);
		}

		return $result;
	}
	
	public function getPackageAvailability()
	{
		if(!$hashes = $this->_getHashes())
		{
			$this->setError($this->getError());
			return false;
		}
		
		$packages = array();
		$be_packages = isset($hashes['be_packages']) ? $hashes['be_packages'] : null;
		$fe_packages = isset($hashes['fe_packages']) ? $hashes['fe_packages'] : null;
		$root = JPath::clean(JPATH_ROOT . DS);
		
		if(!empty($be_packages))
		{
			foreach($be_packages as $name => $hash)
			{
				$path = JPath::clean($this->backendPath . $name);
				$pack = new stdClass;
				$pack->label = str_replace($root, '', $path);
				$pack->exists = false;
				$pack->hash = false;
				
				if(file_exists($path))
				{
					$pack->exists = true;
					$file_hash = md5_file($path);
					if($file_hash == $hash)
					{
						$pack->hash = true;
					}					
				}
				
				$packages[] = $pack;
			}
		}
		
		if(!empty($fe_packages))
		{
			foreach($fe_packages as $name => $hash)
			{
				$path = JPath::clean($this->frontendPath . $name);
				$pack = new stdClass;
				$pack->label = str_replace($root, '', $path);
				$pack->exists = false;
				$pack->hash = false;
				
				if(file_exists($path))
				{
					$pack->exists = true;
					$file_hash = md5_file($path);
					if($file_hash == $hash)
					{
						$pack->hash = true;
					}					
				}
				
				$packages[] = $pack;
			}
		}		

		return $packages;
	}

	/**
	 * Checks if all of the mandatory PHP options are met
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	public function getPackageSufficient()
	{
		$result  = true;

		if(!$packages = $this->getPackageAvailability())
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!empty($packages))
		{
			foreach($packages as $package)
			{
				$result = ($result && $package->exists && $package->hash);
			}			
		}

		return $result;
	}
	
	protected function _getHashes()
	{
		static $hashes = null;
		
		if($hashes === null)
		{
			$path = JPath::clean(QAZAP_ADMINISTRATOR . DS . 'installer.hash.ini');
			
			if(!file_exists($path))
			{
				$this->setError($path . ' file not found.');
				return false;
			}
			
			if(!$hashes = parse_ini_file($path, true))
			{
				$this->setError($path . ' could not be parsed.');
				return false;
			}		
		}
		
		return $hashes;
	}
	

	/**
	 * Gets PHP Settings.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPhpSettings()
	{
		$settings = array();

		// Check for safe mode.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_PHP_MAX_EXECUTION_TIME');
		$setting->state = (float) ini_get('max_execution_time');
		$setting->recommended = 300;
		$settings[] = $setting;

		// Check for display errors.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_PHP_MAX_INPUT_TIME');
		$setting->state = (float) ini_get('max_input_time');
		$setting->recommended = 300;
		$settings[] = $setting;

		// Check for file uploads.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_PHP_MEMORY_LIMIT');
		$setting->state = (float) ini_get('memory_limit');
		$setting->recommended = 128;
		$settings[] = $setting;

		// Check for magic quotes runtimes.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_PHP_POST_MAX_SIZE');
		$setting->state = (float) ini_get('post_max_size');
		$setting->recommended = 2048;
		$settings[] = $setting;

		// Check for output buffering.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_PHP_UPLOAD_MAX_FILE_SIZE');
		$setting->state = (float) ini_get('upload_max_filesize');
		$setting->recommended = 2048;
		$settings[] = $setting;

		return $settings;
	}	
	/**
	 * Gets MySQL Settings.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */	
	public function getMySQLSettings()
	{
		$settings = array();
		$db = JFactory::getDBO();
		
		// Check for wait_timeout.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_MYSQL_WAIT_TIMEOUT');
		$db->setQuery("show variables like 'wait_timeout'");
		$info = $db->loadRow();		
		$setting->state = count($info) > 1? $info[1] : JText::_('COM_QAZAP_INSTL_DATA_NOT_AVAILABLE');
		$setting->recommended = 120;
		$settings[] = $setting;
		
		// Check for connect_timeout.
		$setting = new stdClass;
		$setting->label = JText::_('COM_QAZAP_INSTL_MYSQL_CONNECTION_TIMEOUT');
		$db->setQuery("show variables like 'connect_timeout'");
		$info = $db->loadRow();		
		$setting->state = count($info) > 1? $info[1] : JText::_('COM_QAZAP_INSTL_DATA_NOT_AVAILABLE');
		$setting->recommended = 120;
		$settings[] = $setting;		
		
		return $settings;		
	}
	
	public function getVersion()
	{
		// Load the local XML file first to get the local version
		$fileXml = QAZAP_ADMINISTRATOR . DS . 'qazap.xml';
		$parser = new SimpleXMLElement($fileXml, NULL, FALSE);
		$version = $parser->version;

		return $version;
	}	
	/**
	 * Method to parse the parameters of an extension, build the INI
	 * string for its default parameters, and return the INI string.
	 *
	 * @return  string   INI string of parameter values
	 *
	 * @since   3.1
	 */
	public function getConfigFromFile($forceReload = false)
	{
		if($this->fileConfig === null || $forceReload)
		{
			$fileXml = JPath::clean(QAZAP_ADMINISTRATOR . DS . 'config.xml');
			
			if(!file_exists($fileXml))
			{
				$this->setError($fileXml . ' file not found');
				return false;
			}
			
			$manifest = simplexml_load_file($fileXml);
			
			// Creating the data collection variable:
			$config = array();
			
			// Validate that we have a fieldset to use
			if (!isset($manifest->fieldset))
			{
				return $config;
			}
			
			// Getting the fieldset tags
			$fieldsets = $manifest->fieldset;

			// Iterating through the fieldsets:
			foreach ($fieldsets as $fieldset)
			{
				if (!count($fieldset->children()))
				{
					// Either the tag does not exist or has no children therefore we return zero files processed.
					continue;
				}

				// Iterating through the fields and collecting the name/default values:
				foreach ($fieldset as $field)
				{
					// Check against the null value since otherwise default values like "0"
					// cause entire parameters to be skipped.

					if (($name = $field->attributes()->name) === null)
					{
						continue;
					}

					if (($value = $field->attributes()->default) === null)
					{
						continue;
					}
					
					$value = (string) $value;
					
					if(strpos($value, ','))
					{
						$value = explode(',', $value);
					}
					
					$config[(string) $name] = $value;
				}
			}
			
			$this->fileConfig = $config;		
		}

		return $this->fileConfig;
	}
	
	public function getActions()
	{
		$actions = array(
									'unpack_backend', 
									'unpack_frontend', 
									'install', 
									'install_plugins',
									'install_modules', 
									'finishing'
								);
								
		return $actions;
	}
	
	public function getStepValue()
	{
		$actions = $this->getActions();
		$count = count($actions);
		$step = round((100 / $count), 3);
		
		return (float) $step;
	}
	
	public function run($process)
	{
		if(!$this->logProcess($process))
		{
			$this->setError($this->getError());
			return false;	
		}
		
		if(!$hashes = $this->_getHashes())
		{
			$this->setError($this->getError());
			return false;
		}

		$be_packages = isset($hashes['be_packages']) && !empty($hashes['be_packages']) ? array_keys($hashes['be_packages']) : array();
		$fe_packages = isset($hashes['fe_packages']) && !empty($hashes['fe_packages']) ? array_keys($hashes['fe_packages']) : array();
		
		switch(strtolower($process))
		{
			case 'unpack_backend' :
				
				if(in_array('backend.zip', $be_packages))
				{
					$package			= $this->backendPath . 'backend.zip';
					$destination	= $this->backendPath;

					if (!$this->extractArchive($package, $destination))
					{
						$this->setError(JText::sprintf('COM_QAZAP_INSTL_UNPACK_FAILED', 'backend.zip'));
						return false;
					}					
				}
				
				break;
				
			case 'unpack_frontend' :

				if(in_array('frontend.zip', $fe_packages))
				{			
					$package			= $this->frontendPath . 'frontend.zip';
					$destination	= $this->frontendPath;

					if (!$this->extractArchive($package, $destination))
					{
						$this->setError(JText::sprintf('COM_QAZAP_INSTL_UNPACK_FAILED', 'frontend.zip'));
						return false;
					}
				}
							
				break;
				
			case 'install' :

				if(in_array('backend.zip', $be_packages))
				{				
					if(!$this->_installSchema())
					{
						$this->setError($this->getError());
						return false;
					}
					
					if(!$this->_installDefaultData())
					{
						$this->setError($this->getError());
						return false;					
					}
					
					if(!$this->_installContentTypes())
					{
						$this->setError($this->getError());
						return false;						
					}
				}
				
				break;
				
			case 'install_plugins' :

				if(in_array('all_plugins.zip', $fe_packages))
				{							
					if(!$this->_installPlugins())
					{
						$this->setError($this->getError());
						return false;
					}

					if(!$this->enablePlugin('qazapsystem', 'system'))
					{
						$this->setError($this->getError());
						return false;
					}
					
					if(!$this->enableQazapPlugins())
					{
						$this->setError($this->getError());
						return false;
					}
					
				}
				
				break;
				
			case 'install_modules' :
			
				if(in_array('all_modules.zip', $fe_packages))
				{					
					if(!$modules = $this->_installModules())
					{
						$this->setError($this->getError());
						return false;
					}

					if(!$this->enableQazapModules($modules))
					{
						$this->setError($this->getError());
						return false;
					}
				}
				
				break;
				
			case 'finishing' :
				
				$installedVersion = $this->_getInstalledVersion();
				
				if($installedVersion === false && $this->getError())
				{
					$this->setError($this->getError());
					return false;
				}
				// If this is a new installation
				elseif(empty($installedVersion))
				{
					if(!$this->_installDefaultConfig())
					{
						$this->setError($this->getError());
						return false;						
					}
					
					// Lets try to create the default image folder
					$imagesFolder = JPath::clean($this->backendPath . 'images/qazap');
					
					if(JFolder::exists($imagesFolder))
					{
						try
						{
							$result = JFolder::copy($imagesFolder, JPATH_SITE . '/images/qazap');
						} 
						catch (RuntimeException $e) 
						{
							$this->setError($e->getMessage());
							return false;
						}
						
						if($result !== true)
						{
							$this->setError($imagesFolder . ' folder could not be moved to ' . JPATH_SITE . DIRECTORY_SEPARATOR . 'images');
							return false;
						}
						
						// Image folder moved to Joomla images directory. Now we can remove the temporary installation directory.
						$result = JFolder::delete($this->backendPath . 'images');
					}
				}
				
				if(!$this->_renameOrRemoveLogFile())
				{
					$this->setError($this->getError());
					return false;
				}
				
				JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_INSTL_COMPLETE_DESC'), 'success');
				
				break;
		}
		
		return true;
	}
	

	protected function _installSchema()
	{
		$db		= JFactory::getDBO();		
		$sqlFile = JPath::clean($this->backendPath . 'sql/install.mysql.utf8.sql');
		
		if(!file_exists($sqlFile))
		{
			$this->setError('install.mysql.utf8.sql not found');
			return false;
		}
		
		$buffer = file_get_contents($sqlFile);		
		$queries = $db->splitSql($buffer);

		if (!empty($queries))
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				
				if (!empty($query) && $query{0} != '#')
				{
					$db->setQuery($query);
					
					if (!$db->execute())
					{
						$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
						return false;
					}
				}
			}
		}

		return true;
	}	

	protected function _installDefaultData()
	{
		$db		= JFactory::getDBO();		
		$sqlFile = JPath::clean($this->backendPath . 'sql/default.mysql.utf8.sql');
		
		if(!file_exists($sqlFile))
		{
			$this->setError('default.mysql.utf8.sql not found');
			return false;
		}
		
		$buffer = file_get_contents($sqlFile);		
		$queries = $db->splitSql($buffer);

		if (!empty($queries))
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				
				if (!empty($query) && $query{0} != '#')
				{
					$db->setQuery($query);
					
					if (!$db->execute())
					{
						$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
						return false;
					}
				}
			}
		}
		
		JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_INSTL_COMPLETE_DESC'), 'success');
		
		return true;
	}	

	public function getSampleData()
	{
		$sqlFile = JPath::clean($this->backendPath . 'sql/sample_data.mysql.utf8.sql');
		
		if(!file_exists($sqlFile))
		{
			return false;
		}	
		
		return true;	
	}	
	
	/**
	* Method to install sample data
	* 
	* @return boolean
	*/
	public function installSampleData()
	{
		$db		= $this->getDBO();		
		$sqlFile = JPath::clean($this->backendPath . 'sql/sample_data.mysql.utf8.sql');
		
		if(!JFile::exists($sqlFile))
		{
			$this->setError($this->backendPath . 'sql/sample_data.mysql.utf8.sql not found');
			return false;
		}
		
		$buffer = file_get_contents($sqlFile);
		
		if(empty($buffer))
		{
			$this->setError('Contents could not be fetched from ' . $this->backendPath . 'sql/sample_data.mysql.utf8.sql');
			return false;			
		}
		
		$usergroup_level = $this->_createDemoVendorUsergroupLevel();
		
		if($usergroup_level === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!isset($usergroup_level['jusergroup_id']) || !isset($usergroup_level['jview_id']) || empty($usergroup_level['jusergroup_id']) || empty($usergroup_level['jview_id']))
		{
			$this->setError('Sample user group and user level creation error. ERROR: installSampleData()');
			return false;
		}
		
		$jusergroup_id = (int) $usergroup_level['jusergroup_id'];
		$jview_id = (int) $usergroup_level['jview_id'];
		
		$users = $this->_createUsers();
		
		if($users === false)
		{
			$this->setError($this->getError());
			return false;
		}

		if(!isset($users['user_userid']) || !isset($users['vendor_userid']) || empty($users['user_userid']) || empty($users['vendor_userid']))
		{
			$this->setError('Sample users creation error. ERROR: installSampleData()');
			return false;
		}
		
		$user_userid = (int) $users['user_userid'];
		$vendor_userid = (int) $users['vendor_userid'];
		
		$superAdminID = $this->_getSuperAdminID();
		
		if($superAdminID === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		$listplugin_id = $this->_getAttributePluginID('list');
	
		if($listplugin_id === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		$radioplugin_id = $this->_getAttributePluginID('radio');
		$textareaplugin_id = $this->_getAttributePluginID('textarea');
		$cashplugin_id = $this->_getAttributePluginID('cash');
		$collectplugin_id = $this->_getAttributePluginID('collect');		
				
		$finds = array('{{[jusergroup_id]}}', '{{[jview_id]}}', '{{[user_userid]}}', '{{[vendor_userid]}}', '{{[superAdmin_id]}}', '{{[listplugin_id]}}', '{{[radioplugin_id]}}', '{{[textareaplugin_id]}}', '{{[cashplugin_id]}}', '{{[collectplugin_id]}}');
		$replace = array($jusergroup_id, $jview_id, $user_userid, $vendor_userid, (int) $superAdminID, $listplugin_id, $radioplugin_id, $textareaplugin_id, $cashplugin_id, $collectplugin_id);
		
		$buffer = str_replace($finds, $replace, $buffer);			
		$queries = $db->splitSql($buffer);

		if(!empty($queries))
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				
				if (!empty($query) && $query{0} != '#')
				{
					$db->setQuery($query);
					
					if (!$db->execute())
					{
						$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
						return false;
					}
				}
			}
		}
		
		return true;
	}	
	
	protected function _getAttributePluginID($element)
	{
		if($this->plugin_ids === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
									->select('extension_id, element')
									->from('#__extensions')
									->where('folder IN (' . $db->quote('qazapcartattributes') . ',' . $db->quote('qazappayment') . ',' . $db->quote('qazapshipment') . ',' . $db->quote('qazapcustomfields') . ')')
									->where('type = ' . $db->quote('plugin'));
			try
			{
				$db->setQuery($query);
				$plugins = $db->loadObjectList('element');				
			}						
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($plugins))
			{
				$this->plugin_ids = array();
			}
			else
			{
				$this->plugin_ids = $plugins;
			}							
		}
		
		if(isset($this->plugin_ids[$element]))
		{
			return (int) $this->plugin_ids[$element]->extension_id;
		}
		
		return 0;			
	}
	
	protected function _createDemoVendorUsergroupLevel($name = 'Default Vendor Group')
	{
		if($this->usergroup_level === null)
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');		
			
			$db = $this->getDbo();
			
			$query = $db->getQuery(true)
									->select('id')
									->from('#__usergroups')
									->where('title = ' . $db->quote($name));
			try
			{
				$db->setQuery($query);
				$jusergroup_id = $db->loadResult();				
			}						
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($jusergroup_id))
			{
				$model = JModelLegacy::getInstance('group', 'UsersModel', array('ignore_request' => true));	
				$data = array(
									"id" => 0,
									"title" => (string) $name,
									"parent_id" => 4,
									"metadata" => array("tags" => '')
								);
				
				if(!$model->save($data))
				{
					$this->setError($model->getError());
					return false;
				}		
				
				$query->clear()
							->select('max(id)')
							->from('#__usergroups');
				try
				{
					$db->setQuery($query);
					$jusergroup_id = $db->loadResult();				
				}						
				catch(Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}			
			}				

			// For view level
			$query = $db->getQuery(true)
									->select('id')
									->from('#__viewlevels')
									->where('title = ' . $db->quote($name));
			try
			{
				$db->setQuery($query);
				$jview_id = $db->loadResult();				
			}						
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($jview_id))
			{
				$jlevel_model = JModelLegacy::getInstance('level', 'UsersModel', array('ignore_request' => true));
				
				$levelData = array(
												"id" => 0,
												"title" => (string) $name,
												"rules" => array(8, (int) $jusergroup_id)
											);
					   
				if(!$jlevel_model->save($levelData))
				{
					$model->delete($jusergroup_id);			
					$this->setError($jlevel_model->getError());
					return false;
				}
				 
				$query->clear()
							->select('max(id)')
							->from('#__viewlevels');
				try
				{
					$db->setQuery($query);
					$jview_id = $db->loadResult();				
				}						
				catch(Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}				
			}
			
			$this->usergroup_level = array('jusergroup_id' => (int) $jusergroup_id, 'jview_id' => (int) $jview_id);			
		}
		
		return $this->usergroup_level;				
	}	
	
	protected function _createUsers($usernames = array('user' => 'test', 'vendor' => 'demo'))
	{
		$usergroup_level = $this->_createDemoVendorUsergroupLevel();
		
		if(!isset($usergroup_level['jusergroup_id']) || !isset($usergroup_level['jview_id']) || empty($usergroup_level['jusergroup_id']) || empty($usergroup_level['jview_id']))
		{
			$this->setError('Demo user group and demo user level not found. ERROR: _createUsers()');
			return false;
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
								->select('id, username')
								->from('#__users')
								->where('username IN (' . $db->quote($usernames['user']) . ', ' . $db->quote($usernames['vendor']) . ')');
		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('username');				
		}						
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$vendor_id = null;
		$user_id = null;
		
		if(!empty($result))
		{
			$v_username = $usernames['vendor'];
			
			if(isset($result[$v_username]))
			{
				$vendor_id = $result[$v_username]->id;
				
				$vendor = JFactory::getUser($vendor_id);
				if(!in_array($usergroup_level['jusergroup_id'], $vendor->groups))
				{
					$vendor->groups[] = $usergroup_level['jusergroup_id'];
					
					if(!$vendor->save())
					{
						$this->setError($vendor->getError());
						return false;
					}					
				}
			}
			
			$u_username = $usernames['user'];
			if(isset($result[$u_username]))
			{
				$user_id = $result[$u_username]->id;
			}
		}
		
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');	
		$user_model = JModelLegacy::getInstance('user', 'UsersModel', array('ignore_request' => true));
		
		// Do we need to create a new test user
		if(empty($user_id))
		{
			$data = array(
								'id' => 0,
						    'name' => 'Test User',
						    'username' => 'test',
						    'password' => 'test',
						    'password2' => 'test',
						    'email' => 'testuser@qazap.com',
						    'registerDate' => '',
						    'lastvisitDate' => '',
						    'lastResetTime' => '',
						    'resetCount' => 0,
						    'sendEmail' => 0,
						    'block' => 0,
						    'requireReset' => 0,						    
						    'groups' => array(2),
						    'params' => array
						        (
						            'admin_style' => '',
						            'admin_language' => '',
						            'language' => '',
						            'editor' => '',
						            'helpsite' => '',
						            'timezone' => ''
						        ),
						    'tags' => ''			
						);			
			
			if(!$user_model->save($data))
			{
				$this->setError($user_model->getError());
				return false;
			}
			
			$query->clear()
						->select('MAX(id)')
						->from('#__users');
			try
			{
				$db->setQuery($query);
				$user_id = $db->loadResult();				
			}						
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}			
		}
		
		// Do we need to create a new demo vendor
		if(empty($vendor_id))
		{
			$data = array(
								'id' => 0,
						    'name' => 'Demo User',
						    'username' => 'demo',
						    'password' => 'demo',
						    'password2' => 'demo',
						    'email' => 'demouser@qazap.com',
						    'registerDate' => '',
						    'lastvisitDate' => '',
						    'lastResetTime' => '',
						    'resetCount' => 0,
						    'sendEmail' => 0,
						    'block' => 0,
						    'requireReset' => 0,						    
						    'groups' => array(2, $usergroup_level['jusergroup_id']),
						    'params' => array
						        (
						            'admin_style' => '',
						            'admin_language' => '',
						            'language' => '',
						            'editor' => '',
						            'helpsite' => '',
						            'timezone' => ''
						        ),
						    'tags' => ''			
						);			
			
			if(!$user_model->save($data))
			{
				$this->setError($user_model->getError());
				return false;
			}
			
			$query->clear()
						->select('MAX(id)')
						->from('#__users');
			try
			{
				$db->setQuery($query);
				$vendor_id = $db->loadResult();				
			}						
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}			
		}
		
		$return = array('user_userid' => $user_id, 'vendor_userid' => $vendor_id);
		
		return $return;					
	}
	
	protected function _getSuperAdminID()
	{
		$user = JFactory::getUser();
		$isSuperAdmin = $user->authorise('core.admin');
		
		if($isSuperAdmin)
		{
			return $user->get('id');
		}
		
		$query->clear()
					->select('a.id')
					->from('#__users AS a')
					->join('LEFT', '#__user_usergroup_map AS b ON b.user_id = a.id')
					->where('b.group_id = 8');
		try
		{
			$db->setQuery($query);
			$id = $db->loadResult();				
		}						
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}			
		
		return $id;
	}
	
	protected function _installDefaultConfig()
	{
		if(!$config = $this->getConfigFromFile())
		{
			$this->setError($this->getError());
			return false;
		}
		
		$db = JFactory::getDBO();
		$config = (array) $config;
		
		$query = $db->getQuery(true)
								->update($db->quoteName('#__extensions'))
								->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($config)))
								->where($db->quoteName('element') . ' = ' . $db->quote('com_qazap'))
								->where($db->quoteName('type') . ' = ' . $db->quote('component'));

		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorNum() . ':' . $db->getErrorMsg());
			return false;
		}
		
		return true;		
	}
	
	protected function _installContentTypes()
	{
		$db				= JFactory::getDBO();		
		$sqlFile	= JPath::clean($this->backendPath . 'sql/content_type.mysql.utf8.sql');
		
		if(!JFile::exists($sqlFile))
		{
			$this->setError('content_type.mysql.utf8.sql not found');
			return false;
		}
		
		$sql = $db->getQuery(true)
							->select('type_id')
							->from('#__content_types')
							->where('type_alias = ' . $db->quote('com_qazap.product'));		
		try
		{
			$db->setQuery($sql);
			$result = $db->loadResult();
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!empty($result))
		{
			return true;
		}
		
		$buffer = file_get_contents($sqlFile);		
		$queries = $db->splitSql($buffer);

		if (!empty($queries))
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				
				if (!empty($query) && $query{0} != '#')
				{
					$db->setQuery($query);
					
					if (!$db->execute())
					{
						$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
						return false;
					}
				}
			}
		}
		
		return true;			
	}
	
	protected function _getInstalledVersion()
	{
		$config = QZApp::getConfig();
		$version = $config->get('qazap_version', null);
		
		return $version;
	}
	
	protected function _getFileVersion()
	{
		if(!$tmp = $this->getConfigFromFile())
		{
			$this->setError($this->getError());
			return false;
		}
		
		$config = new JRegistry;
		$config->loadArray($tmp);
		$version = $config->get('qazap_version', null);
		
		return $version;
	}

	/**
	 * Method to extract archive out
	 *
	 * @returns	boolean	True on success false otherwise.
	 **/
	public function extractArchive($source, $destination)
	{
		// Cleanup path
		$destination		= JPath::clean($destination);
		$source					= JPath::clean($source);

		return JArchive::extract($source, $destination);
	}
	
	public function logProcess($process)
	{
		$logFile = JPath::clean($this->backendPath . 'installer.log.ini');

		$buffer = '';
		
		if(file_exists($logFile))
		{
			$buffer = file_get_contents($logFile) . "\n";			
		}
		
		$buffer .= JText::_('COM_QAZAP_INSTL_' . strtoupper($process)) . "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . JFactory::getDate();

		if(!JFile::write($logFile, $buffer))
		{
			$this->setError('COM_QAZAP_FAILED_TO_WRITE_LOG');
			return false;
		}
		
		return true;	
	}
	
	protected function _renameOrRemoveLogFile()
	{
		$logFile = JPath::clean($this->backendPath . 'installer.log.ini');
		$date = JFactory::getDate();
		$newFilename = JPath::clean($this->backendPath . 'installer.log.' . $date->toISO8601(true) . '.ini');
		
		if(!JFile::copy($logFile, $newFilename))
		{
			// Copy failed. Log file will not be retained
		}
		
		if(!JFile::delete($logFile))
		{
			$this->setError($this->backendPath . 'installer.log.ini could not be removed. Please remove it manually.');
			return false;
		}
		
		return true;
	}

	protected function _installModules()
	{
		$app 					= JFactory::getApplication();
		$source				= QAZAP_SITE . '/all_modules.zip';
		$destination	= QAZAP_SITE . '/all_modules/';
		$tmp_path			= $app->getCfg('tmp_path');
		$modules 			= array();
		$return				= array();
		$installed		= array();
		
		if (!JFolder::exists($destination))
		{
			JFolder::create($destination);
		}

		if(JArchive::extract($source, $destination))
		{
			$packages = JFolder::files($destination, '.zip');
			
			foreach($packages as $pack)
			{
				$modules[] = $destination . $pack;
				$path_parts = pathinfo($destination . $pack);
				$return[] = $path_parts['filename'];
			}
		}		
		
		if(!empty($modules))
		{
			foreach($modules as $module)
			{
				$package   = JInstallerHelper::unpack($module);
				$installer = JInstaller::getInstance();

				if (!$installer->install($package['dir']))
				{
					$this->setError($this->getAppErrors());
					return false;
				}

				$xml = $installer->get('manifest');
				$type = (string) $xml->attributes()->type;

				// Set the installation path
				if(count($xml->files->children()))
				{
					foreach ($xml->files->children() as $file)
					{
						if ((string) $file->attributes()->module)
						{
							$element = (string) $file->attributes()->module;
							break;
						}
					}
				}
				
				if(!empty($element))
				{
					$installed[] = $element;
				}				

				// Cleanup the install files
				if (!is_file($package['packagefile']))
				{
					$package['packagefile'] = $tmp_path . DS .$package['packagefile'];
				}

				JInstallerHelper::cleanupInstall('', $package['extractdir']);
			}			
		}

		if(!$this->_saveInstalledExtensions('module', $installed))
		{
			$this->setError($this->getError());
			return false;
		}
				
		// Delete temporary installation folder
		JFolder::delete($destination);
			
		return $return;
	}

	protected function _installPlugins()
	{
		$app 					= JFactory::getApplication();
		$source				= QAZAP_SITE . '/all_plugins.zip';
		$destination	= QAZAP_SITE . '/all_plugins/';
		$tmp_path			= $app->getCfg('tmp_path');
		$plugins 			= array();
		$installed		= array();

		$qzgroups = array
							(
								'qazapcartattributes', 'qazapcustomfields',
								'qazappayment', 'qazapshipment',
								'qazapvendorpayment', 'qazapsystem',
								'qazapcoupon', 'qazapproduct'								
							);

		if(!$this->_createFolder($qzgroups, JPATH_SITE . '/plugins/'))
		{
			$this->setError($this->getError());
			return false;
		}

		if(!JFolder::exists($destination))
		{
			if(!$this->_createFolder($destination))
			{
				$this->setError($this->getError());
				return false;
			}
		}

		if(JArchive::extract($source, $destination))
		{
			$packages = JFolder::files($destination, '.zip');
			
			foreach($packages as $pack)
			{
				$plugins[] = $destination . $pack;
			}
		}		
		
		if(!empty($plugins))
		{
			foreach($plugins as $plugin)
			{
				$package   = JInstallerHelper::unpack($plugin);
				$installer = JInstaller::getInstance();				

				if (!$installer->install($package['dir']))
				{
					$this->setError($this->getAppErrors());
					return false;
				}
				
				$xml = $installer->get('manifest');
				$type = (string) $xml->attributes()->type;

				// Set the installation path
				if (count($xml->files->children()))
				{
					foreach ($xml->files->children() as $file)
					{
						if ((string) $file->attributes()->$type)
						{
							$element = (string) $file->attributes()->$type;
							break;
						}
					}
				}

				$group = (string) $xml->attributes()->group;
				$installed[$element] = $group;
				
				// Cleanup the install files
				if (!is_file($package['packagefile']))
				{
					$package['packagefile'] = $tmp_path . DS .$package['packagefile'];
				}

				JInstallerHelper::cleanupInstall('', $package['extractdir']);
			}			
		}
		
		if(!$this->_saveInstalledExtensions('plugin', $installed))
		{
			$this->setError($this->getError());
			return false;
		}
		
		// Delete temporary installation folder
		JFolder::delete($destination);		
		return true;
	}

	public function enableQazapModules($modules)
	{
		$modules = (array) $modules;
		
		if(empty($modules))
		{
			return true;
		}
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->clear()
					->select($db->quoteName('id') . ', ' . $db->quoteName('module'))
					->from($db->quoteName('#__modules'))
					->where($db->quoteName('position') . ' = ' . $db->quote('') . ' OR ' . $db->quoteName('position') . ' IS NULL')
					->where($db->quoteName('module') . ' IN (' . implode(',', $db->quote($modules)) . ')')
					->group($db->quoteName('module'));					
	
		try 
		{
			$db->setQuery($query);
			$results = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		if(empty($results))
		{
			// Nothing to do.
			return true;
		}

		$ids = array();
		$when = ''; 
		
		foreach($results as $result)
		{
			if(!$params = $this->_getModuleParamsFromFile($result->module))
			{
				$this->setError($this->getError());
				return false;
			}
			
			$ids[]   = (int) $result->id;
			$params  = (array) $params;
			$when   .= sprintf('WHEN %d THEN %s ', $result->id, $db->quote(json_encode($params)));
		}

		$query->clear()
					->update($db->quoteName('#__modules'))
					->set($db->quoteName('published') . ' = ' . $db->quote(1))
					->set($db->quoteName('position') . ' = ' . $db->quote('position-7'))
					->set($db->quoteName('params') . ' = CASE '. $db->quoteName('id') . ' ' . $when . ' END')
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->getError($db->getErrorNum() . ':' . $db->getErrorMsg());
			return false;
		}
		
		$query->clear()
					->select($db->quoteName('moduleid'))
					->from($db->quoteName('#__modules_menu'))
					->where($db->quoteName('moduleid') . ' IN (' . implode(',', $ids) . ')');
					
		$db->setQuery($query);
		$menus = $db->loadColumn();
		
		if(!empty($menus))
		{
			foreach($menus as $module_id)
			{
				$key = array_search($module_id, $ids);
				unset($ids[$key]);
			}
		}
		
		if(!empty($ids))
		{
			$values = array();
			
			foreach($ids as $id)
			{
				$values[] = $id . ', 0';
			}
			
			$tableColumns = array('moduleid', 'menuid');
			
			$query->clear()
						->insert($db->quoteName('#__modules_menu'))
						->columns($db->quoteName($tableColumns))
						->values(implode('),(', $values));

			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->getError($db->getErrorNum() . ':' . $db->getErrorMsg());
				return false;
			}		
		}
		
		return true;
	}
	
	protected function _getModuleParamsFromFile($module)
	{
		$source = JPath::clean(JPATH_ROOT . '/modules/' . $module . '/' . $module . '.xml');
			
		if(!file_exists($source))
		{
			$this->setError($source . ' file not found');
			return false;
		}
		
		$params = array();
		$manifest = simplexml_load_file($source);

		// Validate that we have a fieldset to use
		if (!isset($manifest->config) || !isset($manifest->config->fields))
		{
			return $params;
		}
		elseif($manifest->config->fields->attributes()->name != 'params')
		{
			return $params;
		}
		
		// Getting the fieldset tags
		$fieldsets = $manifest->config->fields->fieldset;

		// Iterating through the fieldsets:
		foreach ($fieldsets as $fieldset)
		{
			if (!count($fieldset->children()))
			{
				// Either the tag does not exist or has no children therefore we return zero files processed.
				continue;
			}

			// Iterating through the fields and collecting the name/default values:
			foreach ($fieldset as $field)
			{
				// Check against the null value since otherwise default values like "0"
				// cause entire parameters to be skipped.
				if (($name = $field->attributes()->name) === null)
				{
					continue;
				}

				if (($value = $field->attributes()->default) === null)
				{
					continue;
				}
				
				$value = (string) $value;
				
				if(strpos($value, ','))
				{
					$value = explode(',', $value);
				}
				
				$params[(string) $name] = $value;
			}
		}
		
		return $params;		
	}

	public function enableQazapPlugins()
	{
		$db = JFactory::getDBO();
		$groups = array('qazapcartattributes', 'qazapcustomfields', 'qazappayment', 'qazapshipment', 'qazapvendorpayment');

		$query = $db->getQuery(true)
								->update($db->quoteName('#__extensions'))
								->set($db->quoteName('enabled') . ' = 1')
								->where($db->quoteName('folder') . ' IN ( ' . implode(',', $db->quote($groups)) . ')');
								
		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorNum() . ':' . $db->getErrorMsg());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to enable a plugin by name
	* @param undefined $plugin
	* 
	* @return
	*/
	public function enablePlugin($plugin, $group = null)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
								->update($db->quoteName('#__extensions'))
								->set($db->quoteName('enabled') . ' = 1')
								->where($db->quoteName('element') . ' = ' . $db->quote($plugin));
								
		if(!empty($group))
		{
			$query->where($db->quoteName('folder') . ' = ' . $db->quote($group));
		}

		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorNum() . ':' . $db->getErrorMsg());
			return false;
		}
		
		return true;

	}

	
	public function getSteps($active = null, $failed = null)
	{
		$active = !empty($active) ? $active : $this->activeStep;
		$failed = !empty($failed) ? $failed : $this->failedStep;
		$options = $this->getActions();
					
		$activeKey = !empty($active) ? array_search(strtolower($active), $options) : -1;
		$steps = array();
		
		foreach($options as $key => $option)
		{
			$step = new stdClass;
			$step->label  = JText::_('COM_QAZAP_INSTL_' . strtoupper($option));
			
			if($key < $activeKey || $active == 'all-done')
			{
				$step->state  = 'C'; // Completed
			}
			elseif($option == strtolower($active))
			{
				$step->state  = 'A'; // Active / running
			}
			elseif($option == strtolower($failed))
			{
				$step->state  = 'F'; // Failed
			}
			else
			{
				$step->state  = 'P'; // Pending by default
			}
			
			$step->notice = null;			
			$steps[] = $step;		
		}

		return $steps;		
	}
	
	public function getAppErrors()
	{
		$msgs = JFactory::getApplication()->getMessageQueue();
		
		if(!empty($msgs))
		{
			$errors = array();
			foreach($msgs as $msg)
			{
				$type = strtolower($msg['type']);
				if($type != 'success' && $type != 'message')
				{
					$errors[] = $msg['message'];
				}
			}
			
			if(!empty($errors))
			{
				return implode('<br/>', $errors);
			}
		}
		
		JFactory::getApplication()->set('_messageQueue', ''); 
		
		return null;
	}	
	
	protected function _saveInstalledExtensions($type, $data)
	{
		$data = (array) $data;
		
		if(empty($data))
		{
			return true;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
								->select('names')
								->from('#__qazap_install')
								->where('extension_type = ' . $db->quote($type));			
		try
		{
			$db->setQuery($query);
			$oldData = $db->loadResult();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!empty($oldData))
		{
			if(!is_null($oldData))
			{
				$oldData = json_decode($oldData, true);				
				$data = array_merge($oldData, $data);
				if(isset($oldData[0]))
				{
					$data = array_unique($data);
				}
			}	
			
			$query->clear()
						->update($db->quoteName('#__qazap_install'))
						->set('names = ' . $db->quote(json_encode($data)))
						->where('extension_type = ' . $db->quote($type));
			$db->setQuery($query);
			
			if(!$db->execute())
			{
				$this->setError($db->getErrorNum() . ':' . $db->getErrorMsg());
				return false;				
			}					
		}
		else
		{
			$query->clear()
						->insert($db->quoteName('#__qazap_install'))
						->columns($db->quoteName(array('extension_type', 'names')))
						->values($db->quote($type) . ', ' . $db->quote(json_encode($data)));
						
			$db->setQuery($query);
			
			if(!$db->execute())
			{
				$this->setError($db->getErrorNum() . ':' . $db->getErrorMsg());
				return false;				
			}			
		}
		
		return true;
	}
	
	public function checkAddFieldToTable($tableName, $field, $fieldType) 
	{
		static $cache = array();		
		$db = JFactory::getDbo();
		
		if(!isset($cache[$tableName]))
		{
			$fields = $db->getTableColumns($tableName, false);

			if(empty($fields))
			{
				$this->setError(sprintf('No columns found for %s table', $name));
				return false;
			}	
			
			$cache[$tableName] = array_keys($fields);		
		}		
		
		if(!in_array($field, $cache[$tableName]))
		{
			$query = 'ALTER TABLE `' . $tableName . '` ADD ' . $field . ' ' . $fieldType;
			$db->setQuery($query);
			
			if(!$db->execute())
			{
				$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
				return false;
			} 
		}
		
		return true;
	}		


	protected function _createFolder($folderPaths = array(), $path = null)
	{
		$folderPaths = (array) $folderPaths;
		
		if(!empty($folderPaths))
		{
			foreach($folderPaths as $folderPath)
			{
				if(!empty($path))
				{
					$folderPath = JPath::clean($path . $folderPath);
				}
				else
				{
					$folderPath = JPath::clean($folderPath);
				}				
				
				if(!JFolder::exists($folderPath))
				{
					if(!JFolder::create($folderPath))
					{
						$this->setError($folderPath . ' folder could be created. Please check permission.');
						return false;
					}				
				}
				
				if(!JFile::exists($folderPath . '/index.html'))
				{
					$buffer = '<html><body bgcolor="#FFFFFF"></body></html>';
					if(!JFile::write($folderPath . '/index.html', $buffer))
					{
							$this->setError($folderPath . '/index.html file could be created. Please check permission.');
							return false;					
					}					
				}
			}
		}
		
		return true;	
	}	
}