<?php
/**
 * media.php
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

jimport('joomla.application.component.modellegacy');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelMedia extends JModelLegacy 
{
	
	protected $options;

	protected $error_messages;
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		parent::__construct($config);	
		
		$this->error_messages = array(
									1 => 'COM_QAZAP_MEDIA_ERROR_1',
									2 => 'COM_QAZAP_MEDIA_ERROR_2',
									3 => 'COM_QAZAP_MEDIA_ERROR_3',
									4 => 'COM_QAZAP_MEDIA_ERROR_4',
									6 => 'COM_QAZAP_MEDIA_ERROR_6',
									7 => 'COM_QAZAP_MEDIA_ERROR_7',
									8 => 'COM_QAZAP_MEDIA_ERROR_8',
									'post_max_size' => 'COM_QAZAP_MEDIA_ERROR_POST_MAX_SIZE',
									'max_file_size' => 'COM_QAZAP_MEDIA_ERROR_MAX_FILE_SIZE',
									'min_file_size' => 'COM_QAZAP_MEDIA_ERROR_MIN_FILE_SIZE',
									'accept_file_types' => 'COM_QAZAP_MEDIA_ERROR_ACCEPT_FILE_TYPES',
									'max_number_of_files' => 'COM_QAZAP_MEDIA_ERROR_MAX_NUMBER_OF_FILES',
									'max_width' => 'COM_QAZAP_MEDIA_ERROR_MAX_WIDTH',
									'min_width' => 'COM_QAZAP_MEDIA_ERROR_MIN_WIDTH',
									'max_height' => 'COM_QAZAP_MEDIA_ERROR_MAX_HEIGHT',
									'min_height' => 'COM_QAZAP_MEDIA_ERROR_MIN_HEIGHT'
								);

		$this->setOptions();
	}
	
	
	protected function populateState() 
	{
		// Get the parameters from qazapmedia field form data
		$params = JFactory::getApplication()->getUserState('com_qazap.media.params', array());
		$this->setState('params', $params);

		// Load the parameters.
		$config = JComponentHelper::getParams('com_qazap');
		$this->setState('config', $config);		
	}	

	/**
	* Set Options
	* 
	* @param (array) $options Predfined Options to add
	* @return Sets $this->options
	*/	
	protected function setOptions($options = NULL)
	{
		$this->options = array(
			'script_url' => 'index.php?option=com_qazap&view=media',
			'upload_dir' => JPATH_SITE . '/images/qazap/' . $this->getParam('group', 'images') . '/',
			'upload_url' => '/images/qazap/' . $this->getParam('group', 'images') . '/',
			'user_dirs' => false,
			'param_name' => $this->getParam('param_name', 'files'),
			'mkdir_mode' => 0755,
			// Defines which files (based on their names) are accepted for upload:
			'accept_file_types' => $this->getParam('imagesonly', 1) ? '/\.(gif|jpe?g|png)$/i' : '/.+$/i',
			// The php.ini settings upload_max_filesize and post_max_size
			// take precedence over the following max_file_size setting:
			'max_file_size' => null,
			'min_file_size' => 1,
			// The maximum number of files for the upload directory:
			'max_number_of_files' => null,
			// Image resolution restrictions:
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set the following option to false to enable resumable uploads:
			'discard_aborted_uploads' => true,
			// Set to true to rotate images based on EXIF meta data, if available:
			'orient_image' => false,
			'crop' => $this->getParam('crop', false),
			'image_versions' => array(
				// Uncomment the following version to restrict the size of
				// uploaded images:
				/*
				'' => array(
				'max_width' => 1920,
				'max_height' => 1200,
				'jpeg_quality' => 95
				),
				*/
			)
		);

		if($this->getParam('medium', 1))
		{
			$this->options['image_versions']['medium'] = array(
				'max_width' => $this->getParam('medium_width', 180),
				'max_height' => $this->getParam('medium_height', 180)
			);
		}		

		if($this->getParam('thumbnail', 1))
		{
			$this->options['image_versions']['thumbnail'] = array(
				'max_width' => $this->getParam('thumbnail_width', 90),
				'max_height' => $this->getParam('thumbnail_height', 90)
			);			
		}

		if ($options) 
		{
			$this->options = array_merge($this->options, $options);
		}		
	}

	/**
	* Method to get params
	* 
	* @param (string) $name Name of the param
	* @param $default Default return
	* 
	*/
	protected function getParam($name = NULL, $default = NULL)
	{
		$config = $this->getState('config');
		$params = $this->getState('params');
		
		if(isset($params[$name]))
		{
			$return = $params[$name];
		}
		else
		{
			$return = $config->get($name, $default);
		}		
		return $return;
	}

	protected function get_user_path() 
	{
		if ($this->options['user_dirs']) 
		{
			$user = JFactory::getUser();
			return $user->id . '/';
		}
		return '';
	}

	protected function get_upload_path($file_name = null, $version = null) 
	{
		$file_name = $file_name ? $file_name : '';
		$version_path = empty($version) ? '' : $version . '/';
		return $this->options['upload_dir'] . $this->get_user_path() . $version_path . $file_name;
	}	

	protected function get_query_separator($url) 
	{
		return strpos($url, '?') === false ? '?' : '&';
	}

	protected function get_url($file_name, $version = null) 
	{
		$version_path = empty($version) ? '' : rawurlencode($version).'/';
		return $this->options['upload_url'] . $this->get_user_path() . $version_path . rawurlencode($file_name);
	}

	protected function set_file_remove_properties($file) 
	{
		$file->remove_url = $file->name;
	}
	
	// Fix for overflowing signed 32 bit integers,
	// works for sizes up to 2^32-1 bytes (4 GiB - 1):
	protected function fix_integer_overflow($size) 
	{
		if ($size < 0) 
		{
			$size += 2.0 * (PHP_INT_MAX + 1);
		}
		return $size;
	}

	protected function get_file_size($file_path, $clear_stat_cache = false) 
	{
		if ($clear_stat_cache) 
		{
			clearstatcache(true, $file_path);
		}
		return $this->fix_integer_overflow(filesize($file_path));
	}

	protected function is_valid_file_object($file_name) 
	{
		$file_path = $this->get_upload_path($file_name);
		if (is_file($file_path) && $file_name[0] !== '.') 
		{
			return true;
		}
		return false;
	}

	protected function get_file_object($file_name) 
	{
		if ($this->is_valid_file_object($file_name)) 
		{
			$file = new stdClass();
			$file->name = $file_name;
			$file->size = $this->get_file_size(
				$this->get_upload_path($file_name)
			);
			$file->url = $this->get_url($file->name);
			foreach($this->options['image_versions'] as $version => $options) 
			{
				if (!empty($version)) 
				{
					if (is_file($this->get_upload_path($file_name, $version))) 
					{
						$file->{$version.'_url'} = $this->get_url(
							$file->name,
							$version
						);
					}
				}
			}
			$this->set_file_remove_properties($file);
			return $file;
		}
		return null;
	}

	protected function get_file_objects($iteration_method = 'get_file_object') 
	{
		$upload_dir = $this->get_upload_path();
		if (!is_dir($upload_dir)) 
		{
			return array();
		}
		return array_values(array_filter(array_map(
			array($this, $iteration_method),
			scandir($upload_dir)
		)));
	}

	protected function count_file_objects() 
	{
		return count($this->get_file_objects('is_valid_file_object'));
	}

	protected function create_scaled_image($file_name, $version, $options) 
	{
		$file_path = $this->get_upload_path($file_name);
		if (!empty($version)) 
		{
	    $version_dir = $this->get_upload_path(null, $version);
	    if (!is_dir($version_dir)) 
			{
				mkdir($version_dir, $this->options['mkdir_mode'], true);
	    }
	    $new_file_path = $version_dir.'/'.$file_name;
		} 
		else 
		{
			$new_file_path = $file_path;
		}
		list($img_width, $img_height) = @getimagesize($file_path);
		if (!$img_width || !$img_height) 
		{
			return false;
		}
		$scale = min(
	    $options['max_width'] / $img_width,
	    $options['max_height'] / $img_height
		);
		if ($scale >= 1) 
		{
			if ($file_path !== $new_file_path) 
			{
				return copy($file_path, $new_file_path);
			}
			return true;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);
		
		switch (strtolower(substr(strrchr($file_name, '.'), 1))) 
		{
			case 'jpg':
			case 'jpeg':
				$src_img = @imagecreatefromjpeg($file_path);
				$write_image = 'imagejpeg';
				$image_quality = isset($options['jpeg_quality']) ? $options['jpeg_quality'] : 75;
				break;
			case 'gif':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				$src_img = @imagecreatefromgif($file_path);
				$write_image = 'imagegif';
				$image_quality = null;
				break;
			case 'png':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$src_img = @imagecreatefrompng($file_path);
				$write_image = 'imagepng';
				$image_quality = isset($options['png_quality']) ? $options['png_quality'] : 9;
				break;
			default:
				$src_img = null;
		}
		
		$success = $src_img && @imagecopyresampled(
			$new_img,
			$src_img,
			0, 0, 0, 0,
			$new_width,
			$new_height,
			$img_width,
			$img_height
		) && $write_image($new_img, $new_file_path, $image_quality);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}	

	protected function get_error_message($error) 
	{
		return array_key_exists($error, $this->error_messages) ? JText::_($this->error_messages[$error]) : $error;
	}	
	
	function get_config_bytes($val) 
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) 
		{
			case 'g':
			    $val *= 1024;
			case 'm':
			    $val *= 1024;
			case 'k':
			    $val *= 1024;
		}
		return $this->fix_integer_overflow($val);
	}

	protected function validate($uploaded_file, $file, $error, $index) 
	{
		if ($error) 
		{
			$file->error = $this->get_error_message($error);
			return false;
		}
		
		$content_length = $this->fix_integer_overflow(intval($_SERVER['CONTENT_LENGTH']));
		$post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
		
		if ($post_max_size && ($content_length > $post_max_size)) 
		{
			$file->error = $this->get_error_message('post_max_size');
			return false;
		}

		if (!preg_match($this->options['accept_file_types'], $file->name)) 
		{
			$file->error = $this->get_error_message('accept_file_types');
			return false;
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)) 
		{
			$file_size = $this->get_file_size($uploaded_file);
		} 
		else 
		{
			$file_size = $content_length;
		}
		if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) 
		{
			$file->error = $this->get_error_message('max_file_size');
			return false;
		}
		
		if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) 
		{
			$file->error = $this->get_error_message('min_file_size');
			return false;
		}
		
		if (is_int($this->options['max_number_of_files']) && ($this->count_file_objects() >= $this->options['max_number_of_files'])) 
		{
			$file->error = $this->get_error_message('max_number_of_files');
			return false;
		}
		
		list($img_width, $img_height) = @getimagesize($uploaded_file);
		
		if (is_int($img_width)) 
		{
			if ($this->options['max_width'] && $img_width > $this->options['max_width']) 
			{
				$file->error = $this->get_error_message('max_width');
				return false;
			}
			if ($this->options['max_height'] && $img_height > $this->options['max_height']) 
			{
				$file->error = $this->get_error_message('max_height');
				return false;
			}
			if ($this->options['min_width'] && $img_width < $this->options['min_width']) 
			{
				$file->error = $this->get_error_message('min_width');
				return false;
			}
			if ($this->options['min_height'] && $img_height < $this->options['min_height']) 
			{
				$file->error = $this->get_error_message('min_height');
				return false;
			}
		}
		return true;
	}

	protected function upcount_name_callback($matches) 
	{
		$index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
		$ext = isset($matches[2]) ? $matches[2] : '';
		return ' ('.$index.')'.$ext;
	}

	protected function upcount_name($name) 
	{
		return preg_replace_callback('/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/', array($this, 'upcount_name_callback'), $name, 1);
	}

	protected function get_unique_filename($name, $type, $index, $content_range) 
	{
		while(is_dir($this->get_upload_path($name))) 
		{
			$name = $this->upcount_name($name);
		}
		// Keep an existing filename if this is part of a chunked upload:
		$uploaded_bytes = $this->fix_integer_overflow(intval($content_range[1]));
		while(is_file($this->get_upload_path($name))) 
		{
	    	if ($uploaded_bytes === $this->get_file_size(
				$this->get_upload_path($name))) 
				{
					break;
	   	 		}
	    	$name = $this->upcount_name($name);
		}
		return $name;
	}

	protected function trim_file_name($name, $type, $index, $content_range) 
	{
		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$name = trim(basename(stripslashes($name)), ".\x00..\x20");
		// Use a timestamp for empty filenames:
		if (!$name) 
		{
			$name = str_replace('.', '-', microtime(true));
		}
		// Add missing file extension for known image types:
		if (strpos($name, '.') === false &&
		preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) 
		{
			$name .= '.'.$matches[1];
		}
		return $name;
	}

	protected function get_file_name($name, $type, $index, $content_range) 
	{
		return $this->get_unique_filename(
			$this->trim_file_name($name, $type, $index, $content_range),
			$type,
			$index,
			$content_range
		);
	}

	protected function handle_form_data($file, $index) 
	{
		// Handle form data, e.g. $_REQUEST['description'][$index]
	}

	protected function orient_image($file_path) 
	{
		if (!function_exists('exif_read_data')) 
		{
			return false;
		}
		$exif = @exif_read_data($file_path);
		if ($exif === false) 
		{
			return false;
		}
		$orientation = intval(@$exif['Orientation']);
		if (!in_array($orientation, array(3, 6, 8))) 
		{
			return false;
		}
		$image = @imagecreatefromjpeg($file_path);
		
		switch ($orientation) 
		{
			case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($image);
		return $success;
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) 
	{
		$file = new stdClass();
		$file->name = $this->get_file_name($name, $type, $index, $content_range);
		$file->size = $this->fix_integer_overflow(intval($size));
		$file->type = $type;
		
		if ($this->validate($uploaded_file, $file, $error, $index)) 
		{
			$this->handle_form_data($file, $index);
			$upload_dir = $this->get_upload_path();
			
			if (!is_dir($upload_dir)) 
			{
				mkdir($upload_dir, $this->options['mkdir_mode'], true);
			}
			
			$file_path = $this->get_upload_path($file->name);			
			$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
			
			if ($uploaded_file && is_uploaded_file($uploaded_file)) 
			{
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) 
				{
					file_put_contents($file_path, fopen($uploaded_file, 'r'), FILE_APPEND);
				} 
				else 
				{
					move_uploaded_file($uploaded_file, $file_path);
				}
			} 
			else 
			{
				// Non-multipart uploads (PUT method support)
				file_put_contents($file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
			}
			
			$file_size = $this->get_file_size($file_path, $append_file);
			
			if ($file_size === $file->size) 
			{
				if ($this->options['orient_image']) 
				{
					$this->orient_image($file_path);
				}
				$file->url = $this->get_url($file->name);
				
				foreach($this->options['image_versions'] as $version => $options) 
				{
					if ($this->create_scaled_image($file->name, $version, $options)) 
					{
						if (!empty($version)) 
						{
							$file->{$version.'_url'} = $this->get_url($file->name, $version);
						} 
						else 
						{
							$file_size = $this->get_file_size($file_path, true);
						}
					}
				}
			} 
			else if (!$content_range && $this->options['discard_aborted_uploads']) 
			{
		    	unlink($file_path);
		    	$file->error = 'abort';
			}
			
			$file->size = $file_size;
			$this->set_file_remove_properties($file);
		}
		return $file;
	}

	protected function readfile($file_path) 
	{
		return readfile($file_path);
	}	

	protected function get_file_type($file_path) 
	{
		switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) 
		{
			case 'jpeg':
			case 'jpg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'gif':
				return 'image/gif';
			default:
				return '';
		}
	}

	/**
	* Method to upload @filesource
	* 
	* @author Abhishek Das
	* @param (array) $files File data array
	* 
	*/
	public function upload($upload)
	{
		if(!is_array($upload))
		{
			$this->setError('Invalid file data');
			return false;
		}
		
		$file_name = isset($_SERVER['HTTP_CONTENT_DISPOSITION']) ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $_SERVER['HTTP_CONTENT_DISPOSITION'])) : null;	
		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = isset($_SERVER['HTTP_CONTENT_RANGE']) ? preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']) : null;
		
		$size =  $content_range ? $content_range[3] : null;
		$files = array();
		
		$singleFile = isset($upload['tmp_name']) || isset($upload['name']) || isset($upload['size']) || isset($upload['type']);
		
		if (!$singleFile) 
		{
			// $upload is a multi-dimensional array:
			foreach ($upload as $index => $value) 
			{
				$files[] = $this->handle_file_upload(
					$value['tmp_name'],
					$file_name ? $file_name : $value['name'],
					$size ? $size : $value['size'],
					$value['type'],
					$value['error'],
					$index,
					$content_range
				);
			}
		} 
		else 
		{
			// $upload is a one-dimensional array:
			$files[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				$file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null),
				$size ? $size : (isset($upload['size']) ? $upload['size'] : $_SERVER['CONTENT_LENGTH']),
				isset($upload['type']) ? $upload['type'] : $_SERVER['CONTENT_TYPE'],
				isset($upload['error']) ? $upload['error'] : null,
				null,
				$content_range
			);
		}
		return array($this->options['param_name'] => $files);		
	}


	public function remove($file_name) 
	{
		$response = array();
		$file_path = $this->get_upload_path($file_name);		
		$success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
		if ($success) 
		{
			foreach($this->options['image_versions'] as $version => $options) 
			{
				if (!empty($version)) 
				{
					$file = $this->get_upload_path($file_name, $version);
					if (is_file($file)) 
					{
						unlink($file);
					}
				}
			}
		}
		return array($file_name=>$success);
	}
	
	public static function toArray($data, $decode = true)
	{
		if(is_string($data) && $decode)
		{
			$data = json_decode($data, true);
		}	
			
		if(empty($data))
		{
			return null;
		}
		
		$array = array();
		foreach($data as $key=>$value)
		{
			if(is_object($value))
			{
				$value = self::toArray($value, false);
			}
			$array[$key] = $value;
		}		
		
		return $array;
	} 
	
	protected function isExternal($url)
	{
		$url_host = parse_url($url, PHP_URL_HOST);
		$base_url_host = parse_url(JURI::root(), PHP_URL_HOST);
		 
		if($url_host == $base_url_host || empty($url_host))
		{
		  return false;
		}
		
		return true;
	}
	
	
	protected function getExternalFileType($url)
	{
		if(!function_exists('curl_version'))
		{
			return 'UNKNOWN';
		}
	  	# the request
		if(!$ch = curl_init($url))
		{
			return 'UNKNOWN';
		}
		
	  if(!curl_setopt($ch, CURLOPT_RETURNTRANSFER, true))
		{
			return 'UNKNOWN';
		}
		
	  if(!curl_exec($ch))
		{
			return 'UNKNOWN';
		}

	  # get the content type
	  if(!$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE))
		{
			return 'UNKNOWN';
		}
		
		return $content_type;
		
	  # output
	  //text/html; charset=ISO-8859-1	
		
	}
	
	protected function getInternalFileType($url)
	{
		$path = JPATH_SITE . $url;
		if(!is_file($path))
		{
			return 'UNKNOWN';
		}
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$content_type = finfo_file($finfo, $path);
		finfo_close($finfo);
		return $content_type;
	}
	
	
	public function getFileInfo($url)
	{
		$return = array();
		
		if(!$this->isExternal($url))
		{
			$return['filetype'] = 'INTERNAL';
			$return['type'] = $this->getInternalFileType($url);
		}
		else
		{
			$return['filetype'] = 'EXTERNAL';
			$return['type'] = $this->getExternalFileType($url);			
		}
		
		return $return;
	}

}
