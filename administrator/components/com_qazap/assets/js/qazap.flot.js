/**
 * qazap.flot.js
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
if(typeof jQ === 'undefined' || typeof jQ === undefined) {
	var jQ = jQuery.noConflict();
}	
jQ(document).ready(function() {
	var $plotCont = jQ('.qz-plot-container');
	var modal = $plotCont.parents('.modal-plot-wrap').length;
	var maxHeight;
	if(!modal) {
		maxHeight = $plotCont.parents('.well').siblings('.well').find('.row-striped').outerHeight();
		if(maxHeight < 180) {
			maxHeight = 181;
		}		
	} else {
		$plotCont.parents('body').css({'overflow' : 'hidden'});
		maxHeight = $plotCont.parents('body').height() - 200;
	}

	$plotCont.outerHeight(maxHeight - 10);
	
	jQ('#qz-plot-more').fancybox({
		'width'						: '90%',
		'height'					: '90%',
		'autoScale'				: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'						: 'iframe'
	});	
});
jQ(window).load(function() {
	var options = {
		lines: {
			show: true,
			fill: true,
			fillColor: { colors: [{ opacity: 0.5 }, { opacity: 0.1}] }
		},
		points: {
			show: true
		},
    grid: {
        hoverable: true,
        clickable: true
    },		
		xaxis: {mode: "time", timeformat: "%Y-%m-%d"},
		colors: ['#3a87ad', '#08c']
	};

	var series = {};
	
	jQ.ajax({
		url: window.qzuri + '?option=com_qazap&view=home&format=json&layout=history',
		type: "GET",
		beforeSend: function() {
			jQ('#qz-plot-nodata').hide();
			jQ('#qz-plot-loading').show();
			showPlotMsg();
		},
		dataType: "json",
		success: function(e) {
			jQ('#qz-plot-loading').hide();
			if(!e.error) {
				series = jQ(e);
				var data = jQ(e.data.totals);
				jQ.plot("#qzPlotHolder", data, options);
				if(!jQ(e.data.totals.data).length) {
					jQ('#qz-plot-nodata').show();
					showPlotMsg();
				}					
			}		
		}
	});
	
	function showPlotMsg() {
		jQ('.qz-msg-inner').each(function(){
			jQ(this).css({
				'margin-left' : - jQ(this).outerWidth() / 2,
				'opacity' : 1
				});
		});
	}
	
	jQ('#qz-plot-actions').find('a').on('click', function() {
		var action = jQ(this).data('action');
		var data;
		if(series.length) {
			if(action == 'value') {
				data = jQ(series[0].data.totals);
			} else if(action == 'count') {
				data = jQ(series[0].data.counts);
			}
			if(data) {
				jQ.plot("#qzPlotHolder", data, options);
				jQ(this).addClass('active').siblings().removeClass('active');
				return false;				
			}
		}		
	});
	
	var previousPoint;
	
	jQ("#qzPlotHolder").bind("plothover", function (event, pos, item) {
	    if (item) {
	        if (previousPoint != item.dataIndex) {
	            previousPoint = item.dataIndex;
	            jQ("#tooltip").remove();
	            var x = item.datapoint[0].toFixed(2),
	            y = item.datapoint[1].toFixed(2);
	            showTooltip(item.pageX, item.pageY, y);
	        }
	    } else {
	        jQ("#tooltip").remove();
	        previousPoint = null;            
	    }
	});

	function showTooltip(x, y, contents) {
	    var tooltip = jQ('<div id="tooltip" class="tooltip top in"><div class="tooltip-arrow"></div><div class="tooltip-inner">' + contents + '</div></div>').css({
	        position: 'absolute',
	        display: 'none',
	        top: y + 5,
	        left: x + 5,
	    }).appendTo('body');
	    
	    var height = tooltip.outerHeight();
	    var width = tooltip.outerWidth();
	    
			tooltip.css({
				top: y - ( 5 + height),
				left: x - (width / 2)
			}).fadeIn(200);
	}

	jQ('#qz-plot-period').on('change', function(){
		var period = jQ(this).val();
		var dynOptions = options;
		if(period == 'last1year') {
			dynOptions.xaxis.timeformat = "%b-%Y";
			dynOptions.xaxis.minTickSize = [1, "month"];
		}
		jQ('#qz-plot-actions').find('a').removeClass('active');
		jQ('#qz-plot-actions').find('a[data-action="value"]').addClass('active');
		
		jQ.ajax({
			url: window.qzuri + '?option=com_qazap&view=home&format=json&layout=history&period=' + period,
			type: "GET",
			beforeSend: function() {
				jQ('#qz-plot-nodata').hide();
				jQ('#qz-plot-loading').show();
				showPlotMsg();
			},			
			dataType: "json",
			success: function(e) {
				jQ('#qz-plot-loading').hide();
				if(!e.error) {
					series = jQ(e);
					var data = jQ(e.data.totals);
					jQ.plot("#qzPlotHolder", data, dynOptions);
					if(!jQ(e.data.totals.data).length) {
						jQ('#qz-plot-nodata').show();
						showPlotMsg();
					}
				}
				else {
					console.log(e.error_msg);
				}		
			}
		});		
	});
	
});

