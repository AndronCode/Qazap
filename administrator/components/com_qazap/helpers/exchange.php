<?php
/**
 * exchange.php
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
defined('JPATH_PLATFORM') or die;

class QZExchange extends QZObject
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $instances = array();
	
	
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	/**
	 * Returns a reference to a QZExchange object
	 *
	 * @param   array				$config    An array of optional configurations
	 *
	 * @return  QZExchange	object
	 *
	 * @since   1.0
	 */
	public static function getInstance($config = array())
	{
		$hash = md5(serialize($config));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		self::$instances[$hash] = new QZExchange($config);
		
		return self::$instances[$hash];
	}	
	
	public function convert($value, $fromCurrencyCode, $toCurrencyCode)
	{
		if(!$exchange = $this->getExchange($fromCurrencyCode, $toCurrencyCode))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$value = ((float) $value * $exchange);
		
		return $value;
	}	
	
	public function getExchange($fromCurrencyCode, $toCurrencyCode)
	{
		if(!$data = $this->getRates())
		{
			$this->setError($this->getError());
			return false;
		}
		
		$fromRate = isset($data['rates'][$fromCurrencyCode]) ? (float) $data['rates'][$fromCurrencyCode] : 1;
		$toRate = isset($data['rates'][$toCurrencyCode]) ? (float) $data['rates'][$toCurrencyCode] : 1;
		
		$value = ($toRate / $fromRate);
		
		return $value;		
	}
	
	public function getRates()
	{
		$config = QZApp::getConfig();
		$source = $config->get('exchange_source', 'ecb');
		$fncName = 'get' . ucfirst(strtolower($source)) . 'Rates';
		
		if (!method_exists($this, $fncName))
		{
			$this->setError(__METHOD__ . ' -- Unknown method: ' . $fncName);
			return false;
		}		

		$cache = JFactory::getCache('com_qazap_exchange', 'callback');
		$cache->setLifeTime(86400/4); // check 4 time per day
		$cache->setCaching(1); //enable caching		
		$data = $cache->call(array($this, $fncName), $fncName);
		
		return $data;		
	}	

	public function getEcbRates() 
	{
		$return['rates'] = array('EUR' => 1.0);
		$return['source'] = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
		$return['information'] = 'http://www.ecb.int/stats/eurofxref/';
		$return['supplier'] = 'European Central Bank';

		// Retrieve the feed from the source
		$connector = QZConnector::getInstance(array('url' => $return['source']));
		
		if(!$feed = $connector->retrieveData())
		{
			$this->setError($connector->getError());
			return false;
		}

		$xmlDoc = new DomDocument();
		
		try
		{
			$result = $xmlDoc->loadXML($feed);
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!$result) 
		{
			$this->setError('Failed to loadXML().');
			return false;
		}
		
		$currency_list = $xmlDoc->getElementsByTagName('Cube');
		$length = $currency_list->length;
		
		for ($i = 0; $i <= $length; $i++) 
		{
			$currNode = $currency_list->item($i);
			if(!empty($currNode) && !empty($currNode->attributes->getNamedItem('currency')->nodeValue))
			{
				$return['rates'][$currNode->attributes->getNamedItem('currency')->nodeValue] = $currNode->attributes->getNamedItem('rate')->nodeValue;
				unset($currNode);
			}
		}	

		return $return;
	}
	
	
	
	public function getYahooRates()
	{
		$return['rates'] = array('USD' => 1.0);
		$return['source'] = 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote;currency=true?view=basic&format=json';
		$return['information'] = 'http://finance.yahoo.com/';
		$return['supplier'] = 'Yahoo Finance';

		// Retrieve the feed from the source
		//$connector = QZConnector::getInstance(array('url' => $return['source']));
		
		if(!$feed = file_get_contents($return['source']))
		{
			$this->setError('Failed to load Yahoo exchange rates');
			return false;
		}

		$feed = json_decode($feed, true);
		
		// check for valid JSON
		if ($errors = json_last_error()) 
		{
		  switch ($errors) 
		  {
				case JSON_ERROR_NONE:
					$this->setError(' - No errors');
				break;
				case JSON_ERROR_DEPTH:
					$this->setError(' - Maximum stack depth exceeded');
				break;
				case JSON_ERROR_STATE_MISMATCH:
					$this->setError(' - Underflow or the modes mismatch');
				break;
				case JSON_ERROR_CTRL_CHAR:
					$this->setError(' - Unexpected control character found');
				break;
				case JSON_ERROR_SYNTAX:
					$this->setError(' - Syntax error, malformed JSON');
				break;
				case JSON_ERROR_UTF8:
					$this->setError(' - Malformed UTF-8 characters, possibly incorrectly encoded');
				break;
				default:
					$this->setError(' - Unknown error');
				break;
		  }
		  return false;
		}

		foreach ($feed['list']['resources'] as $r) 
		{
			foreach ($r['resource'] as $key => $val) 
			{
				if ($key === 'fields') 
				{
					if (stripos($val['name'], '/') !== false) 
					{
						$return['rates'][(string) substr(trim($val['name']), -3)] = (float) $val['price'];
					}
				}
			}
		}
				
		return $return;
	}

}