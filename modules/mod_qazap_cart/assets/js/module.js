/**
 * module.js
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Cart Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

;(function (a) {
		var addToCartModPool = {};
		var itemState = 'hidden';

		function initCartModButton() {
			a('.' + window.cart_module_class).find('.cart-module-showproduct').click(function(e){
					if(itemState == 'hidden') {
						itemState = 'shown';
					} else {
						itemState = 'hidden';
					}
					showHideCartModItems();
					return false;
				});
		}

		function showHideCartModItems() {
			var items = a('.' + window.cart_module_class).find('.cart-module-products');
			var button = a('.' + window.cart_module_class).find('.cart-module-showproduct');
			if(itemState == 'shown') {
				items.removeClass('hide');
				button.find('.onhide').addClass('hide');
				button.find('.onshow').removeClass('hide');
			} else {
				items.addClass('hide');
				button.find('.onhide').removeClass('hide');
				button.find('.onshow').addClass('hide');
			}
		}

		a(document).ready(function() {
				a(document).on('cartUpdate', function(event, updateInfo) {
						if (addToCartModPool.hasOwnProperty('update') && addToCartModPool['update'].readystate != 4) {
							addToCartModPool['update'].abort();
						}
						addToCartModPool['update'] = a.ajax({
								dataType: 'html',
								type: 'GET',
								cache: false,
								url: window.qzuri + '?option=com_qazap&view=cart&format=raw&module=mod_qazap_cart&Itemid=' + window.cart_module_itemid,
								success: function (data, textStatus, jqXHR) {
									if(a.type(data) == 'string') {
										data = a.parseHTML(data);
									}
									var html = a(data).filter('.qazap-cart-module').html();
									if(!html) {
										html = data;
									}
									if(!window.cart_module_class) {
										window.cart_module_class = 'qazap-cart-module';
									}
									a('.' + window.cart_module_class).html(html);

									showHideCartModItems();
									initCartModButton();

									a(document).trigger('cartModUpdate', [updateInfo, data]);
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.log(textStatus);
									console.log(errorThrown);
								}
							});
					});
					
				initCartModButton();
			});
	})(jQuery);