/**
 * qazap.js
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
if(typeof Qazap === 'undefined') {
	var jq = jQuery.noConflict();
	
	var Qazap = {	
		spinnervars : function() {
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
			loader_spinner = new Spinner(SpinnerOpts).spin();
		},
		addloader: function(addprocess) {
			if(typeof loader_spinner === 'undefined')
			{
				Qazap.spinnervars();
			}
			console.log(addprocess);			
			if(jq(addprocess).find('.qazap-loader').length == 0) {
				var loaderHTML = '<div class="qazap-loader"><span></span></div>';
				jq(addprocess).append(loaderHTML);
				jq('.qazap-loader > span').append(loader_spinner.el);
			}
		},
		removeloader: function(removeprocess) {
			if(jq(removeprocess).find('.qazap-loader').length > 0) {
				jq(removeprocess).find('.qazap-loader').remove();
			}
		},			
		deleteMe: function(element) {
			if(jq(element).hasClass('undo-delete')) {
				jq(element).parents('tr').removeClass('error');
				jq(element).parents('tr').find('input:not(.calculated-price), select').removeAttr('disabled');
				jq(element).removeClass('undo-delete').html('<i class=\"icon-cancel\" title="Delete"></li>');	
				jq(element).parents('tr').find('input.delete-info:not(:disabled)').attr('disabled', 'disabled');						
			} else {											
				var inputs = jq(element).parents('tr').find('input[type=\"hidden\"]');
				jq(inputs).each(function() {
					if(jq(this).attr('name').indexOf('[id]') >= 0) {
						var deleteInput = jq(this).clone()
						var deleteName = jq(deleteInput).attr('name');
						deleteName = deleteName.replace('[id]','[deleteID]');
						deleteInput = jq(deleteInput).attr('name',deleteName).addClass('delete-info');
						if(jq(this).siblings('.delete-info').length == 0) {
							jq(deleteInput).insertAfter(this);
						}								
					}
					if(jq(this).attr('name').indexOf('[quantity_price_id]') >= 0) {
						var deleteInput = jq(this).clone()
						var deleteName = jq(deleteInput).attr('name');
						deleteName = deleteName.replace('[quantity_price_id]','[deleteID]');
						deleteInput = jq(deleteInput).attr('name',deleteName).addClass('delete-info');
						if(jq(this).siblings('.delete-info').length == 0) {
							jq(deleteInput).insertAfter(this);
						}								
					}					
				});
				jq(element).parents('tr').addClass('error');					
				jq(element).parents('tr').find('input:not(.delete-info), select').attr('disabled', 'disabled');
				if(jq(element).parents('tr').find('input.delete-info').is(':disabled')) {
					jq(element).parents('tr').find('input.delete-info').removeAttr('disabled');
				}	
				jq(element).addClass('undo-delete').html('<i class=\"icon-undo\" title="Undo Delete"></li>');						
			}
			if(typeof minimum_purchase_quantity !== 'undefined' && typeof maximum_purchase_quantity !== 'undefined') {
				Qazap.quantityPricing(minimum_purchase_quantity, maximum_purchase_quantity);
			}			
		},
		multiple_pricing: function(type) {
			var tab = ['#standard_pricing', '#usergroup_pricing', '#quantity_pricing'];
			var value = jq(type).val();
			jq('.pricing-pane').removeClass('active');
			console.log(tab[value]);
			jq(tab[value]).addClass('active');
		},
		add_pricing_row: function(table) {
			var lastRow = jq(table).find('tr:last').clone();
			lastRow.removeClass('error');
			lastRow.find('input').each(function() {
				var key = jq(this).data('index');
				var newKey = parseInt(key) + 1;
				jq(this).attr('name', jq(this).attr('name').replace(key, newKey));
				jq(this).attr('id', jq(this).attr('id').replace(key, newKey));
				jq(this).attr('data-index', newKey);
				jq(this).val('');
				if(!jq(this).hasClass('calculated-price')) jq(this).removeAttr('disabled');
				jq(this).removeAttr('readonly');
			});
			jq(table).append(lastRow);
			if(typeof minimum_purchase_quantity !== 'undefined' && typeof maximum_purchase_quantity !== 'undefined') {
				Qazap.quantityPricing(minimum_purchase_quantity, maximum_purchase_quantity);
			}
		},
		unsetzero: function(elements) {
			jq(elements).each(function(){
				if(jq(this).val() == 0) {
					jq(this).val('');
				}
			});
			jq(elements).change(function(){
				if(jq(this).val() == 0) {
					jq(this).val('');
				}
				if(jq(this).val() < 0) {
					var field_name = jq('#'+jq(this).attr('id')+'-lbl').text();
					var cont = jq('<div/>').text(Joomla.JText._('COM_QAZAP_NO_NEGATIVE_ALERT'));
					Qazap.setAlert(jq(cont).text().replace('%s', field_name));
				}
				jq(this).val(parseInt(jq(this).val().replace('-', '')));
				if(jq(this).val() == 'NaN') jq(this).val('');
				if(typeof minimum_purchase_quantity !== 'undefined' && typeof maximum_purchase_quantity !== 'undefined') {
					Qazap.quantityPricing(minimum_purchase_quantity, maximum_purchase_quantity);
				}
			});			
		},
		checkcustomprice: function() {
			jq('.input-product-custom-price').change(function(){
				if(jq(this).val() < 0) {
					var field_name = jq(jq(this).attr('id')+'-lbl').text();
					if(field_name == NaN || field_name == null || field_name == '') {
						var col = jq(this).parent('td').prevAll().length;
						field_name = jq(this).parents('table').find('th').eq(col).find('span').text();
					}
					var cont = jq('<div/>').text(Joomla.JText._('COM_QAZAP_NO_NEGATIVE_ALERT'));
					Qazap.setAlert(jq(cont).text().replace('%s', field_name));
					jq(this).val(parseFloat(jq(this).val().replace('-', '')));
					return false;
				}				
				if(isNaN(jq(this).val())) jq(this).val('');
			});
			return true;		
		},
		quantityPricing: function(minQty, maxQty) {
			Qazap.clearAlert();
			if(!Qazap.checkcustomprice())
			{
				Qazap.qtyPriceChecker(minQty, maxQty);
				return false;
			}
			Qazap.clearAlert();
			Qazap.qtyPriceChecker(minQty, maxQty);
			jq('#quantity_pricing_table').find('input').change(function(){
				Qazap.clearAlert();
				Qazap.qtyPriceChecker(minQty, maxQty);
			});
		},
		qtyPriceChecker: function(minQty, maxQty) {
			var productMinQty = jq('#jform_minimum_purchase_quantity').val();
			var productMaxQty = jq('#jform_maximum_purchase_quantity').val();
			if(productMinQty) {
				minQty = parseInt(productMinQty);
			}
			if(productMaxQty) {
				maxQty = parseInt(productMaxQty);
			}
			jq('#quantity_pricing_table').find('tbody td.min-quantity-col input').removeAttr('readonly');				
			jq('#quantity_pricing_table').find('tbody tr:not(.error):first').find('td.min-quantity-col input:first').val(minQty).attr('readonly', true);
			jq('#quantity_pricing_table').find('tbody td.max-quantity-col input:read-only').removeAttr('readonly').val('');
			jq('#quantity_pricing_table').find('tbody tr:not(.error):last').find('td.max-quantity-col input:last').val(maxQty).attr('readonly', true);
			jq('#quantity_pricing_table input').removeClass('input-error');
			jq('td.max-quantity-col input:not(:disabled):not([readonly])').each(function(){
				var thisValue = jq(this).val(); 
				var thisMinQty = jq(this).parents('tr').find('td.min-quantity-col input').val();
				if(!Qazap.validateMaxQty(jq(this),thisValue, thisMinQty, minQty, maxQty))
				{
					return false;
				}
			});
			
			jq('td.min-quantity-col input:not(:disabled):not([readonly])').each(function(){
				var thisValue = jq(this).val();
				var thisMaxQty = jq(this).parent().next('td.max-quantity-col').find('input').val();
				var prevMaxQty = Qazap.findPrevMax(jq(this).parents('tr'));
				if(!Qazap.validateMinQty(jq(this),thisValue, thisMaxQty, prevMaxQty, maxQty))
				{
					return false;
				}
			});
		},
		findPrevMax: function(element) {
			var prevRow = element.prev();
			if(prevRow.length == 0)
			{
				return;
			}			
			else if(!prevRow.hasClass('error'))
			{
				return prevRow.find('td.max-quantity-col input').val();
			}
			else
			{
				return Qazap.findPrevMax(prevRow);
			}
		},
		validateMaxQty: function(element, thisValue, thisMinQty, minQty, maxQty) {
			if(isNaN(parseInt(thisValue))) 
			{
				element.addClass('input-error');
				Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_INVALID_QUANTITY_ALERT'), 'error');
				return false;
			}
			else 
			{
				thisValue = parseInt(thisValue);
				if(isNaN(parseInt(thisMinQty))) thisMinQty = 0;
				if(thisMinQty !== 'undefined' && thisValue <= thisMinQty) 
				{					
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MAXIMUM_QUANTITY_ALERT'), 'error');
					return false;
				}
				else if(minQty !== 'undefined' && thisValue <= minQty)
				{
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MAXIMUM_QUANTITY_ALERT'), 'error');
					return false;					
				}
				else if(maxQty !== 'undefined' && thisValue >= maxQty)
				{
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MAXIMUM_QUANTITY_ALERT'), 'error');
					return false;								
				}
			}
			return true;			
		},
		validateMinQty: function(element, thisValue, thisMaxQty, prevMaxQty, maxQty) {
			if(isNaN(parseInt(thisValue))) 
			{
				element.addClass('input-error');
				Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_INVALID_QUANTITY_ALERT'), 'error');
				return false;
			}			
			else 
			{
				thisValue = parseInt(thisValue);
				if(isNaN(parseInt(thisMaxQty))) thisMaxQty = 0;
				if(isNaN(parseInt(prevMaxQty))) prevMaxQty = 0;
				if(thisValue >= thisMaxQty)
				{
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MINIMUM_QUANTITY_ALERT'), 'error');
					return false;	
				}
				else if(thisValue >= maxQty)
				{
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MINIMUM_QUANTITY_ALERT'), 'error');
					return false;		
				}
				else if(thisValue <= prevMaxQty)
				{
					element.addClass('input-error');
					Qazap.setAlert(Joomla.JText._('COM_QAZAP_PRODUCT_MINIMUM_QUANTITY_ALERT'), 'error');
					return false;		
				}							
			}	
			return true;	
		},
		setAlert: function(text, type) {
			if(typeof type === 'undefined'){
				type = 'warning';
			}
			var html = '<div class="alert alert-'+type+'">';
			html += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
  		html += '<strong>'+Qazap.ucfirst(type)+'!</strong> '+text;
			html += '</div>';
			jq('#qazap-system-message-box').append(html);
			jq('html,body').animate({scrollTop: jq('#qazap-system-message-box').offset().top-100}, 300);
		},
		clearAlert: function() {
			jq('#qazap-system-message-box').html('');
		},
		ucfirst: function(str) {
			str += '';
			var f = str.charAt(0).toUpperCase();
			return f + str.substr(1);			
		},
		fancybox: function() {
			if(jq.fn.fancybox) {
				jq('.qazap-image-fancybox').fancybox({
					centerOnScroll: true,
					transitionIn: 'elastic',
					transitionOut: 'elastic'
				});				
			}

		},
		langTab: function() {
			jq('[data-qazap="tab-set"]').find('.qz-language-selector').click(function(e) {
				e.preventDefault();
				var $parent = jq(this).parents('[data-qazap="tab-set"]');
				$parent.find('.qz-language-selector').removeClass('active');
				jq(this).addClass('active');
				var tab = jq(this).attr('rel');
				$parent.find('.qz-language-tab-pane').removeClass('active');
				$parent.find(tab).addClass('active').css('margin-left', '-500px').animate({'margin-left': 0}, 100);
				return false;
			});
		},
		sidebar: function() {
			jq('#qazap-sidemenu .qzsidemenu-header').not('.home').find('a').click(function() {
				jq(this).find('.qzsidebar-arrow').toggleClass('qzarrow-down qzarrow-left', 1000);
				jq(this).parent().next('.qzsidemenu-items').slideToggle('fast', 'easeInOutQuad');
				return false;
			});
/*			if(this.isDesktop())
			{
				var screenHeight = jq('body').height();
				var offset = jq('#qazap-sidebar').offset().top;
				jq('#qazap-sidemenu').outerHeight(screenHeight - (offset + 35));
				jq('#qazap-sidemenu').jScrollPane({
					showArrows: true,
					verticalArrowPositions: 'os',
				});
			}*/
		},
		orders: function() {
			jq('#qazap-orders').find('.qazap-order-title > a').click(function(e) {
				e.preventDefault();
				jq(this).toggleClass('active');
				jq(this).find('.qzsidebar-arrow').toggleClass('qzarrow-down qzarrow-left', 1000);
				var toOpen = jq(this).attr('href');
				jq(toOpen).slideToggle('fast', 'easeInOutQuad', function() {
					jq(this).toggleClass('opened closed');
					Qazap.orderheaderHeights();
				});
				return false;
			});			
		},
		orderheaderHeights: function() {
			jq('#qazap-orders').find('.qazap-order-contents').each(function(){
				var biggestHeight = 0;
				jq(this).find('.header-inner').not('.heightAdjusted').each(function() {
					if(jq(this).height() > biggestHeight)
					{
						biggestHeight = jq(this).height();
					}
				});
				if(biggestHeight > 0)
					jq(this).find('.header-inner').height(biggestHeight).addClass('heightAdjusted');
			})
		},
		isDesktop: function() {
			return !('ontouchstart' in window) // works on most browsers 
				|| !('onmsgesturechange' in window); // works on ie10			
		},
		validatePath: function(inputbox) {
			Qazap.checkPath(inputbox);		
			//setup before functions
			var typingTimer;                //timer identifier
			var doneTypingInterval = 1000;  //time in ms, 5 second for example
			var value;

			//on keyup, start the countdown
			jq(inputbox).keyup(function() {
			    clearTimeout(typingTimer);
			    typingTimer = setTimeout(function() {
			    	value = jq(inputbox).val();
			    	Qazap.checkPath(inputbox);
			    }, doneTypingInterval);
			});

			//on keydown, clear the countdown 
			jq(inputbox).keydown(function(){
			    clearTimeout(typingTimer);
			});
			
			jq(inputbox).change(function(){
				Qazap.checkPath(this);
			});
		},
		checkPath: function(inputbox) {
			var value = jq(inputbox).val();
			var hidden_field = jq(inputbox).attr('id') + '_hidden'
			var mark = jq(inputbox).attr('id') + '-mark'
			jq.getJSON('index.php?option=com_qazap&task=validate.path&format=json&path='+encodeURIComponent(value),							
								function(data) {
									if(data.valid) {
										jq(inputbox).removeClass('invalid');
										jq('#'+mark).addClass('hide');
										value = value.replace(/\\$|\/$/g, '');
										jq('#'+hidden_field).val(value);
									} else {
										jq(inputbox).addClass('invalid');
										jq('#'+mark).removeClass('hide');
									}
								}
							);			
		}
		
		
	}
	
	jq(document).ready(function() {
		Qazap.fancybox();
		Qazap.langTab();
		Qazap.sidebar();
		Qazap.orders();
    if(jq.fn.fancybox) {
			jq('.fancybox-popup').fancybox({
				'type': 'inline',
				'cyclic': true,
				'titlePosition': 'over',
				'padding': 0,
        'autoSize': false,
        'closeClick': false,
        'openEffect': 'none',
        'closeEffect': 'none',
        'showCloseButton': false,
        'onCleanup': function () {        	
            var myContent = this.href;
            jq(myContent).unwrap();
        }

			});
			jq('.qazap-popup-close').click(function() {
			    parent.jq.fancybox.close();
			});                            
		} 
		
		jq(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
		    Qazap.orderheaderHeights();
		});		
	});
	

}
