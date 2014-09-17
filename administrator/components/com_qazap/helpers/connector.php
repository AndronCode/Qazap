<?php
/**
 * connector.php
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

class QZConnector extends QZObject
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $instances = array();
	
	protected $url = null;
	protected $headers = null;
	protected $cached_file_path = null;	
	protected $proxy_url = null;
	protected $proxy_port = null;
	protected $proxy_user = null;
	protected $proxy_pass = null;
	protected $proxy_cert_path = null;
	protected $curl = false;
	
	
	public function __construct($config = array())
	{
		if(!empty($config))
		{
			$this->setProperties($config);
		}
		
		if(function_exists('curl_init') && function_exists('curl_exec'))
		{
			$this->curl = true;
		}
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

		self::$instances[$hash] = new QZConnector($config);
		
		return self::$instances[$hash];
	}		

	public function retrieveData($postData = null)
	{
		if(!$this->url)
		{
			$this->setError('No url available to retrive data');
			return false;
		}
		
		$urlParts = parse_url($this->url);
		$urlParts['port'] = isset($urlParts['port']) ? (int) $urlParts['port'] : null;
		$urlParts['scheme'] = isset($urlParts['scheme']) ? $urlParts['scheme'] : 'http';
		$urlParts['query'] = isset($urlParts['query']) ? '?' . $urlParts['query'] : null;
		$urlParts['path'] = isset($urlParts['path']) ? ($urlParts['path'] . $urlParts['query']) : null;
		
		if($this->proxy_url)
		{
			if(!stristr($this->proxy_url, 'http'))
			{
				$this->proxy_url = array();
				$this->proxy_url['host'] = $this->proxy_url;
				$this->proxy_url['scheme'] = 'http';				
			}
			else
			{
				$this->proxy_url = parse_url($this->proxy_url);
			}
		}
		
		$result = false;

		if($this->curl)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
			
			if($this->headers) 
			{
				// Add additional headers if provided
				curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
	    }
	    
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			
	    if($postData) 
	    {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($ch, CURLOPT_POST, 1);
	    }
	    
			if($this->cached_file_path && is_resource($this->cached_file_path)) 
			{
				curl_setopt($ch, CURLOPT_FILE, $this->cached_file_path);
	    } 
	    else 
	    {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    }
	    
	    if($this->proxy_url) 
	    {
				//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
				curl_setopt($ch, CURLOPT_PROXY, $this->proxy_url['host'] );
				curl_setopt($ch, CURLOPT_PROXYPORT, VM_PROXY_PORT );
				// Check if the proxy needs authentication
				if($this->proxy_user) 
				{
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_user . ':' . $this->proxy_pass);
				}
			}

			if($urlParts['scheme'] == 'https') 
	    {
				// No PEER certificate validation...as we don't have
				// a certificate file for it to authenticate the host www.ups.com against!
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
				if($this->proxy_cert_path)
				{
					curl_setopt($ch, CURLOPT_SSLCERT , $this->proxy_cert_path);
				}
			}
			
	    $result = curl_exec($ch);
	    $error = curl_error($ch);
	    
	    if(!empty($error) && stristr($error, '502') && !empty($this->proxy_url)) 
	    {
				curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM );
				$result = curl_exec($ch);
				$error = curl_error($ch);
	    }
	    
	    curl_close($ch);

	    if(!empty($error)) 
	    {
				$this->setError($error);
				return false;
	    } 	
		}
		else
		{
			if($postData) 
			{
				if(!empty($this->proxy_url)) 
				{
			    if($this->proxy_url['scheme'] == 'https') 
			    {
						$protocol = 'ssl';
			    }
			    else 
			    {
						$protocol = 'http';
			    }
			    
					$fp = fsockopen("$protocol://" . $this->proxy_url['host'], $this->proxy_port, $errorCode, $errorMsg, $timeout = 30);
				}
				else 
				{
					if($urlParts['scheme'] == 'https') 
					{
						$protocol = 'ssl';
					}
					else 
					{
						$protocol = $urlParts['scheme'];
					}
					
					$fp = fsockopen("$protocol://" . $urlParts['host'], $urlParts['port'], $errorCode, $errorMsg, $timeout = 30);
				}
			} 
			else 
			{
				if(!empty($this->proxy_url)) 
				{
					$fp = fopen($this->proxy_url['scheme'] . '://' . $this->proxy_url['host'] . ':' . $this->proxy_port, 'rb');
				}
				else 
				{
					if($urlParts['port'])
					{
						$fp = fopen($urlParts['scheme'] . '://' . $urlParts['host'] . ':' . $urlParts['port'] . $urlParts['path'], 'rb');
					}
					else
					{
						$fp = fopen($urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'], 'rb');
					}
				}
			}

			if(!$fp) 
			{
				$this->setError('QZConnector::retrieveData() - Server error! - ' . $errorMsg . '(' . $errorCode . ')');
				return false;
			}
			
			if($postData) 
			{
				//send the server request
				if(!empty($this->proxy_url)) 
				{
			    fputs($fp, 'POST ' . $urlParts['host'] . ':' . $urlParts['port'] . $urlParts['path'] . " HTTP/1.0\r\n");
			    fputs($fp, 'Host: ' . $this->proxy_url['host'] . "\r\n");

					if($this->proxy_user) 
			    {
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode($this->proxy_user . ':' . $this->proxy_pass) . "\r\n\r\n");
					}
				}
				else 
				{
					fputs($fp, 'POST ' . $urlParts['path'] . " HTTP/1.0\r\n");
					fputs($fp, 'Host:' . $urlParts['host'] . "\r\n");
				}
				
				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: " . strlen($postData) . "\r\n");
				fputs($fp, "Connection: close\r\n\r\n");
				fputs($fp, $postData . "\r\n\r\n");
			} 
			else 
			{
				if(!empty($this->proxy_url)) 
				{echo 'test';exit;
					fputs($fp, "GET ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, "Host: ".$proxyURL['host']."\r\n");
					if($this->proxy_user) 
					{
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode($this->proxy_user . ':' . $this->proxy_pass) . "\r\n\r\n");
					}
				}
				else 
				{
					fputs($fp, 'GET ' . $urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, 'Host:' . $urlParts['host']."\r\n");
				}
			}
			
			if(!empty($this->headers)) 
			{
				foreach($this->headers as $header) 
				{
					fputs($fp, $header . "\r\n");
				}			
			}

			$data = '';
			while(!feof($fp)) 
			{
				$data .= @fgets($fp, 4096);
			}
			
			fclose($fp);
			$result = trim($data);
			
			if(!$result) 
			{
				$this->setError('An error occured while communicating with the server '.$urlParts['host'].'. It didn\'t reply (correctly). Please try again later, thank you.' );
				return false;
			}
			
			if($this->cached_file_path && is_resource($this->cached_file_path)) 
			{
				fwrite($this->cached_file_path, $result);
			}
		}
		
		return $result;
	}	

// 	var $archive = true;
// 	var $last_updated = '';



	/**
	 * Converts an amount from one currency into another using
	 * the rate conversion table from the European Central Bank
	 *
	 * @param float $amountA
	 * @param string $currA defaults to $vendor_currency
	 * @param string $currB defaults to
	 * @return mixed The converted amount when successful, false on failure
	 */
// 	function convert( $amountA, $currA='', $currB='', $a2b = true ) {
	public static function convert( $amountA, $currA='', $currB='', $a2rC = true, $relatedCurrency = 'EUR') {
		
		$document_address = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

		$info_address = 'http://www.ecb.int/stats/eurofxref/';
		$supplier = 'European Central Bank';		

		// cache subfolder(group) 'convertECB', cache method: callback
		$cache= JFactory::getCache('QZExchange','callback');

		// save configured lifetime
		@$lifetime=$cache->lifetime;

		$cache->setLifeTime(86400/4); // check 4 time per day


		$cache->setCaching(1); //enable caching

		$globalCurrencyConverter = $cache->call( array( 'QZExchange', 'getSetExchangeRates' ),$document_address );


		if(!$globalCurrencyConverter ){
			//vmdebug('convert convert No $globalCurrencyConverter convert '.$amountA);
			return $amountA;
		} else {
			$valA = isset( $globalCurrencyConverter[$currA] ) ? $globalCurrencyConverter[$currA] : 1.0;
			$valB = isset( $globalCurrencyConverter[$currB] ) ? $globalCurrencyConverter[$currB] : 1.0;

			$val = (float)$amountA * (float)$valB / (float)$valA;
			//vmdebug('convertECB with '.$currA.' '.$amountA.' * '.$valB.' / '.$valA.' = '.$val,$globalCurrencyConverter[$currA]);

			return $val;
		}
	}

	static function getSetExchangeRates($ecb_filename){

			$archive = true;
			setlocale(LC_TIME, "en-GB");
			$now = time() + 3600; // Time in ECB (Germany) is GMT + 1 hour (3600 seconds)
			if (date("I")) {
				$now += 3600; // Adjust for daylight saving time
			}
			$weekday_now_local = gmdate('w', $now); // week day, important: week starts with sunday (= 0) !!
			$date_now_local = gmdate('Ymd', $now);
			$time_now_local = gmdate('Hi', $now);
			$time_ecb_update = '1415';
			if( is_writable(JPATH_BASE.DIRECTORY_SEPARATOR.'cache') ) {
				$store_path = JPATH_BASE.DIRECTORY_SEPARATOR.'cache';
			}
			else {
				$store_path = JPATH_SITE.DIRECTORY_SEPARATOR.'media';
			}

			$archivefile_name = $store_path.'/daily.xml';

			$val = '';


			if(file_exists($archivefile_name) && filesize( $archivefile_name ) > 0 ) {
				// timestamp for the Filename
				$file_datestamp = date('Ymd', filemtime($archivefile_name));

				// check if today is a weekday - no updates on weekends
				if( date( 'w' ) > 0 && date( 'w' ) < 6
				// compare filedate and actual date
				&& $file_datestamp != $date_now_local
				// if localtime is greater then ecb-update-time go on to update and write files
				&& $time_now_local > $time_ecb_update) {
					$curr_filename = $ecb_filename;
				}
				else {
					$curr_filename = $archivefile_name;
					$last_updated = $file_datestamp;
					$archive = false;
				}
			}
			else {
				$curr_filename = $ecb_filename;
			}

			if( !is_writable( $store_path )) {
				$archive = false;
				vmError( "The file $archivefile_name can't be created. The directory $store_path is not writable" );
			}
			//			JError::raiseNotice(1, "The file $archivefile_name should be in the directory $store_path " );
			if( $curr_filename == $ecb_filename ) {
				// Fetch the file from the internet
				if(!class_exists('QazapConnector')) require(__DIR__.DIRECTORY_SEPARATOR.'connection.php');
				//				JError::raiseNotice(1, "Updating currency " );
				if (!$contents = QazapConnector::handleCommunication( $curr_filename )) {
					if (isset($file_datestamp)) {
						$contents = @file_get_contents( $curr_filename );
					}
				} else $last_updated = date('Ymd');

			}
			else {
				$contents = @file_get_contents( $curr_filename );
			}
			if( $contents ) {
				// if archivefile does not exist
				if( $archive ) {
					// now write new file
					file_put_contents( $archivefile_name, $contents );
				}

				$contents = str_replace ("<Cube currency='USD'", " <Cube currency='EUR' rate='1'/> <Cube currency='USD'", $contents);

				/* XML Parsing */
				$xmlDoc = new DomDocument();

				if( !$xmlDoc->loadXML($contents) ) {
					//todo
					vmError('Failed to parse the Currency Converter XML document.');
					vmError('The content: '.$contents);
					//					$GLOBALS['product_currency'] = $vendor_currency;
					return false;
				}

				$currency_list = $xmlDoc->getElementsByTagName( "Cube" );
				// Loop through the Currency List
				$length = $currency_list->length;
				for ($i = 0; $i < $length; $i++) {
					$currNode = $currency_list->item($i);
					if(!empty($currNode) && !empty($currNode->attributes->getNamedItem("currency")->nodeValue)){
						$currency[$currNode->attributes->getNamedItem("currency")->nodeValue] = $currNode->attributes->getNamedItem("rate")->nodeValue;
						unset( $currNode );
					}

				}
				$globalCurrencyConverter = $currency;
			}
			else {
				$globalCurrencyConverter = false;
				vmError( 'Failed to retrieve the Currency Converter XML document.');
// 				return false;
			}

			return $globalCurrencyConverter;
	}

}
// pure php no closing tag
