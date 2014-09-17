/**
 * qazap.media.js
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
if(typeof qz === 'undefined') {
	qz = jQuery.noConflict();
}

qz(function() {
/*!
 * jQuery - .scrollTo()
 *
 *  Author:
 *  Timothy A. Perez
 *
 * Date: OCT 2012
 * Comments: Setting new web standards...
 */
	// .scrollTo - Plugin
	qz.fn.scrollTo = function( target, options, callback ){
	  if(typeof options == 'function' && arguments.length == 2){ callback = options; options = target; }
	  var settings = qz.extend({
		scrollTarget  : target,
		offsetTop     : 150,
		duration      : 500,
		easing        : 'swing'
	  }, options);
	  return this.each(function(){
		var scrollPane = qz(this);
		var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : qz(settings.scrollTarget);
		var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + scrollPane.scrollTop() - settings.offsetTop;
		scrollPane.animate({scrollTop : scrollY }, settings.duration, settings.easing, function(){
		  if (typeof callback == 'function') { callback.call(this); }
		});
	  });
	}
});

if(typeof QZMedia === 'undefined') {
	
	var QZMedia = new function() {
		
		this.fieldClass = '.qazapmedia-field';
		
		this.form = null;
		this.media_spinner = null;
		
		this.onSelect = function(inputbox) {
			var data = qz(inputbox).prop('files');
			var $parent = this._getParentByChildren(inputbox);
			this.resetUploadBtn($parent);
			this.form = qz(inputbox).parents('form');
			if(data.length) {
				$parent.find('table.qazap-media-preview-tmpl tbody').find('.preview-item').remove();
				this.resetProgress($parent);
				$parent.find('table.qazap-media-preview-tmpl').removeClass('hidden');
				qz.each(data, function(index, value) {					
					var $prevTmpl = $parent.find('script.qzmedia-media-preview-format');					
					var $prevContainer = $parent.find('table.qazap-media-preview-tmpl tbody');
					if($prevTmpl.length) {
						qz($prevContainer).append($prevTmpl.html());
						$prevContainer.find('tr:last').attr('data-index', index).addClass('preview-item');
						$prevContainer.find('tr:last').find('.qazap-media-index').text((index+1));
						$prevContainer.find('tr:last').find('.qazap-media-name').text(value.name);
					}					
				});							
			}
		};
		
		this.manualUpload = function(btn) {
			var $parent = this._getParentByChildren(btn);
			var scriptTMPL = $parent.find('script.qzmedia-tmpl').html();
			var $previewCont = $parent.find('.qzmedia-preview-container');
			var $tmpl = $previewCont.append(scriptTMPL);
			var $lastItem = $tmpl.find('.qzmedia-preview-group:last').addClass('editing');
			$lastItem.find('input').removeAttr('readonly');
			$lastItem.find('input[data-type="filetype"]').val('MANUAL');
			$previewCont.trigger('sortupdate');
			Qazap.fancybox();
	    qz('html, body').animate({
	        scrollTop: $lastItem.offset().top
	    }, 500);			
			return false;			
		}
		
		this.upload = function(btn) {
			var $parent = this._getParentByChildren(btn);
			var $prevContainer = $parent.find('table.qazap-media-preview-tmpl tbody');
			var $bar = $parent.find('.bar');
			var $inputbox = $parent.find('.qazap-media-inputbox');
			var param_name = qz(btn).data('name');
			var msg = '';
			qz(this.form).ajaxSubmit({
				dataType: 'json',
				data: {'qzmedia[param_name]' : $inputbox.data('name')},
				beforeSubmit: function(array, $form, options) { 
			    qz.each(array, function(index, value){
						if(value.name == 'task') {
							array[index].value = 'media.upload';
							return false;
						}
					});
					$bar.width('0%');
					QZMedia.addloader($prevContainer.find('td.qazap-media-status'));
				},
				uploadProgress: function(event, position, total, percentComplete) {
					var percentVal = percentComplete + '%';
					$bar.width(percentVal)
				},							
				success: function showResponse(responseText, statusText, xhr, $form)  { 
					QZMedia.resetInput($parent);
					var $previewCont = $parent.find('.qzmedia-preview-container');
					var scriptTMPL = $parent.find('script.qzmedia-tmpl').html();
					var success = false;
					var failed = false;
					qz.each(responseText[param_name], function(index, file){
						var $previewItem = $prevContainer.find('tr:eq('+index+')');
						if(!file.error)
						{
							if(!file.thumbnail_url && !file.medium_url)
							{
								msg = Joomla.JText._('COM_QAZAP_MEDIA_STATUS_COMPLETED');
							}
							else if(!file.medium_url && file.thumbnail_url)
							{
								msg = Joomla.JText._('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_THUMBNAIL');
							}
							else if(file.medium_url && !file.thumbnail_url)
							{
								msg = Joomla.JText._('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_MEDIUM');
							}
							else
							{
								msg = Joomla.JText._('COM_QAZAP_MEDIA_STATUS_COMPLETED_WITH_THUMBNAIL_MEDIUM');
							}
							var tmpl = $previewCont.append(scriptTMPL);							
							qz.each(file, function(name, value){
								tmpl.find('input[data-type="' + name + '"]:last').val(value);
							});
							var previewElement = tmpl.find('img[data-type="preview"]:last');
							var root = previewElement.data('root');
							if(file.thumbnail_url)
							{
								previewElement.attr('src', root + file.thumbnail_url);
							}
							else
							{
								previewElement.attr('src', root + file.url);
							}
							if(file.thumbnail_url)
							{
								tmpl.find('a[data-preview="actual"]:last').attr('href', root + file.url);
							}	
							if(file.medium_url)
							{
								tmpl.find('a[data-preview="medium"]:last').attr('href', root + file.medium_url);
							}	
							if(file.remove_url)
							{
								tmpl.find('button[data-preview="remove"]:last').attr('data-action', root + '/'+ file.remove_url);
							}
							
							QZMedia.removeloader($previewItem.find('td.qazap-media-status'));
							$previewItem.addClass('success');
							$previewItem.find('td.qazap-media-status').html(msg);	
												
							success = true;																						
						}
						else
						{
							QZMedia.removeloader($previewItem.find('td.qazap-media-status'));
							msg = '<span class="label label-error">'+Joomla.JText._('ERROR')+'</span> '+ file.error;
							$previewItem.addClass('error');
							$previewItem.find('td.qazap-media-status').html(msg);
							failed = true;
						}
					});
					if(failed && !success)
					{
						$bar.width('0%');
					}
					if(success)
					{
						$previewCont.trigger('sortupdate');
						Qazap.fancybox();
						qz('#qzmedia-message-box').html($parent.find('.qzmedia-onupload-msg').html());
						qz('html,body').animate({scrollTop: jq('#qzmedia-message-box').offset().top-100}, 300);						
					}
					
				},
				error: function() {
					QZMedia.removeloader($prevContainer.find('td.qazap-media-status'));
					$bar.width('0%');
					QZMedia.resetInput($parent);
				}
			});
		};
		
		this.calculateKeyOrder = function($parent)
		{
			$parent.find('.qzmedia-preview-group').each(function(index, element) {
				var $group = qz(this);
				var order = index.toString();
				qz(this).find('input').each(function() {
					var $field = qz(this);
					var oldName = $field.attr('name');
					var oldID = $field.attr('id');
					var key =  $field.attr('data-key').toString();
					var $label = $group.find('label[for="'+oldID+'"]');
					$field.attr('name', oldName.replace('\['+key+']', '['+order+']', 'g'));
					$field.attr('id', oldID.replace('_'+key+'_', '_'+order+'_', 'g'));
					$label.attr('for', oldID.replace('_'+key+'_', '_'+order+'_', 'g'));
					$field.attr('data-key', order);		
				});
			});
		}
		
		this._getParentByChildren = function(element) {
			return qz(element).parents(this.fieldClass);			
		};
		
		this._getParentByName = function(param_name) {
			var inputbox = qz('input[data-name="' + param_name + '"]:file');			
			return inputbox.parents(this.fieldClass);
		};
		
		this.setSortable = function(element) {
			element.sortable();
			element.on('sortupdate', function(event, ui) {
				var $parent = QZMedia._getParentByChildren(this);
				QZMedia.calculateKeyOrder($parent);
			});				
		};
		
		this.toogleEdit = function(element) {
			var edit = qz(element).parents('.qzmedia-preview-group').toggleClass('editing');
			qz('body').scrollTo(edit);
			return false;
		};
		
		this.resetInput = function(parent) {
			parent.find('.qazap-media-inputbox').val('').change();
			this.resetUploadBtn(parent);			
		}
		
		this.resetUploadBtn = function(parent) {
			var files = parent.find('.qazap-media-inputbox').val();
			if(!files)
			{
				parent.find('.qazap-media-upload-button').addClass('disabled').attr('disabled', 'disabled');
			}
			else
			{
				parent.find('.qazap-media-upload-button').removeClass('disabled').removeAttr('disabled');
			}			
		}	
		
		this.resetProgress = function(parent) {
			parent.find('.progress .bar').hide().width('0%').show();
			var files = parent.find('.qazap-media-inputbox').val();
			if(!files)
			{
				parent.find('.progress').addClass('hidden');
			}
			else
			{
				parent.find('.progress').removeClass('hidden');
			}			
		}	
		
		this.removeMe = function(btn) {
			var $parent = this._getParentByChildren(btn);
			qz(btn).parents('.qzmedia-preview-group').remove();
			var $previewCont = $parent.find('.qzmedia-preview-container');
			$previewCont.trigger('sortupdate');
			qz('#qzmedia-message-box').html($parent.find('.qzmedia-ondelete-msg').html());
			qz('html,body').animate({scrollTop: jq('#qzmedia-message-box').offset().top-100}, 300);
/*			var $parent = this._getParentByChildren(btn);
			var $bar = $parent.find('.bar');
			var $myParent = qz(btn).parents('.qzmedia-preview-group');
			var param_name = qz(btn).data('name');
			var fileName = qz(btn).data('action');
			this.form = qz(btn).parents('form');
			qz(this.form).ajaxSubmit({
				dataType: 'json',
				data: {
					'qzmedia[param_name]' : param_name,
					'qzmedia[remove_file]' : fileName
					},
				beforeSubmit: function(array, $form, options) { 
			    qz.each(array, function(index, value){
						if(value.name == 'task') {
							array[index].value = 'media.remove';
							return false;
						}
					});
					$bar.width('0%');
				},
				uploadProgress: function(event, position, total, percentComplete) {
					var percentVal = percentComplete + '%';
					$bar.width(percentVal)
				},							
				success: function showResponse(responseText, statusText, xhr, $form)  {
					console.log(responseText);
					$bar.width('0%');
					if(responseText[fileName])
					{							
						$myParent.remove();								
					}
					//$previewCont.trigger('sortupdate');
				},
				error: function() {
					$bar.width('0%');
				}
			});*/
		};	


		this.setSpinner = function() {
			// Button Process Spinner
			var SpinnerOpts = {
				lines: 13, // The number of lines to draw
				length: 3, // The length of each line
				width: 2, // The line thickness
				radius: 5, // The radius of the inner circle
				corners: 1, // Corner roundness (0..1)
				rotate: 0, // The rotation offset
				direction: 1, // 1: clockwise, -1: counterclockwise
				color: '#000', // #rgb or #rrggbb or array of colors
				speed: 1.5, // Rounds per second
				trail: 60, // Afterglow percentage
				shadow: false, // Whether to render a shadow
				hwaccel: false, // Whether to use hardware acceleration
				className: 'qazap-spinner', // The CSS class to assign to the spinner
				zIndex: 2e9, // The z-index (defaults to 2000000000)
				top: 'auto', // Top position relative to parent in px
				left: 'auto' // Left position relative to parent in px
			};
			if(!this.media_spinner)
			{
				this.media_spinner = new Spinner(SpinnerOpts).spin();
			}
			
		};		
		
		this.addloader = function(addprocess) {
			var element = addprocess.html('<div class="qazap-media-loader"><span></span></div>');
			element.find('.qazap-media-loader > span').append(QZMedia.media_spinner.el);
		};
		
		this.removeloader = function(removeprocess) {
			qz(removeprocess).each(function() {
					if(qz(this).find('.qazap-media-loader').length) {
						qz(this).find('.qazap-media-loader').remove();
					}
				});
		};			
		
	}
	
	
	
	qz(document).ready(function(){
		QZMedia.setSpinner();
		qz('.qazap-media-inputbox').change(function() {
			QZMedia.onSelect(this);
		});
		qz('.qazap-media-upload-button').on('click', function() {
			QZMedia.upload(this);
		});
		qz('.qazap-media-manual-upload-button').click(function(e){
			e.preventDefault();
			QZMedia.manualUpload(this);			
		});			
		QZMedia.setSortable(qz('.qzmedia-preview-container'));
		var parentForm = qz('.qazapmedia-field').parents('form');
		if(qz('#qzmedia-message-box').length === 0)
		{
			qz('<div id="qzmedia-message-box"></div>').insertBefore(parentForm);
		}		
	});
	
}


