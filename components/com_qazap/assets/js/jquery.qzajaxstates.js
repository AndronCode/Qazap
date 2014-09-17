/**
 * jquery.qzajaxstates.js
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

;(function($) {
        var methods = {
            init: function(settings) {
                return this.each(function() {
                        this.opt = $.extend(true, {}, $.fn.qzajaxstates.defaults, settings);
                        var self = this,
                            that = $(self);
                        methods.bindOnChange.call(self);
                    });
            },
            bindOnChange: function() {
                var self = this,
                    that = $(self);
                var countryField = that.parents(self.opt.parent).find(self.opt.countryField);
                countryField.on('change', function() {
                        methods.select.call(self);
                    }).trigger('change');
            },
            select: function() {
                var self = this,
                    that = $(self);
                methods.destroy.call(self);
                methods.getstates.call(self);
            },
            destroy: function() {
                var self = this,
                    that = $(self);
                that.find('optgroup').remove();
                that.find('option').remove();
            },
            getstates: function() {
                var self = this,
                    that = $(self);
                var thatLabel = $('label[for="' + that.attr('id') + '"]');
                var country = that.parents('form').find(self.opt.countryField);
                var countryId = country.val();
                var countryName = country.find('option[value="' + countryId + '"]').text();
                if(countryId > 0) {
                    $.getJSON(window.qzuri + '?option=com_qazap&view=states&format=json&country_id=' + countryId, function(data) {
                            if(!data.options) {
                                that.append(data.selector);
                                that.removeAttr('required').removeClass('invalid');
                                thatLabel.removeClass('required').removeClass('invalid').find('.star').remove();
                            } else {
                                var group = '<optgroup id="country-' + countryId + '" label="' + countryName + '">' + data.options + '</optgroup>';
                                that.append(data.selector + group);
                                that.attr('required', 'required');
                                thatLabel.addClass('required');
                                if(thatLabel.find('.star').length == 0) {
                                    thatLabel.append('<span class="star">&nbsp;*</span>');
                                }
                            }
                            if(self.opt.stateId) {
                                that.find('option[value="' + self.opt.stateId + '"]').attr('selected','selected');
                            }
                            that.trigger('liszt:updated');
                        });
                }
            }
        };

        $.fn.qzajaxstates = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if (typeof method === 'object' || !method) {
                return methods.init.apply(this, arguments);
            } else {
                $.error('Method ' + method + ' does not exist!');
            }
        };

        $.fn.qzajaxstates.defaults = {
            stateId       : '0',
            countryField  : '.qazap-country-field',
            optgroup      : true,
            parent        : 'form'
        };

    })(jQuery);