<?php
/**
 * app.php
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

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('QAZAP_ADMINISTRATOR', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_qazap');
define('QAZAP_SITE', JPATH_SITE . DS . 'components' . DS . 'com_qazap');
define('QAZAP_LIBRARIES', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_qazap' . DS . 'libraries');
define('QZPATH_MODEL_ADMIN', QAZAP_ADMINISTRATOR . DS . 'models');
define('QZPATH_MODEL', QAZAP_SITE . DS . 'models');
define('QZPATH_CONTROLLER_ADMIN', QAZAP_ADMINISTRATOR . DS .'controllers');
define('QZPATH_CONTROLLER', QAZAP_SITE . DS . 'controllers');
define('QZPATH_TABLE_ADMIN', QAZAP_ADMINISTRATOR . DS .'tables');
define('QZPATH_TABLE', QAZAP_SITE . DS .'tables');
define('QZPATH_OVERRIDES', QAZAP_ADMINISTRATOR . DS . 'overrides');
define('QZPATH_HELPER_ADMIN', QAZAP_ADMINISTRATOR . DS . 'helpers');
define('QZPATH_HELPER', QAZAP_SITE . DS . 'helpers');
define('QZPATH_PLUGIN', QAZAP_ADMINISTRATOR . DS . 'plugins');
define('QZPATH_LAYOUT_ADMIN', QAZAP_ADMINISTRATOR . DS . 'layouts' . DS . 'qazap');
define('QZPATH_LAYOUT', QAZAP_SITE . DS . 'layouts' . DS . 'qazap');
define('QZPATH_CUSTOMFIELD', JPATH_SITE . DS . 'plugins' . DS . 'qazapcustomfields');
define('QZPATH_CARTATTRIBUTE', JPATH_SITE . DS . 'plugins' . DS . 'qazapcartattributes');
define('QZPATH_PAYMENT', JPATH_SITE . DS . 'plugins' . DS . 'qazappayment');
define('QZPATH_SHIPMENT', JPATH_SITE . DS . 'plugins' . DS . 'qazapshipment');

function qzdump($data)
{
  ob_start();
  print_r($data);
  $str = ob_get_contents();
  ob_end_clean();	
	
	echo '<pre class="qazap-debug-qzdump" dir="ltr">';
	echo '<small>' . gettype($data) . '</small> ';
	echo '<font color="#cc0000">' . $str . '</font>';
	echo '<i>{Length: ' . strlen($str) . '}</i>';
	if((is_object($data) || is_array($data)) && (!($data instanceof JForm) && !($data instanceof SimpleXMLElement)))
	{
		//echo '<br/><i>{Serialized Length: ' . strlen(serialize($data)) . '}</i>';
	}	
	echo '<ul style="margin:15px 0; padding:15px; list-style-position:inside; background: #FAFAFA; border: 1px solid #DDD;">';
	if(function_exists('xdebug_call_file'))
		echo '<li>Calling File: ' . xdebug_call_file() . '</li>';
	if(function_exists('xdebug_call_class'))
		echo '<li>Calling Class: ' . xdebug_call_class() . '</li>';
	if(function_exists('xdebug_call_function'))
		echo '<li>Calling Function: ' . xdebug_call_function() . '</li>';	
	if(function_exists('xdebug_call_line'))
		echo '<li>Calling Line: ' . xdebug_call_line() . '</li>';
	echo '<li>Current Memory Usage: ' . QZApp::calculateSize(memory_get_usage()) . '</li>';
	echo '<li>Peak Memory Usage: ' . QZApp::calculateSize(memory_get_peak_usage()) . '</li>';
	echo '</ul>';
	echo '</pre>';
}

abstract class QZApp
{	
	/**
	* Qazap component's JRegistry object
	* 
	* @var		object
	* @since	1.0
	*/
	protected static $config = NULL;
	
	/**
	* Array of model instances
	* 
	* @var			array
	* @since		1.0
	*/	
	protected static $models = array();
	
	protected static $mediaViews = array('product', 'category', 'manufacturer', 'vendor', 'userinfo');

	/**
	* Method to set autoloader function
	* 
	* @since		1.0
	*/
	public static function setup() 
	{
		spl_autoload_register(array('QZApp', 'helperLoader'));
		spl_autoload_register(array('QZApp', 'htmlLoader'));
		spl_autoload_register(array('QZApp', 'pluginLoader'));
	}
	
	/**
	* Auto load helper class on call
	* @param string $helper Name of the helper class
	* 
	* @return		false if file not found or class already exists
	* @since 		1.0
	*/	
  private static function helperLoader($helper) 
	{
		if (class_exists($helper, false))
		{
			return true;
		}
		if(JFactory::getApplication()->isSite())
		{
			$path_1 = QZPATH_HELPER;
			$path_2 = QZPATH_HELPER_ADMIN;
		}
		else
		{
			$path_1 = QZPATH_HELPER_ADMIN;		
			$path_2 = QZPATH_HELPER;	
		}	
		
		if($helper == 'QazapHelper')
		{
			$file = 'qazap.php';				
		}
		elseif(strpos($helper, 'QazapHelper') === 0)
		{
			$fileName = substr($helper, 11);
			$file = strtolower($fileName) . '.php';			
		}		
		elseif((strpos($helper, 'QZ') === 0) && (strpos(strtolower($helper), 'plugin') === false))
		{
			$fileName = substr($helper, 2);
			$file = strtolower($fileName) . '.php';			
		}
		else
		{
			return;
		}		
		
		if(is_file($path_1 . DS . $file) && !class_exists($helper)) 
		{
			//echo 'Trying to load ', $helper, ' via ', __METHOD__, "()\n";
			// Lets try to load the helper file if class no exists			
		  require_once($path_1 . DS . $file);
		}
		elseif(is_file($path_2 . DS . $file) && !class_exists($helper)) 
		{
			//echo 'Trying to load ', $helper, ' via ', __METHOD__, "()\n";
			// Lets try to load the helper file if class no exists			
		  require_once($path_2 . DS . $file);
		}		
		else
		{
			return false;
		}
  }	
	
	/**
	* Auto load html class on call
	* @param string $html Name of the helper class with prefix QZHtml
	* 
	* @return		false if file not found or class already exists
	* @since		1.0
	*/	
  private static function htmlLoader($class) 
	{
		if (class_exists($class, false))
		{
			return true;
		}		
		
		if(strpos($class, 'QZHtml') === 0)
		{
			$fileName = substr($class, 6);
		}
		elseif(strpos($class, 'JHtml') === 0)
		{
			$fileName = substr($class, 5);
		}		
		else
		{
			return;
		}
		
		$admin_file = QZPATH_HELPER_ADMIN . DS . 'html' . DS . strtolower($fileName) . '.php';
		$site_file = QZPATH_HELPER . DS . 'html' . DS . strtolower($fileName) . '.php';
		
		if(is_file($admin_file) && !class_exists($class)) 
		{
		  require_once($admin_file);
		}
		elseif(is_file($site_file) && !class_exists($class))
		{
			require_once($site_file);
		}
		else
		{
			return false;
		}
		
		if(class_exists($class))
		{
			return true;
		}
  }		

	/**
	* Auto load html class on call
	* @param string $html Name of the helper class with prefix QZHtml
	* 
	* @return		false if file not found or class already exists
	* @since		1.0
	*/	
  private static function pluginLoader($class) 
	{
		if (class_exists($class, false))
		{
			return true;
		}		

		if((strpos($class, 'QZ') !== 0) && (strpos(strtolower($class), 'plugin') === false))
		{
			return;
		}
		
		$fileName = substr($class, 2);
		$file = QZPATH_PLUGIN . DS . strtolower($fileName) . '.php';
		
		if(is_file($file) && !class_exists($class)) 
		{
			//echo 'Trying to load ', $class, ' via ', __METHOD__, "()\n";
			// Lets try to load the helper file if class no exists			
		  require_once($file);
		}
		else
		{
			return false;
		}
  }
	
	/**
	 * Returns a reference to a Qazap models object
	 *
	 * @param   string  $model  		Name of model
	 * @param   array   $config			An array of config
	 * @param		booean	$admin			Set true to load model from admin or false to load from site
	 *
	 * @return  QazapModel         	Model object
	 *
	 * @since   1.0
	 */
	public static function getModel($model, $config = array(), $admin = true)
	{
		$hash = md5($model . serialize($config));

		if (isset(self::$models[$hash]))
		{
			return self::$models[$hash];
		}

		$classname = 'QazapModel' . ucfirst($model);

		if (!class_exists($classname))
		{
			$admin_path = QZPATH_MODEL_ADMIN . DS . strtolower($model) . '.php';
			$site_path = QZPATH_MODEL . DS . strtolower($model) . '.php';
			
			if ($admin && is_file($admin_path))
			{
				require_once $admin_path;
			}
			elseif(!$admin && is_file($site_path))
			{
				require_once $site_path;
			}
			else
			{
				// File path does not exists
				return false;
			}
			
			if (!class_exists($classname))
			{
				// Class not found
				return false;
			}			
		}

		self::$models[$hash] = new $classname($config);

		return self::$models[$hash];
	}	
	
	/**
	* Get Configuration
	* 
	* @return params.
	*/
	public static function getConfig($loadmenu = false, $params = NULL)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$active = $menu->getActive();
		
		if(!$params)
		{
			$params = new JRegistry;
		}
		elseif(!($params instanceof JRegistry))
		{
			$temp = new JRegistry;
			$temp->loadString($params);
			$params = $temp;
		}
		
		if($loadmenu && $active && $active->component == 'com_qazap')
		{
			$menu_params = new JRegistry;
			$menu_params->loadString($active->params);
			$params->merge($menu_params);
		}
		
		$hash = 'QazapConfig_'.serialize($params);
		
		if(isset(self::$config[$hash]))
		{
			return self::$config[$hash];
		}
		
		self::$config[$hash] = clone JComponentHelper::getParams('com_qazap');		
		self::$config[$hash]->merge($params);

		return self::$config[$hash];
	}
	
	/**
	* Load all required JavaScript Files
	* 
	* @return null
	*/	
	public static function loadJS($additionalFiles = array()) 
	{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			$view = $app->input->get('view', '', 'string');
			JHtml::_('jquery.framework');
			JHtml::_('behavior.tooltip');
			JHtml::_('behavior.formvalidation');
			
			$uri = JURI::base(true).'/index.php';
			$path = JURI::base(true).'/';
			
			$doc->addScriptDeclaration("\n"."window.qzuri = '$uri';"."\n"."window.qzpath = '$path';"."\n");
			
			$asset_path = JURI::base(true).'/components/com_qazap/assets/js/';
			$installerDummy = JPath::clean(QAZAP_ADMINISTRATOR . DS . 'installer.log.ini');
					
			if($app->isSite())
			{
				$config = self::getConfig();
				$ajaxcart = (int) $config->get('ajax_addtocart', 1);				
				$doc->addScriptDeclaration("window.qzajaxcart = $ajaxcart;"."\n");
				$files = array('jquery.easing.1.3.min.js', 'jquery.hoverIntent.minified.js', 'jquery.qazap.js', 'site.js');
			}
			else
			{
				JHtml::_('formbehavior.chosen', 'select');
								
				if(in_array(strtolower($view), static::$mediaViews))
				{
					$files = array('jquery-ui-1.10.4.custom.min.js', 'jquery.fancybox-1.3.4.pack.js', 'spin.min.js', 'qazap.js');
				}
				elseif($view == 'install' || is_file($installerDummy))
				{
					$files = array('jquery.fancybox-1.3.4.pack.js', 'jquery.easing.1.3.min.js', 'spin.min.js', 'jquery.nearest.min.js', 'installer.js');
				}
				else
				{
					$files = array('jquery.fancybox-1.3.4.pack.js', 'jquery.easing.1.3.min.js', 'spin.min.js', 'jquery.nearest.min.js', 'qazap.js');
				}				
			}

			if(count($additionalFiles))
			{
				$files = array_merge($additionalFiles, $files);
			}	
						
			foreach($files as $file)
			{
				$doc->addScript($asset_path.$file);
			}			
	}
	
	
	/**
	* Load all required CSS files
	* 
	* @return null
	*/
	public static function loadCSS($additionalFiles = array()) 
	{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			$view = $app->input->get('view', '', 'string');
			
			$asset_path = JURI::base(true).'/components/com_qazap/assets/css/';
			
			
			if($app->isSite())
			{
				$files = array('site.css');
			}
			else
			{
				// Define array of the files to load
				if(in_array(strtolower($view), static::$mediaViews))
				{
					$files = array('smoothness/jquery-ui-1.10.4.custom.min.css', 'jquery.fancybox-1.3.4.css', 'icons.css', 'qazap.css');
				}
				else
				{
					$files = array('jquery.fancybox-1.3.4.css', 'icons.css', 'qazap.css');
				}				
			}

			if(count($additionalFiles))
			{
				$files = array_merge($additionalFiles, $files);
			}
			
			foreach($files as $file)
			{
				$doc->addStyleSheet($asset_path.$file);
			}			
	}	
	
	/**
	* Load all required JavaScript and CSS Files for Sidebar
	* 
	* @return null
	*/	
	public static function loadSidebarScripts() 
	{
			$doc = JFactory::getDocument();
			
			foreach($doc->_scripts as $k=>$v)
			{
				if(strpos($k, 'qazap.js', (strlen($k)-8)) !== false)
				{
					unset($doc->_scripts[$k]);
				}
			}
			
			
			$js_path = JURI::base(true).'/components/com_qazap/assets/js/';
			// Define array of the files to load			

			$jsFiles = array('jquery.easing.1.3.min.js', 'qazap.js');
						
			foreach($jsFiles as $js)
			{
				$doc->addScript($js_path.$js);
			}				
	}
	/**
	 * Debug marker for Qazap.
	 *
	 * @author Abhishek Das
	 * @param $name. Name of mark.
	 * @param $var. Optional 
	 */
	public static function mark($name = '', $var = false)
	{
			if(!JDEBUG)
			{
				return;
			}
			
			if(empty($name))
			{
				return;
			}
			
			JProfiler::getInstance('Qazap')->mark($name);
			
			if($var)
			{
				ob_start();
				print_r($var);
				$var = ob_get_contents();
				ob_end_clean();
				$var = '<pre>' . htmlspecialchars($var, ENT_QUOTES) . '</pre>';
				
				$varQueue = array();
				$session = JFactory::getSession();
				$sessionQueue = $session->get('com_qazap.app.var.queue');
				
				if (count($sessionQueue))
				{
					$varQueue = $sessionQueue;			
				}
				
				$varQueue[] = '<div class="qzdebug-name">' . $name . '</div>' . $var;
				
				$session->set('com_qazap.app.var.queue', $varQueue);
			}		
	}
	
	/**
	 * Trace catcher for Qazap.
	 *
	 * @author Abhishek Das
	 * @param $notice. Name of mark.
	 */		
	public static function trace($notice) 
	{
			if(JDEBUG) 
			{
				ob_start();
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				$trace = ob_get_contents();
				ob_end_clean();
				
				$traceQueue = array();
				$session = JFactory::getSession();
				$sessionQueue = $session->get('com_qazap.app.trace.queue');
				
				if (count($sessionQueue))
				{
					$traceQueue = $sessionQueue;			
				}
				
				$traceQueue[] = '<div class="qzdebug-name">' . $notice . '</div>' . $trace;
				
				$session->set('com_qazap.app.trace.queue', $traceQueue);
			}
	}	
	
	public static function calculateSize($mem_usage)
	{
		if ($mem_usage < 1024) 
			return $mem_usage." bytes"; 
		elseif ($mem_usage < 1048576) 
			return round($mem_usage/1024, 2)." KB"; 
		else 
			return round($mem_usage/1048576, 2)." MB";		
	}
	
	/**
	* Method Get User IP Address
	* 
	* @return string IP Address 
	* @since	1.0
	*/	
	public static function getUserIP()
	{ 
		$ip = "UNKNOWN";

		if (isset($_SERVER))
		{
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			{
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} 
			elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$ip = $_SERVER["HTTP_CLIENT_IP"];
			} 
			else 
			{
				$ip = $_SERVER["REMOTE_ADDR"];
			}
		}
		else 
		{
			if (getenv('HTTP_X_FORWARDED_FOR')) 
			{
				$ip = getenv('HTTP_X_FORWARDED_FOR' );
			} 
			elseif (getenv('HTTP_CLIENT_IP'))
			{
				$ip = getenv('HTTP_CLIENT_IP');
			} 
			else 
			{
				$ip = getenv('REMOTE_ADDR');
			}
		}
		
		return (string) $ip;
	}
	
	
}



?>