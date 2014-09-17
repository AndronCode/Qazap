/**
 * site.js
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
if (typeof jQ === 'undefined') {
    var jQ = jQuery.noConflict();
}
jQuery(document).ready(function () {
    jQ('.qazap-product-sorter').qazap('sorter', '.active-sorter', '.qazap-sorter-options');
    if (jQ.fn.raty) {
        jQ('.average-product-rating, .user-product-rating, .js-rating').raty({
            'score': function () {
                return jQ(this).attr('data-score');
            },
            'path': window.qzpath + 'components/com_qazap/assets/images/',
            'readOnly': true
        });
        jQ('.user-product-rating-field').raty({
            'path': window.qzpath + 'components/com_qazap/assets/images/',
            'scoreName': 'qzform[rating]'
        });
    }
    jQ('.qazap-product-prices').hover(
        function () {
            jQ(this).children('.qazap-sales-price-wrap').fadeTo(500, 1, function () {
                jQ(this).addClass('active');
            }).end().children('.qazap-product-price-breakup').fadeIn(200).show();
        },
        function () {
            jQ(this).children('.qazap-sales-price-wrap').fadeTo(500, 1, function () {
                jQ(this).removeClass('active');
            }).end().children('.qazap-product-price-breakup').fadeOut(200).hide();
        }
    );
    jQ('.qazap-addtocart-form').qazap('refreshProduct');
    jQ('.qazap-addtocart-form').qazap('addToCart');
    jQ('.qazap-addtocart-form-list').qazap('addToCart');
    jQ('[class*="qazap-product-info-"]').each(function () {
        jQ(this).find('.close').click(function () {
            jQ(this).parents('.alert').addClass('hide');
        });
    });
    if(jQ.fn.fancybox) {
			jQ('.fancybox-popup').fancybox({
				'type': 'inline',
				'cyclic': true,
				'autoDimensions': true,
				'transitionIn': 'fade',
				'transitionOut': 'fade',
				'speedIn': 400,
				'speedOut': 400,
				'scrolling': 'no',	
				'autoCenter': true,		
				'padding': 0,
        'autoSize': false,
        'closeClick': false,
        'openEffect': 'none',
        'closeEffect': 'none',
        'showCloseButton': false,
        'onCleanup': function () {        	
            var myContent = this.href;
            jQ(myContent).unwrap();
        }		

			});
			jQ('.qazap-popup-close').click(function() {
			    parent.jQ.fancybox.close();
			});                            
		}
		
		jQ('label.checkout-userbox-switch').find('input').on('change', function(){
			var process = jQ(this).val();
			if(process == 'qzregister') {
				jQ('#qzregister').removeClass('hide');
				jQ('#qzguest, #reg-advantages').addClass('hide');
			} else {
				jQ('#qzregister').addClass('hide');
				jQ('#qzguest, #reg-advantages').removeClass('hide');				
			}
		});
		if(jQ('.row-fluid .address-container').length) {
			var $row = jQ('.address-container').parent('.row-fluid')
			$row.each(function() {
				jQ(this).qazap('equalheight', '.user-address > .address');
				jQ('.add-new-box.address-container').each(function() {
					var old = jQ(this).find('.address').height();
					jQ(this).find('.address').css('min-height', (old + 32));
					jQ(this).find('.address > a').css('padding-top', ((old + 32) / 2));
				});					
			});		
		}

});
jQuery(window).load(function () {    
    jQ('.qazap-product-list').qazap('equalheight', '.qazap-product-list-item-image');
    jQ('.qazap-product-list').qazap('equalheight', '.qazap-product-list-item-bottom');
    jQ('.qazap-product-list').qazap('equalheight', '.qazap-product-list-item-inner');
    jQ('.qazap-category-list').qazap('equalheight', '.category-list-item-inner .image-cont');
    jQ('.qazap-category-list').qazap('equalheight', '.category-list-item-title');
    jQ('.qazap-category-list').qazap('equalheight', '.category-list-item-inner');    
    jQ('.checkout-userbox').qazap('equalheight', '.inner');
    jQ('.qazap-brand-list').qazap('equalheight', '.qazap-brand-list-item .image-container');
    jQ('.qazap-brand-list').qazap('equalheight', '.qazap-brand-list-item .brand-list-item-inner');
    jQ('.qazap-shop-list').qazap('equalheight', '.shop-list-item-inner');
});