<?php
/**
 * plugin.php
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
defined('_JEXEC') or die();

JLoader::import('joomla.plugin.plugin');
JLoader::import('joomla.html.parameter');

/**
* Qazap Subscriptions payment plugin abstract class
*/
abstract class QZPlugin extends JPlugin
{	
	public $db = null;
	public $app = null;	
	public $address = null;
	public $ordergroup = null;
	public $context = null;	
	
	protected $_paramsFormData;
	protected $_tableName;
	protected $_tableKey;
	protected $_data = array();
	protected static $_currencies = array();
	
	protected $_tableInstances = array();	
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		$this->_tableName = '#__'.$this->_type.'_'.$this->_name;
		$this->_tableKey = $this->_name.'_id';

		// Load the language files
		$jlang = JFactory::getLanguage();
		$filename = 'plg_' . $this->_type . '_' . $this->_name;
		$jlang->load($filename, JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load($filename, JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load($filename, JPATH_ADMINISTRATOR, null, true);
		
		// Set the context
		if($this->context === null)
		{
			$session = JFactory::getSession();
			$this->context = $session->getId();				
		}	
	}

	/**
	* Method to display plugin parameters in Attribute edit form
	* 
	* @param	object	$plugin		This plugin data
	* @param	array		$params		Saved params
	* @param	object	$form			Empty variable
	* 
	* @return	JForm object
	*/
	public function onEditParams($plugin, $params, &$form)
	{
		if($plugin->element != $this->_name)
		{
			return;
		}
		
		$this->_paramsFormData = new stdClass;
		
		$filepath = JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR . $this->_name;
		
		jimport ('joomla.filesystem.file');
		if (!JFile::exists ($filepath . DIRECTORY_SEPARATOR . 'params.xml')) 
		{
			return;
		}
		
		JForm::addFormPath($filepath);
		$form = JForm::getInstance('plg_'.$this->_type.'.'.$this->_name.'.params', $data = 'params', $options = array('control' => 'jform'));	
			
		if($params)
		{
			$this->_paramsFormData->params = $params;
		}
		
		$form->bind($this->_paramsFormData);
		
		return true;	
	}	
	
	/**
	* Method to get plugin params from @filesource
	* 
	* @param object       $plugin
	* @param JForm object $paramsForm
	* 
	* @return JForm object
	*/
	public final function onGetParamsFormPath($plugin, &$paramsForm)
	{
		if($plugin->element != $this->_name)
		{
			return;
		}		
		
		$filepath = JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR . $this->_name;
		
		jimport ('joomla.filesystem.file');
		
		if (!JFile::exists ($filepath . DIRECTORY_SEPARATOR . 'params.xml')) 
		{
			return;
		}
		
		$paramsForm =	JPath::clean($filepath . DIRECTORY_SEPARATOR . 'params.xml');
		
		return true;
	}
	
	/**
	* Method to create internal plugin table
	* 
	* @param undefined $plugin
	* @param undefined $shipmentTable
	* @param undefined $isNew
	* 
	* @return
	*/
	public function onSaveCreateTable($plugin, $shipmentTable, $isNew)
	{
		if($plugin->element != $this->_name)
		{
			return;
		}
		
		if(!$query = $this->getTableBuildQuery())
		{
			return;
		}
		
		$db = $this->db;
		$db->setQuery($query);
		
		try 
		{
			$db->execute();
		} 
		catch (Exception $e) 
		{
			JError::raiseWarning (1, 'Plg' . ucfirst($this->_type) . ucfirst($this->_name) . '::onSaveCreateTable: ' . JText::_ ('COM_QAZAP_SQL_ERROR') . ' ' . $db->stderr (TRUE));
			return false;
		}
		
		return true;		
	}

	/**
	* @param $tableComment
	* @return string
	*/
	protected function getTableBuildQuery($tableComment = null) 
	{			
		if(!$fields = $this->getTableFields())
		{
			return false;
		}
		
		if(!$tableComment)
		{
			$tableComment = ucfirst($this->_type) . ' ' . ucfirst($this->_name) . ' Plugin Table';
		}

		$query = 'CREATE TABLE IF NOT EXISTS `' . $this->_tableName . '` (';
		
		$primaryKey = array($this->_tableKey => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT');
		
		$fields = array_merge($primaryKey, $fields, $this->getLoggableTableFields());
		
		foreach ($fields as $fieldname => $fieldtype) 
		{
			$query .= "\n" . '`' . $fieldname . '` ' . $fieldtype . ', ';
		}
		
		$query .= "\n" . 'PRIMARY KEY (`' . $this->_tableKey . '`)';
	  $query .= "\n" . ') ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT="' . $tableComment . '" AUTO_INCREMENT=1 ;';
		
		return $query;
	}

	/**
	* Method to pass required table fields to be implemented in the respective plugin
	* 
	* @return	Mixed	Array of SQL fields or false
	* @since	1.0.0
	*/	
	protected function getTableFields()
	{
		return false;
	}
	
	protected function getTable()
	{
		if(!$this->getTableFields())
		{
			JError::raiseWarning (1, 'Plg' . ucfirst($this->_type) . ucfirst($this->_name) . '::getTable: Plugin do not have any table');
			return false;
		}
		
		$uniqueName = $this->_type . $this->_name;		

		if(!isset($this->_tableInstances[$uniqueName]))
		{
			try
			{
				$this->_tableInstances[$uniqueName] = new QazapTablePlugin($this->_tableName, $this->_tableKey, $this->db);					
			} 
			catch (Exception $e) 
			{
				JError::raiseWarning(500, $e->getMessage());
				return false;					
			}
		}
		
		return $this->_tableInstances[$uniqueName];
	}
	
	/**
	* Method to save internal plugin data in its own database
	* 
	* @param array $data Associative data array to be saved
	* 
	* @return boolean
	*/
	protected function save($data)
	{
		$table = $this->getTable();

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
			
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			
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
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		return true;		
	}
	
	/**
	* Method to get internal plugin data by a ordergroup @id
	* 
	* @param integer $ordergroup_id
	* @param boolean $forceload
	* 
	* @return mixed (array/boolean)
	*/
	protected function getDataByOrdergroupID($ordergroup_id, $forceload = false)
	{
		if(!isset($this->_data[$ordergroup_id]) || $forceload)
		{
			$db = $this->db;
			$query = $db->getQuery(true)
									->select('*')
									->from($this->_tableName)
									->where('ordergroup_id = ' . (int) $ordergroup_id);
			try
			{
				$db->setQuery($query);
				$data = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$this->_data[$ordergroup_id] = $data;
		}
		
		return $this->_data[$ordergroup_id];
	}
	
	/**
	* Fields to be created for all internal plugin table
	* 
	* @return array
	*/
	protected function getLoggableTableFields()
	{
		return array(
			'created_time'  => 'datetime NOT NULL default \'0000-00-00 00:00:00\'',
			'created_by'  => "int(11) NOT NULL DEFAULT '0'",
			'modified_time' => 'datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\'',
			'modified_by' => "int(11) NOT NULL DEFAULT '0'",
			'checked_out'   => 'int(11) NOT NULL DEFAULT \'0\'',
			'checked_out_time'   => 'datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\'',			
		);
	}	
	
	
	/**
	* Method to get complete method details
	* 
	* @param	integer	Method ID
	* @param	string	Method for which the plugin id to be returned (payment, shipment).
	* 
	* @return	mixed		Plugin object or false in case of failure
	* @since	1.0.0
	*/  
	protected function getMethod($method_id, $method)
	{
		$method = strtolower((string) $method);
		
		if(!in_array($method, array('payment', 'shipment')))
		{
			return false;
		}
		
		static $cache = array();
		$key = 'method_id:' . (int) $method_id . '.method:' . $method;
		
		if(!isset($cache[$key]))
		{
			$method_table = '#__qazap_' . $method . '_methods';
			
			$query = $this->db->getQuery(true)
							->select('a.*')
							->from('#__qazap_' . $method . '_methods AS a')
							->select('b.element AS plugin')				
							->join('LEFT', '#__extensions AS b ON a.' . $method . '_method = b.extension_id')
							->where('a.id = ' . (int) $method_id);		
			try
			{
				$this->db->setQuery($query);
				$plugin = $this->db->loadObject();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			if(empty($plugin))
			{
				$this->setError(JText::_('COM_QAZAP_PLUGIN_ERROR_INVALID_METHOD_' . strtoupper($method)));
				return false;
			}
			
			$cache[$key] = $plugin;						
		}
		
		return $cache[$key];		
	}	
	
	protected final function renderLayout($displayData, $layout = 'default')
	{
		// Render the layout
		ob_start();
		include $this->getLayoutPath($this->_type, $this->_name, $layout);
		$return = ob_get_clean();
		ob_end_clean();
		
		return $return;
	}

	public final function getLayoutPath($type, $name, $layout = 'default')
	{
		$template = JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = ($temp[0] == '_') ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = ($temp[1]) ? $temp[1] : 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
		$bPath = JPATH_SITE . '/plugins/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_SITE . '/plugins/' . $type . '/' . $name . '/tmpl/default.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}
		elseif (file_exists($bPath))
		{
			return $bPath;
		}
		else
		{
			return $dPath;
		}
	}
	
	protected final function setParams(&$params)
	{
		if (!($params instanceof JRegistry))
		{
			$temp = new JRegistry;
			
			if(is_array($params))
			{
				$temp->loadArray($params);
			}
			else
			{
				$temp->loadString($params);
			}
			
			$params = $temp;
		}		

		$this->params = &$params;		
	}
	
	protected final function setAddress(&$address)
	{
		if (!($address instanceof JRegistry))
		{
			$temp = new JRegistry;			
			$address = (array) $address;			
			$temp->loadArray($address);			
			$address = $temp;
		}

		$this->address = &$address;
	}
	
	protected final function setOrdergroup(&$ordergroup)
	{
		if (!($ordergroup instanceof QZCart))
		{
			$ordergroup = new QZCart($ordergroup);
		}

		$this->ordergroup = &$ordergroup;
	}
	
	protected final function getCurrency($currency_id)
	{		
		if(!isset(static::$_currencies[$currency_id]))
		{
			$db = $this->db;
			$query = $db->getQuery(true)
							->select('id, currency, exchange_rate, currency_symbol, code3letters, numeric_code, '.
											'decimals, decimals_symbol, format, thousand_separator')
							->from('#__qazap_currencies')
							->where('id = '. (int) $currency_id);
			$db->setQuery($query);
			$currency = $db->loadObject();
			
			if(empty($currency))
			{
				JError::raiseWarning (1, 'Plg' . ucfirst($this->_type) . ucfirst($this->_name) . '::getValueInCurrency: ' . JText::_ ('COM_QAZAP_ERROR_CURRENCY_LOAD_FAILED'));
				static::$_currencies[$currency_id] = false;
			}
			else
			{
				static::$_currencies[$currency_id] = $currency;
			}
		}
		
		return static::$_currencies[$currency_id];
	}

	/**
	* Logs the received IPN information to file
	*
	* @param array $data
	* @param bool $isValid
	*/
	protected function logDebug($data, $type = null)
	{
		ob_start();
		print_r($data);
		$str = ob_get_contents();
		ob_end_clean();
				
		$config = JFactory::getConfig();
		$logpath = $config->get('log_path');

		$logFilenameBase = $logpath.'/'.$this->_type.'_'.$this->_name.'_debug';

		$logFile = $logFilenameBase.'.php';
		JLoader::import('joomla.filesystem.file');
		if(!JFile::exists($logFile)) 
		{
			$dummy = "<?php die(); ?>\n";
			JFile::write($logFile, $dummy);
		} 
		else 
		{
			if(@filesize($logFile) > 1048756) 
			{
				$altLog = $logFilenameBase.'-1.php';
				if(JFile::exists($altLog)) 
				{
					JFile::delete($altLog);
				}
				JFile::copy($logFile, $altLog);
				JFile::delete($logFile);
				$dummy = "<?php die(); ?>\n";
				JFile::write($logFile, $dummy);
			}
		}
		$logData = JFile::read($logFile);
		if($logData === false) $logData = '';
		$logData .= "\n" . str_repeat('-', 60);
		$logData .= (string) !empty($type) ? $type : '';
		$logData .= "\nDate/time : ".gmdate('Y-m-d H:i:s')." GMT\n\n";		
		$logData .= $str;
		$logData .= "\n";
		JFile::write($logFile, $logData);
	}
	
	/**
	 * Translates the given 2-digit country code into the 3-digit country code.
	 *
	 * @param string $country
	 */
	protected function translateCountry($country)
	{
		$countryMap = array(
			'AX' => 'ALA', 'AF' => 'AFG', 'AL' => 'ALB', 'DZ' => 'DZA', 'AS' => 'ASM',
			'AD' => 'AND', 'AO' => 'AGO', 'AI' => 'AIA', 'AQ' => 'ATA', 'AG' => 'ATG',
			'AR' => 'ARG', 'AM' => 'ARM', 'AW' => 'ABW', 'AU' => 'AUS', 'AT' => 'AUT',
			'AZ' => 'AZE', 'BS' => 'BHS', 'BH' => 'BHR', 'BD' => 'BGD', 'BB' => 'BRB',
			'BY' => 'BLR', 'BE' => 'BEL', 'BZ' => 'BLZ', 'BJ' => 'BEN', 'BM' => 'BMU',
			'BT' => 'BTN', 'BO' => 'BOL', 'BA' => 'BIH', 'BW' => 'BWA', 'BV' => 'BVT',
			'BR' => 'BRA', 'IO' => 'IOT', 'BN' => 'BRN', 'BG' => 'BGR', 'BF' => 'BFA',
			'BI' => 'BDI', 'KH' => 'KHM', 'CM' => 'CMR', 'CA' => 'CAN', 'CV' => 'CPV',
			'KY' => 'CYM', 'CF' => 'CAF', 'TD' => 'TCD', 'CL' => 'CHL', 'CN' => 'CHN',
			'CX' => 'CXR', 'CC' => 'CCK', 'CO' => 'COL', 'KM' => 'COM', 'CD' => 'COD',
			'CG' => 'COG', 'CK' => 'COK', 'CR' => 'CRI', 'CI' => 'CIV', 'HR' => 'HRV',
			'CU' => 'CUB', 'CY' => 'CYP', 'CZ' => 'CZE', 'DK' => 'DNK', 'DJ' => 'DJI',
			'DM' => 'DMA', 'DO' => 'DOM', 'EC' => 'ECU', 'EG' => 'EGY', 'SV' => 'SLV',
			'GQ' => 'GNQ', 'ER' => 'ERI', 'EE' => 'EST', 'ET' => 'ETH', 'FK' => 'FLK',
			'FO' => 'FRO', 'FJ' => 'FJI', 'FI' => 'FIN', 'FR' => 'FRA', 'GF' => 'GUF',
			'PF' => 'PYF', 'TF' => 'ATF', 'GA' => 'GAB', 'GM' => 'GMB', 'GE' => 'GEO',
			'DE' => 'DEU', 'GH' => 'GHA', 'GI' => 'GIB', 'GR' => 'GRC', 'GL' => 'GRL',
			'GD' => 'GRD', 'GP' => 'GLP', 'GU' => 'GUM', 'GT' => 'GTM', 'GN' => 'GIN',
			'GW' => 'GNB', 'GY' => 'GUY', 'HT' => 'HTI', 'HM' => 'HMD', 'HN' => 'HND',
			'HK' => 'HKG', 'HU' => 'HUN', 'IS' => 'ISL', 'IN' => 'IND', 'ID' => 'IDN',
			'IR' => 'IRN', 'IQ' => 'IRQ', 'IE' => 'IRL', 'IL' => 'ISR', 'IT' => 'ITA',
			'JM' => 'JAM', 'JP' => 'JPN', 'JO' => 'JOR', 'KZ' => 'KAZ', 'KE' => 'KEN',
			'KI' => 'KIR', 'KP' => 'PRK', 'KR' => 'KOR', 'KW' => 'KWT', 'KG' => 'KGZ',
			'LA' => 'LAO', 'LV' => 'LVA', 'LB' => 'LBN', 'LS' => 'LSO', 'LR' => 'LBR',
			'LY' => 'LBY', 'LI' => 'LIE', 'LT' => 'LTU', 'LU' => 'LUX', 'MO' => 'MAC',
			'MK' => 'MKD', 'MG' => 'MDG', 'MW' => 'MWI', 'MY' => 'MYS', 'MV' => 'MDV',
			'ML' => 'MLI', 'MT' => 'MLT', 'MH' => 'MHL', 'MQ' => 'MTQ', 'MR' => 'MRT',
			'MU' => 'MUS', 'YT' => 'MYT', 'MX' => 'MEX', 'FM' => 'FSM', 'MD' => 'MDA',
			'MC' => 'MCO', 'MN' => 'MNG', 'MS' => 'MSR', 'MA' => 'MAR',	'MZ' => 'MOZ',
			'MM' => 'MMR', 'NA' => 'NAM', 'NR' => 'NRU', 'NP' => 'NPL', 'NL' => 'NLD',
			'AN' => 'ANT', 'NC' => 'NCL', 'NZ' => 'NZL', 'NI' => 'NIC',	'NE' => 'NER',
			'NG' => 'NGA', 'NU' => 'NIU','NF' => 'NFK',	'MP' => 'MNP',	'NO' => 'NOR',
			'OM' => 'OMN','PK' => 'PAK','PW' => 'PLW',	'PS' => 'PSE',	'PA' => 'PAN',
			'PG' => 'PNG','PY' => 'PRY','PE' => 'PER','PH' => 'PHL','PN' => 'PCN',
			'PL' => 'POL','PT' => 'PRT','PR' => 'PRI','QA' => 'QAT','RE' => 'REU',
			'RO' => 'ROU','RU' => 'RUS','RW' => 'RWA','SH' => 'SHN','KN' => 'KNA',
			'LC' => 'LCA','PM' => 'SPM','VC' => 'VCT','WS' => 'WSM','SM' => 'SMR',
			'ST' => 'STP','SA' => 'SAU','SN' => 'SEN','CS' => 'SCG','SC' => 'SYC',
			'SL' => 'SLE','SG' => 'SGP','SK' => 'SVK','SI' => 'SVN','SB' => 'SLB',
			'SO' => 'SOM','ZA' => 'ZAF','GS' => 'SGS','ES' => 'ESP','LK' => 'LKA',
			'SD' => 'SDN','SR' => 'SUR','SJ' => 'SJM','SZ' => 'SWZ','SE' => 'SWE',
			'CH' => 'CHE','SY' => 'SYR','TW' => 'TWN','TJ' => 'TJK','TZ' => 'TZA',
			'TH' => 'THA','TL' => 'TLS','TG' => 'TGO','TK' => 'TKL','TO' => 'TON',
			'TT' => 'TTO','TN' => 'TUN','TR' => 'TUR','TM' => 'TKM','TC' => 'TCA',
			'TV' => 'TUV','UG' => 'UGA','UA' => 'UKR','AE' => 'ARE','GB' => 'GBR',
			'US' => 'USA','UM' => 'UMI','UY' => 'URY','UZ' => 'UZB','VU' => 'VUT',
			'VA' => 'VAT','VE' => 'VEN','VN' => 'VNM','VG' => 'VGB','VI' => 'VIR',
			'WF' => 'WLF','EH' => 'ESH','YE' => 'YEM','ZM' => 'ZMB','ZW' => 'ZWE'
		);
		
		if(array_key_exists($country, $countryMap)) 
		{
			return $countryMap[$country];
		} 
		else 
		{
			return '';
		}
	}
	
	protected function floatOrEmpty(&$value)
	{
		if(!empty($value))
		{
			$value = trim($value);
			
			if(!empty($value))
			{
				$value = floatval($value);
			}
			else
			{
				$value = '';
			}			
		}
		
		return $value;		
	}

}


class QazapTablePlugin extends JTable 
{
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct($tableName, $tableKey, &$db) 
	{
		parent::__construct($tableName, $tableKey, $db);
	}

	/**
	* Overloaded bind function to pre-process the params.
	*
	* @param	array		Named array
	* @return	null|string	null is operation was satisfactory, otherwise returns an error
	* @see		JTable:bind
	* @since	1.0.0
	*/
	public function bind($array, $ignore = '') 
	{        
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');


		return parent::bind($array, $ignore);
	}


	/**
	* Overloaded check function
	*/
	public function check() 
	{
		$k = $this->_tbl_key;
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->$k == 0) 
		{
		    $this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

    
	/**
	* Method to store a node in the database table.
	*
	* @param   boolean  $updateNulls  True to update null values as well.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/store
	* @since   1.0.0
	*/
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$key = $this->getKeyName();
		
		if ($this->$key)
		{
			// Existing item
			$this->modified_time		= $date->toSql();
			$this->modified_by			= $user->get('id');
		}
		else
		{
			// New contact. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}		
		
		return parent::store($updateNulls);
	}
}