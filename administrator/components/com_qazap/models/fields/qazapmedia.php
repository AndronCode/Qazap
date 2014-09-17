<?php
/**
 * qazapmedia.php
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

/**
 * Form Field class for the Qazap Platform.
 * Supports a one line text field.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       1.0.0
 */
class JFormFieldQazapmedia extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	*
	* @since  1.0.0
	*/
	protected $type = 'Qazapmedia';
	
	protected $_label;
	
	protected $_attributes;
	
	protected $_medium;
	
	protected $_thumbnail;
	
	protected $_multiple;
	
	protected $_fields;
	
	protected $previews = array(
												
	
												);
	/**
	* Method to get the field input markup.
	*
	* @return  string  The field input markup.
	*
	* @since   1.0.0
	*/
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('behavior.keepalive');
		
		JText::script('COM_QAZAP_MEDIA_STATUS_COMPLETED');
		JText::script('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_THUMBNAIL');
		JText::script('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_MEDIUM');
		JText::script('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_THUMBNAIL_MEDIUM');
		JText::script('ERROR');
				
		$doc = JFactory::getDocument();		
		$doc->addStyleSheet(JURI::base(true).'/components/com_qazap/assets/css/qazap.media.css');	
		$doc->addScript(JURI::base(true).'/components/com_qazap/assets/js/jquery.form.min.js');	
		$doc->addScript(JURI::base(true).'/components/com_qazap/assets/js/qazap.media.js');
		
		// Initialize some field attributes.
		$group = isset($this->element['group']) ? trim($this->element['group']) : 'images';
		$imagesOnly = isset($this->element['imagesonly']) ? (int) $this->element['imagesonly'] : 1;
		$this->_thumbnail = isset($this->element['thumbnail']) ? (int) $this->element['thumbnail'] : 1;
		$this->_medium = isset($this->element['medium']) ? (int) $this->element['medium'] : 1;
		$manualUpload = isset($this->element['manual']) ? (int) $this->element['manual'] : 1;
		$crop = isset($this->element['crop']) ? (int) $this->element['crop'] : 0;
		
		$multipleAttr = (isset($this->element['multiple']) && $this->element['multiple']) ? ' multiple="multiple"' : '';
		$this->_multiple = isset($this->element['multiple']) ? $this->element['multiple'] : false;
		$name = base64_encode($this->name);
		$fieldName = $this->element['multiple'] ? 'qzmedia['.$name.'][]' : 'qzmedia['.$name.']';
		
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';
		$this->_attributes = $readonly.$disabled.$required;		
		$this->_label = $this->element['label'] ? (string) $this->element['label'] : '';
		
		$buttonText = $this->_multiple ? JText::_('COM_QAZAP_MEDIA_SELECT_FILES') : JText::_('COM_QAZAP_MEDIA_SELECT_FILE');
		
		$html  = "\t".'<div class="qazapmedia-field">'."\n";
	
		$html .= "\t\t".'<span class="btn btn-success btn-large btn-file"><i class="iconic-document-alt"></i> '.$buttonText.'<input type="file" class="qazap-media-inputbox inputbox" name="'.$fieldName.'" '.$multipleAttr.' data-name="'.$name.'" /></span>'."\n";
		
		$html .= "\t\t".'<button type="button" class="qazap-media-upload-button btn btn-primary btn-large disabled" disabled="disabled" data-name="'.$name.'"><i class="iconic-upload"></i> '.JText::_('COM_QAZAP_MEDIA_UPLOAD_FILES').'</button>'."\n";

		if($manualUpload)
		{
			$html .= "\t\t".'<button type="button" class="qazap-media-manual-upload-button btn btn-warning btn-large" data-name="'.$name.'"><i class="iconic-plus"></i> '.JText::_('COM_QAZAP_MEDIA_SELECT_FILES_BY_URL').'</button>'."\n";
		}
		

		$html .= "\t\t".'<div class="progress progress-striped active hidden">'."\n";
		$html .= "\t\t\t".'<div class="bar" style="width: 0%;"></div>'."\n";
		$html .= "\t\t".'</div>'."\n";
		
		$html .= "\t\t".'<table class="table table-striped qazap-media-preview-tmpl hidden">'."\n";
		$html .= "\t\t\t".'<thead><tr>'."\n";
		$html .= "\t\t\t\t".'<th class="qazap-media-index">'.JText::_('#').'</th>'."\n";
		$html .= "\t\t\t\t".'<th class="qazap-media-name">'.JText::_('COM_QAZAP_MEDIA_NAME').'</th>'."\n";
		$html .= "\t\t\t\t".'<th class="qazap-media-status">'.JText::_('COM_QAZAP_MEDIA_STATUS').'</th>'."\n";
		$html .= "\t\t\t".'</tr></thead>'."\n";	
		$html .= "\t\t\t".'<tbody>'."\n";		

		$html .= "\t\t\t".'</tbody>'."\n";	
		$html .= "\t\t".'</table>'."\n";
		
		
		$html .= "\t".'<input type="hidden" name="qzmedia['.$name.'][group]" value="'.$group.'" />'."\n";
		$html .= "\t".'<input type="hidden" name="qzmedia['.$name.'][imagesonly]" value="'.$imagesOnly.'" />'."\n";
		$html .= "\t".'<input type="hidden" name="qzmedia['.$name.'][thumbnail]" value="'.$this->_thumbnail.'" />'."\n";
		$html .= "\t".'<input type="hidden" name="qzmedia['.$name.'][medium]" value="'.$this->_medium.'" />'."\n";
		$html .= "\t".'<input type="hidden" name="qzmedia['.$name.'][crop]" value="'.$crop.'" />'."\n";

		$default = array('name'=>'', 'url'=>'', 'type'=>'', 'filetype'=>'INTERNAL', 'remove_url'=>'');
		
		if($this->_medium)
		{
			$default['medium_url'] = '';
		}		
		
		if($this->_thumbnail)
		{
			$default['thumbnail_url'] = '';
		}
		
		$this->_fields = array_keys($default);
		
		if(!empty($this->value))
		{
			JLoader::register('QazapModelMedia', QZPATH_MODEL_ADMIN .DS. 'media.php');
			$this->value = QazapModelMedia::toArray($this->value);
		}
		
		$lastIndex = 0;
		
		$html .= "\t".'<div class="qzmedia-preview-container">'."\n";
		if(isset($this->value[0]) && $this->_multiple)
		{
			foreach($this->value as $index=>$array)
			{
				$this->getItemHTML($array, (string) $index, $html);
			}
		}
		elseif(!empty($this->value))
		{
			$this->getItemHTML($this->value, false, $html);
		}		
		$html .= "\t".'</div>'."\n";
		
		
		$this->getTMPL($default, '0', $html);
		
		$html .= "\t".'</div>'."\n";
		
		return $html;

	}

	protected function getItemHTML($array, $index, &$html)
	{
		$name = $this->name;
		if($index !== false)
		{
			$name = str_replace('[]', '['.$index.']', $name);
		}
		$html .= "\t".'<div class="qzmedia-preview-group">'."\n";
		$html .= "\t\t".'<div class="qzmedia-preview-single-item">'."\n";
		$html .= "\t\t\t".'<div class="qzmedia-preview-single-image">'."\n";
		$html .= "\t\t\t\t".$this->preview($array)."\n";
		$html .= "\t\t\t".'</div>'."\n";	
		$html .= "\t\t\t".'<div class="qzmedia-preview-single-details form-horizontal">'."\n";	
		
		foreach($array as $key=>$value)
		{	
			$readonly = ($array['filetype'] == 'INTERNAL' && $key != 'name') ? ' readonly="readonly"' : '';
			
			$type = 'text';
			if(!in_array($key, $this->_fields))
			{
				continue;
			}
			if($key == 'type' || $key == 'filetype' || $key == 'remove_url')
			{
				$type = 'hidden';
			}			
	
			if($type != 'hidden')
			{
				$html .= "\t\t\t\t".'<div class="control-group">'."\n";	
				$html .= "\t\t\t\t\t".'<div class="control-label"><label for="'.$this->id.'_'.$index.'_'.$key.'">'.JText::_($this->_label.'_'.strtoupper($key)).'</label></div>'."\n";
				$html .= "\t\t\t\t\t".'<div class="controls"><input type="'.$type.'" id="'.$this->id.'_'.$index.'_'.$key.'" name="'.$name.'['.$key.']" value="'.$value.'" '.$this->_attributes.' data-type="'.$key.'" data-key="'.$index.'" class="span12" '.$readonly.' /></div>'."\n";			
				$html .= "\t\t\t\t".'</div>'."\n";				
			}
			else
			{
				$html .= "\t\t\t\t".'<input type="'.$type.'" id="'.$this->id.'_'.$index.'_'.$key.'" name="'.$name.'['.$key.']" value="'.$value.'" '.$this->_attributes.' data-type="'.$key.'" data-key="'.$index.'" />'."\n";	
			}			
						
		}
		$html .= "\t\t\t".'</div>'."\n";
		$html .= "\t\t".'</div>'."\n";
		$html .= "\t".'</div>'."\n";			
	}
	
	protected function getTMPL($array, $index, &$html)
	{
		$html .= "\t\t".'<script class="qzmedia-tmpl" type="text/x-tmpl">'."\n";
		$this->getItemHTML($array, $index, $html);
		$html .= "\t\t".'</script>'."\n";
		$html .= "\t\t".'<script class="qzmedia-onupload-msg" type="text/x-tmpl">'."\n";
		$html .= '<div class="alert alert-success">';
		$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
		$html .= '<strong>'.JText::_('NOTICE').'!</strong> '.JText::_('COM_QAZAP_MEDIA_ON_UPLOAD_MESSAGE');
		$html .= '</div>';
		$html .= "\t\t".'</script>'."\n";
		
		$html .= "\t\t".'<script class="qzmedia-ondelete-msg" type="text/x-tmpl">'."\n";
		$html .= '<div class="alert alert-success">';
		$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
		$html .= '<strong>'.JText::_('NOTICE').'!</strong> '.JText::_('COM_QAZAP_MEDIA_ON_DELETE_MESSAGE');
		$html .= '</div>';
		$html .= "\t\t".'</script>'."\n";	
		
		$html .= "\t\t".'<script class="qzmedia-media-preview-format" type="text/x-tmpl">'."\n";
		$html .= "\t\t\t".'<tr class="qazap-media-preview">'."\n";
		$html .= "\t\t\t\t".'<td class="qazap-media-index"></td>'."\n";
		$html .= "\t\t\t\t".'<td class="qazap-media-name"></td>'."\n";
		$html .= "\t\t\t\t".'<td class="qazap-media-status">'.JText::_('COM_QAZAP_MEDIA_STATUS_PENDING').'</td>'."\n";
		$html .= "\t\t\t".'</tr>'."\n";
		$html .= "\t\t".'</script>'."\n";		
					
	}

	protected function preview($media)
	{
		$url = '';
		$thumbnail_url = false;
		$medium_url = false;

		if(!isset($media['type']))
		{
			$url = '';
		}
		elseif(strpos($media['type'], 'image/') == 0)
		{
			if(isset($media['thumbnail_url']))
			{
				$thumbnail_url = JURI::root(true).$media['thumbnail_url'];
			}
			if(isset($media['medium_url']))
			{
				$medium_url = JURI::root(true).$media['medium_url'];
			}
			$url = $thumbnail_url ? $thumbnail_url : JURI::root(true).$media['url'];
			$image_url = JURI::root(true).$media['url'];
		}
		else
		{
			$url = JURI::root(true).$this->previews[$media['type']];
		}
		
		$html  = '<div class="qzmedia-preview-img-cont">'."\n";
		if($media['filetype'] == 'EXTERNAL')
		{
			$html .= "\t".'<div class="qzmedia-preview-img-type">'."\n";
			$html .= "\t\t".'<span class="label label-info">'.JText::_('COM_QAZAP_MEDIA_EXTERNAL').'</span>'."\n";
			$html .= "\t".'</div>'."\n";			
		}		
		$html .= "\t".'<div class="qzmedia-preview-img-inner">'."\n";
		$html .= "\t\t".'<span class="img-helper"></span><img class="qzmedia-preview-img" src="'.$url.'" alt="'.basename($media['name']).'" data-type="preview" data-root="'.JURI::root(true).'" />'."\n";
		$html .= "\t\t".'<div class="qzmedia-preview-img-bottom">'."\n";
		$html .= "\t\t\t".'<div class="btn-group">'."\n";
		if($thumbnail_url)
		{
			$html .= "\t\t\t\t".'<a href="'.$image_url.'" class="btn btn-small btn-inverse qazap-image-fancybox" data-preview="actual" rel="'.$this->id.'_actual">Actual Size</a>'."\n";
		}
		if($medium_url)
		{
			$html .= "\t\t\t\t".'<a href="'.$medium_url.'" class="btn btn-small btn-inverse qazap-image-fancybox" data-preview="medium" rel="'.$this->id.'_medium">Medium Size</a>'."\n";
		}
		$html .= "\t\t\t".'</div>'."\n";
		$html .= "\t\t".'</div>'."\n";		
		$html .= "\t".'</div>'."\n";

		$html .= "\t".'<div class="qzmedia-preview-img-control">'."\n";
		$html .= "\t\t".'<button type="button" onclick="QZMedia.toogleEdit(this)" class="btn btn-small qzmedia-edit-link"><i class="iconic-pen-alt2"></i> '.JText::_('JACTION_EDIT').'</button>'."\n";
		$html .= "\t\t".'<button type="button" onclick="QZMedia.removeMe(this);" class="btn btn-small qzmedia-remove-button" data-preview="remove" data-action="'.$media['remove_url'].'" data-name="'.base64_encode($this->name).'"><i class="iconic-trash-stroke"></i> '.JText::_('COM_QAZAP_REMOVE').'</button>'."\n";

		$html .= "\t".'</div>'."\n";
						
		$html .= '</div>'."\n";
		
		return $html;
	}
	
	protected function isImages($url)
	{
		$extension = substr($url, (strrpos($url, '.') + 1));
	}

}
