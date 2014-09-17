<?php
/**
 * file.php
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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class QZFile extends QZObject
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $instances = array();
	
	protected $_config;	
	
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.0
	 */
	public function __construct($config = array())
	{
		$this->_config = $config;
		return true;
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
	public static function getInstance($config = array())
	{
		$hash = md5(serialize($config));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		self::$instances[$hash] = new QZFile($config);
		
		return self::$instances[$hash];
	}	

	public function createIndexFolder($path)
	{		
		if(JFolder::create($path)) 
		{
			if(!JFile::exists($path . DS . 'index.html'))
			{
				JFile::copy(JPATH_ROOT . DS . 'components' . DS . 'index.html', $path . DS . 'index.html');
			}
			
			return true;
		}
		
		return false;
	}	

	
	private function recurse_copy($src,$dst ) 
	{
		$dir = opendir($src);
		$this->createIndexFolder($dst);

		if(is_resource($dir))
		{
			while(false !== ( $file = readdir($dir)) ) 
			{
				if (( $file != '.' ) && ( $file != '..' )) 
				{
					if ( is_dir($src .DS. $file) ) {
						$this->recurse_copy($src .DS. $file,$dst .DS. $file);
					}
					else {
						if(JFile::exists($dst .DS. $file))
						{
							if(!JFile::delete($dst .DS. $file))
							{
								$app = JFactory::getApplication();
								$app->enqueueMessage('Could not delete '.$dst .DS. $file);
							}
						}
						if(!JFile::move($src .DS. $file,$dst .DS. $file))
						{
							$app = JFactory::getApplication();
							$app->enqueueMessage('Could not move '.$src .DS. $file.' to '.$dst .DS. $file);
						}
					}
				}
			}
			
			closedir($dir);
			if (is_dir($src)) JFolder::delete($src);
		} 
		else 
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('Could not read dir '.$dir.' source '.$src);
		}

	}		
	

}

