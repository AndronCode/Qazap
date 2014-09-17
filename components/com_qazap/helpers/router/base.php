<?php
/**
 * base.php
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
// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

abstract class QazapRouterBase extends JComponentRouterBase
{
	public $suffix = ':';
	
	protected $_app;
	protected $_menu;
	protected $_item;
	protected $_params;
	protected $_advanced;
	protected $_db;
		
	protected static $langs = array();
	protected static $instances = array();
	protected static $layouts = array();

	public function __construct($config = array())
	{
		$this->_app = JFactory::getApplication();
		$this->_menu = $this->_app->getMenu();
		$this->_item = $this->_menu->getActive();
		$this->_params = QZApp::getConfig();
		$this->_advanced = $this->_params->get('sef_advanced_link', 1);
		$this->_db = JFactory::getDbo();		
	}

	/**
	 * Returns a reference to a QZProducts object
	 *
	 * @param   array   $options    An array of options
	 * @param   array   $filters    An array of filters
	 *
	 * @return  QZProducts         QZProducts object
	 *
	 * @since   1.0
	 */
	public static function getInstance($router = "QazapRouter", $config = array())
	{
		$hash = md5(serialize($config));

		if (isset(static::$instances[$hash]))
		{
			return static::$instances[$hash];
		}

		static::$instances[$hash] = new $router($options, $filters);
		
		return static::$instances[$hash];
	}	
	
	protected function loadLang()
	{
		static $loaded = false;
		
		if(!$loaded)
		{
			$language = JFactory::getLanguage();
			$tag = $language->getTag();
			JFactory::getLanguage()->load('com_qazap.sef', JPATH_SITE, $tag, true);
			$loaded = true;	
		}

	}
	
	protected function getSEFLang($key)
	{
		if(!isset(static::$langs[$key]))
		{
			$this->loadLang();
			$sef_tag = 'COM_QAZAP_SEF_' . strtoupper($key);
			$lang = JText::_($sef_tag);
			
			if($lang == $sef_tag || empty($lang))
			{
				$lang = strtolower($key);
			}
			
			static::$langs[$key] = str_replace(array('_', '-'), array('', ''), $lang);
		}
		
		return static::$langs[$key];
	}
	
	protected function isThisKey($part, $key)
	{
		if($this->getSEFLang($key) == $part)
		{
			return true;
		}
		
		return false;
	}
	
	protected function getLayoutByLang($text, $view)
	{
		if(isset(static::$layouts[$view]) && isset(static::$layouts[$view][$text]))
		{
			return static::$layouts[$view][$text];
		}
		
		if(!isset(static::$layouts[$view]))
		{
			static::$layouts[$view] = array();
		}
		
		static::$layouts[$view][$text] = null;
		
		if($fileNames = QZHelper::getLayoutFileNames($view))
		{			
			foreach($fileNames as $fileName)
			{	
				$fileName = JFile::stripExt($fileName);
				
				if($this->getSEFLang($fileName) == $text)
				{
					static::$layouts[$view][$text] = $fileName;
					break;
				}
			}
		}
		
		return static::$layouts[$view][$text];
	}

	
	protected function getLastSplitted($segments, $seperator = null)
	{
		$seperator = $seperator ? $seperator : $this->suffix;
		$last_part = end($segments);
		
		if(strpos($last_part, $seperator) !== false)
		{
			return explode($seperator, $last_part, 2);
		}
		else
		{
			return array($last_part, '');
		}
	}
	
	protected function parseArrayValues($values, $forceInteger = false)
	{
		$values = (array) $values;
		$total = count($values);

		for ($i = 0; $i < $total; $i++)
		{
			$values[$i] = str_replace('-', ':', $values[$i]);
			
			if($forceInteger)
			{
				$values[$i] = (int) $values[$i];
			}
		}	
		
		return $values;
	}
	
	protected function getQueryFromLink($menuItem)
	{
		if(($menuItem instanceof stdClass) && isset($menuItem->link) && !empty($menuItem->link))
		{
			$url = parse_url($menuItem->link);
			
			if(isset($url['query']) && !empty($url['query']))
			{
				$query = array();
				parse_str($url['query'], $query);
				
				if(isset($query['view']))
				{
					return $query;
				}
			}
		}
		
		return null;
	}
}