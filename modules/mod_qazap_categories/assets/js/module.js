/**
 * module.js
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Categories Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

if (typeof jQ === 'undefined' || typeof jQ === undefined) {
  var jQ = jQuery.noConflict();
}
jQ(document).ready(function(){
  jQ('.qazap-categories-module').each(function(){
    jQ(this).find('li').each(function(){
      if(jQ(this).hasClass('active')) {
        jQ(this).parents('li').addClass('active').addClass('parent');
        jQ(this).find('.category-children').removeClass('hide').addClass('show');
        jQ(this).parents('.category-children').removeClass('hide').addClass('show').siblings('.category-toggler').addClass('active');
      }
      else {
        jQ(this).parents('li').addClass('parent');
        jQ(this).find('.category-children').removeClass('show').addClass('hide');      
      }
    });
    jQ(this).find('.category-toggler').on('click', function() {
      jQ(this).toggleClass('active', 50);
      jQ(this).siblings('.category-children').slideToggle(50, function() {
        jQ(this).toggleClass('show hide');
      });
    });       
  });
});