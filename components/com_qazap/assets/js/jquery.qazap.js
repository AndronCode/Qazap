/**
 * jquery.qazap.js
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
;(function ($) {
		var addToCartPool = {
		};
		var refreshPool = {
		};

		var methods = {
			init: function (options) {

			},
			sorter: function (active, optionUL) {
				var $this = $(this);
				var $optionUL = $this.find(optionUL);
				var $active = $this.find(active);
				var sortconfig = {
					over: function () {
						$(this).addClass('over');
						$optionUL.animate({
								opacity: 1,
								height: 'toggle'
							}, 100);
					},
					timeout: 0,
					out: function () {
						var that = this;
						$optionUL.animate({
								opacity: 0,
								height: 'toggle'
							}, 100, function () {
								$(that).removeClass('over');
							});
					}
				};
				$this.hoverIntent(sortconfig);
				var activeTXT = $optionUL.find('li.active > a').text();
				if (!activeTXT) {
					return;
				}
				$active.text(activeTXT);
				$optionUL.css('min-width', $this.width() - ($optionUL.outerWidth() - $optionUL.width()));
			},
			equalheight: function (itemClass) {
				$(this).each(function() {
						var maxHeight = 0;
						$(this).find(itemClass).each(function () {
								if ($(this).height() >= maxHeight) {
									maxHeight = $(this).height();
								}
							});
						$(this).find(itemClass).css('min-height', maxHeight);
					});
			},
			resetForm: function () {
				$(this).each(function () {
						$(this)[0].reset();
						if ($.fn.raty) {
							$(this).find('.user-product-rating-field').raty('score', 0);
						}
					});
				return false;
			},
			refreshProduct: function () {
				var values, key, $form;
				$(this).each(function () {
						$(this).on('change', function (event) {
								$form = $(this);
								values = $(this).serializeArray();
								$.each(values, function (index, item) {
										if (item.name == 'view') {
											item.value = 'product';
										} else if (item.name == 'task') {
											item.value = 'getselection';
										}
									});
								if (values.length) {
									var product_id = $(this).find('input[name="qzform[product_id]"]').val();
									if (refreshPool.hasOwnProperty(product_id) && refreshPool[product_id].readystate != 4) {
										refreshPool[product_id].abort();
									}
									refreshPool[product_id] = $.ajax({
											dataType: 'json',
											type: 'GET',
											cache: false,
											url: window.qzuri + '?option=com_qazap&view=product&task=getselection&format=json',
											data: $.param(values),
											success: function (data) {
												$.each(data.prices, function (index, value) {
														$('.qazap-ajax-update-' + product_id).find('.' + index + '_value').animate({
																'opacity': 0.5
															}, 100, function () {
																$(this).text(value).animate({
																		'opacity': 1
																	}, 500);
															});
													});
												if($form.find('.qazap-add-to-cart-button').length) {
													if (data.info_msg) {
														$('.qazap-product-info-' + product_id).removeClass('hide').find('.info-msg').text(data.info_msg);
													} else {
														$('.qazap-product-info-' + product_id).addClass('hide').find('.info-msg').text('');
													}
												}
												if($.type(data) == 'string') {
													data = $.parseHTML(data);
												}
												$(document).trigger('productRefresh', [data]);
											}
										});
								}
							});
						$(this).trigger('change');
					});
			},
			addToCart: function () {
				if(window.qzajaxcart) {
					$(this).each(function () {
							$(this).find('.qazap-add-to-cart-button').click(function (event) {
									event.preventDefault();
									if(!document.formvalidator.isValid($(this).parents('form'))) {
										return false;
									}
									var product_id = $(this).parents('form').find('input[name="qzform[product_id]"]').val();
									var values = $(this).parents('form').serializeArray();
									$.each(values, function (index, item) {
											if (item.name == 'view') {
												item.value = 'cart';
											} else if (item.name == 'task') {
												item.value = 'add';
											}
										});
									if (addToCartPool.hasOwnProperty(product_id) && addToCartPool[product_id].readystate != 4) {
										addToCartPool[product_id].abort();
									}
									addToCartPool[product_id] = $.ajax({
											dataType: 'html',
											type: 'GET',
											cache: false,
											url: window.qzuri + '?option=com_qazap&view=cart&task=add&format=raw',
											data: $.param(values),
											beforeSend: function() {
												if ($.fn.fancybox) {
													$.fancybox.showActivity();
												}
											}, 
											success: function (data) {
												if ($.fn.fancybox) {
													$.fancybox({
															'titlePosition': 'inside',
															'transitionIn': 'elastic',
															'transitionOut': 'elastic',
															'easingIn': 'easeOutBack',
															'easingOut': 'easeInBack',
															'type': 'html',
															'padding': 0,															
															'autoCenter': true,
															'closeBtn': false,
															'closeClick': false,
															'content': data,
															'onComplete': function() {
																var newForm = $('.qazap-select-attr-popup').find('form');
																if(newForm.length) {
																	newForm.qazap('refreshProduct');
																	newForm.qazap('addToCart');
																}
															}
														});
												}
												if($.type(data) == 'string') {
													data = $.parseHTML(data);
												}
												// This event is called when cart is updated. Can be used to Ajax update cart module etc.
												$(document).trigger('cartUpdate', [data]);
											},
											error: function() {
												if ($.fn.fancybox) {
													$.fancybox.hideActivity();
												}
											},
											complete: function() {
												if ($.fn.fancybox) {
													$.fancybox.hideActivity();
												}													
											}											
										});
									return false;
								});
						});
				}

			}

		};

		$.fn.qazap = function (methodOrOptions) {
			if (methods[methodOrOptions]) {
				return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
				// Default to "init"
				return methods.init.apply(this, arguments);
			} else {
				$.error('Method ' + methodOrOptions + ' does not exist in jQuery.qazap');
			}
		};

	})(jQuery);