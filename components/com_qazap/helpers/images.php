<?php
/**
 * images.php
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

defined('JPATH_BASE') or die;

/**
 * Utility class for Qazap images
 *
 * @package     Qazap.Frontend
 * @subpackage  Helpers
 * @since       1.0
 */
abstract class QZImages
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $items = array();
	
	protected static $_dependenciesLoaded = false;

	public static function display($images = array(), $options = array())
	{
		$options['single'] = isset($options['single']) ? $options['single'] : false;
		$options['gallery'] = isset($options['gallery']) ? $options['gallery'] : true;
		$options['modal'] = isset($options['modal']) ? $options['modal'] : true;
		$options['cloud_zoom'] = isset($options['cloud_zoom']) ? $options['cloud_zoom'] : true;
		$options['class'] = isset($options['class']) ? $options['class'] : null;
		$options['gallery_rel'] = 'qazap-gallery-' . time();
		
		if(is_object($images))
		{
			$tmp = $images;
			$images = array();
			$images[] = $tmp;
			unset($tmp);
		}
		elseif(empty($images))
		{
			$images = array();
			$images[] = self::getNoImageObject();
		}
		
		$images = self::processImageURL($images);
		
		$layout = new JLayoutFile('qazap.images.images', null, $options);
		return $layout->render($images);
	}
	
	public static function displaySingleImage($images, $options = array())
	{
		$options = (array) $options;
		$options['single'] = true;
		$options['gallery'] = false;
		$options['modal'] = isset($options['modal']) ? $options['modal'] : false;
		$options['cloud_zoom'] = isset($options['cloud_zoom']) ? $options['cloud_zoom'] : false;
		$options['class'] = isset($options['class']) ? $options['class'] : null;

		if(!empty($images) && is_string($images))
		{
			$tmp = $images;
			$images = array();
			$images[0] = self::processSingleImageFile($tmp);		
		}
		
		if(is_array($images) && isset($images['url']))
		{
			$tmp = (object) $images;
			$images = array($tmp);
		}
		
		return self::display($images, $options);		
	}

	public static function loadDependencies()
	{
		if(!self::$_dependenciesLoaded)
		{
			$css = array('jquery.fancybox-1.3.4.css');
			$js = array('jquery.fancybox-1.3.4.pack.js', 'cloud-zoom.1.0.2.modified.js');		

			QZApp::loadCSS($css);			
			QZApp::loadJS($js);
			
			self::$_dependenciesLoaded = true;		
		}
	}
	
	protected static function processImageURL($images)
	{
		$images = (array) $images;
		
		if(!count($images))
		{
			return false;
		}
		
		foreach($images as &$image)
		{
			if(isset($image->filetype) && strtolower($image->filetype) == 'internal')
			{
		    $image->url = JURI::base(true) . $image->url;
		    $image->medium_url = isset($image->medium_url) ? $image->medium_url : $image->thumbnail_url;			
		    $image->medium_url = JURI::base(true) . $image->medium_url;
		    $image->thumbnail_url = JURI::base(true) . $image->thumbnail_url;				
			}
		}
		
		return $images;
	}
	
	public static function getNoImageObject()
	{
		$config = QZApp::getConfig();
		$default = 'images/qazap/no-image.jpg';
		$no_image = '/' . $config->get('no_image_file', $default);
		
		$image = new stdClass;		
    $image->name = JText::_('COM_QAZAP_NO_IMAGE_AVAILABLE');
    $image->url = $no_image;
    $image->type = 'image/jpeg';
    $image->filetype = 'INTERNAL';
    $image->remove_url = NULL;
    $image->medium_url = $no_image;
    $image->thumbnail_url = $no_image;
    $image->no_image = true;
    
    return $image;
	}	
	
	protected static function processSingleImageFile($imageFile)
	{
		$file = basename($imageFile);
		
		if(strpos($file, '.'))
		{
			$parts = explode($file);
			$extn = end($parts);
			$fileName = str_replace('.' . $extn, '', $file);
		}
		else
		{
			$fileName = $file;
		}
		
		$image = new stdClass;
		$image->name = $fileName;
		$image->url = $imageFile;
		$image->thumbnail_url = $image->url;
		$image->medium_url = $image->url;
		
		$mediaModel = QZApp::getModel('media');		
		$fileInfo = $mediaModel->getFileInfo($imageFile);
		
		$image->filestype = $fileInfo['filetype'];
		$image->type = $fileInfo['type'];		
		
		return $image;
	}	
}
