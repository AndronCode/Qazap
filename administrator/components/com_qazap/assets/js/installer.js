/**
 * installer.js
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

if(typeof QazapInstaller === 'undefined' || typeof QazapInstaller === undefined) {
    var jQ = jQuery.noConflict();

    var QazapInstaller = {
        spinnervars : function() {
            var t = {
                lines: 10,
                length: 10,
                width: 4,
                radius: 15,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: '#000',
                speed: 1.5,
                trail: 60,
                shadow: false,
                hwaccel: true,
                className: 'qazap-installer-page-loader',
                zIndex: 2e9,
                top: 20,
                left: 14
            };
            loader_spinner = (new Spinner(t)).spin();
            var e = {
                lines: 13,
                length: 3,
                width: 2,
                radius: 5,
                corners: 1,
                rotate: 0,
                direction: 1,
                color: '#000',
                speed: 1.5,
                trail: 60,
                shadow: false,
                hwaccel: false,
                className: "qazap-installer-process-spinner",
                zIndex: 2e9,
                top: 20,
                left: 0
            };
            process_spinner = (new Spinner(e)).spin();
        },
        addPageLoader: function () {
            if(typeof loader_spinner === 'undefined' || typeof loader_spinner === undefined)
            {
                QazapInstaller.spinnervars();
            }
            if (jQ('#qazap-installer-page-overlay').length == 0) {
                jQ('body').append('<div id="qazap-installer-page-overlay"></div><div id="qazap-installer-page-spinner"><span></span></div>')
            }
            jQ('#header .navigation.sticky').css('z-index', 2e9);
            var e = jQ('body').outerHeight();
            jQ('#qazap-installer-page-overlay').css({
                    display: 'block',
                    height: e
                }).animate({
                    opacity: .7
                }, 300);
            jQ('#qazap-installer-page-spinner > span').append(loader_spinner.el)
        },
        removePageLoader: function () {
            if (jQ('#qazap-installer-page-overlay').length > 0) {
                jQ('#qazap-installer-page-overlay, #qazap-installer-page-spinner').animate({
                        'opacity': 0
                    }, 200, function() {
                        jQ(this).remove();
                    });
            }
            jQ('#header .navigation.sticky').css('z-index', '')
        },
        addProcessSpinner: function() {
            if(typeof process_spinner === 'undefined' || typeof process_spinner === undefined)
            {
                QazapInstaller.spinnervars();
            }
            jQ('.table-installer-status span.pending-process').append(process_spinner.el);
        },
        setAlert: function(text, type) {
            if(typeof type === 'undefined'){
                type = 'warning';
            }
            var html = '<div class="alert alert-'+type+'">';
            html += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
            html += text;
            html += '</div>';
            jQ('#qazap-system-message-box').append(html);
            jQ('html, body').animate({ scrollTop: 0 }, 300);
        },
        clearAlert: function() {
            jQ('#qazap-system-message-box').html('');
        },
        submitForm: function() {
            var $form = jQ('.qazap-installer-form');
            jQ.ajax({
                    dataType: 'json',
                    url: window.qzuri + '?option=com_qazap&view=install',
                    data: $form.serialize(),
                    beforeSend: function() {
                        QazapInstaller.addPageLoader();
                    },
                    success: function(data, textStatus) {
                        if(data.error == 1) {
                            Qazap.setAlert(data.html, 'error');
                        } else {
                            jQ('#qazap-installer-container').find('.qazap-installer-inner').html(data.html);
                            QazapInstaller.removePageLoader();
                            QazapInstaller.addProcessSpinner();
                            QazapInstaller.executeNextProcess();
                        }
                    },
                    error: function(jQXHR, textStatus, errorThrown) {
                        QazapInstaller.setAlert(errorThrown, 'error')
                    }
                });
            return false;
        },
        setGlobalVars: function() {
            lastProcess = null;
            processes = window.qzinstaller_actions;
            progress = 2;
        },
        executeNextProcess: function () {
            if(typeof lastProcess === 'undefined' || typeof lastProcess === undefined) {
                QazapInstaller.setGlobalVars();
            }
            var process = null;
            if(!lastProcess) {
                process = 'unpack_backend';
                QazapInstaller.setProgress();
            } else {
                for(var i = 0; i < processes.length; i++) {
                    if(processes[i] === lastProcess) {
                        break;
                    }
                }
                var nextKey = (i + 1);
                if(nextKey < processes.length) {
                    process = processes[nextKey];
                }
            }
            if(!process) {
                QazapInstaller.execute('all-done', null, 1);
                return false;
            }
            else
            {
                QazapInstaller.execute(process);
                lastProcess = process;
            }
        },
        execute: function(process, failed, last) {
            var tokenName = jQ('.table-installer-cont').find('#jtoken input').attr('name');
            var tokenVal = jQ('.table-installer-cont').find('#jtoken input').val();
            var variables = '';
            if(!process && !failed) {
                variables = tokenName + '=' + tokenVal;
            } else if(process) {
                variables = 'active=' +  process + '&' + tokenName + '=' + tokenVal;
            }    else if(failed) {
                variables = 'failed=' +  failed + '&' + tokenName + '=' + tokenVal;
            }
            jQ.ajax({
                    dataType: 'json',
                    url: window.qzuri + '?option=com_qazap&view=install&task=getsteps&' + variables,
                    success: function(data, textStatus) {
                        if(data.error == 1) {
                            QazapInstaller.setAlert(data.html, 'error');
                        } else {
                            var html = jQ.parseHTML(data.html);
                            var html = jQ(html).find('.table-installer-cont').html();
                            jQ('#qazap-installer-container').find('.table-installer-cont').html(html);
                            QazapInstaller.addProcessSpinner();
                            var runningProcess = jQ('#qazap-steps-table').find('.install-process.running .item').text();
                            var failedProcess = jQ('#qazap-steps-table').find('.install-process.failed .item').text();
                            if(failedProcess) {
                                jQ('#qazap-installer-container').find('.qazap-installer-process').text(failedProcess);
                            } else if(!runningProcess) {
                                QazapInstaller.setCompleted();
                            } else {
                                jQ('#qazap-installer-container').find('.qazap-installer-process').text(runningProcess);
                            }
                            if(!last && !failed) {
                                var data = 'process=' +  process + '&' + tokenName + '=' + tokenVal;
                                jQ.ajax({
                                        dataType: 'json',
                                        url: window.qzuri + '?option=com_qazap&view=install&task=run&' + data,
                                        success: function(rdata, rtextStatus) {
                                            if(rdata.error == 1) {
                                                QazapInstaller.execute(null, process);
                                                QazapInstaller.setAlert(rdata.html, 'error');
                                                QazapInstaller.setFailed();
                                            } else {
                                                QazapInstaller.setProgress(window.qzinstaller_stepvalue);
                                                QazapInstaller.executeNextProcess();
                                            }
                                        },
                                        error: function(rjQXHR, rtextStatus, rerrorThrown) {
                                            QazapInstaller.setAlert(rerrorThrown, 'error')
                                        }
                                    });
                            }
                        }
                    },
                    error: function(jQXHR, textStatus, errorThrown) {
                        QazapInstaller.setAlert(errorThrown, 'error');
                    }
                });
        },
        setProgress: function(add) {
            if(typeof progress === 'undefined' || typeof progress === undefined) {
                QazapInstaller.setGlobalVars();
            }
            if(!add) {
                //width = progress;
            } else {
                if(progress == 2) {
                    progress = progress + (add - 2);
                } else {
                    progress = progress + add;
                }
            }
            jQ('#qazap-installer-container').find('.progress .bar').width(progress + '%');
        },
        setFailed: function(error) {
            jQ('#qazap-installation-running, #qazap-installation-completed').hide();
            jQ('#qazap-installation-failed').show();
        },
        setCompleted: function() {
            jQ('#qazap-installation-running, #qazap-installation-failed').hide();
            jQ('#qazap-installation-completed').show();
            jQ('#QZSampleDataPop').popover({
                    'html' : true,
                    'placement' : 'top'
                });
        },
        installSampleData: function() {
            var tokenName = jQ('.table-installer-cont').find('#jtoken input').attr('name');
            var tokenVal = jQ('.table-installer-cont').find('#jtoken input').val();
            jQ.ajax({
                    dataType: 'json',
                    url: window.qzuri + '?option=com_qazap&view=install&task=installSampleData&' + tokenName + '=' + tokenVal,
                    beforeSend: function() {
                        jQ('#QZSampleDataPop').popover('hide');
                        QazapInstaller.addPageLoader();
                    },
                    success: function(data, textStatus) {
                        QazapInstaller.removePageLoader();
                        if(data.error == 1) {
                            QazapInstaller.setAlert(data.html, 'error');
                        } else {
                            if(data.html) {
                                QazapInstaller.setAlert(data.html, 'success');
                            }
                            setTimeout(function() {
                                    location.reload(true);
                                }, 3000);
                        }
                    },
                    error: function(jQXHR, textStatus, errorThrown) {
                        QazapInstaller.removePageLoader();
                        QazapInstaller.setAlert(errorThrown, 'error');
                    }
                });
        }

    }

    jQ(document).ready(function() {
            jQ('.hasTooltip').tooltip({'html': true, 'container': 'body'});
            if(jQ('#qazap-installer-container').length > 0) {
                jQ('#system-message-container').css('margin', '15px auto 0').width(jQ('#qazap-installer-container').outerWidth());
            }
        });


}
