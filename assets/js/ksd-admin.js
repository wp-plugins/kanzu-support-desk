/*Load google chart first. */
if ('undefined' !== typeof (google)) {
    google.load("visualization", "1", {packages: ["corechart"]});
}

jQuery(document).ready(function () {

    /**For the general navigation tabs**/
    jQuery("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
    jQuery("#tabs > ul > li").removeClass("ui-corner-top").addClass("ui-corner-left");

    /*Get URL parameters*/
    jQuery.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results === null) {
            return null;
        }
        else {
            return results[1] || 0;
        }
    };
    /*---------------------------------------------------------------*/
    /***************************UTILITIES: Used by all the rest*******/
    /*---------------------------------------------------------------*/
    KSDUtils = function () {
        _this = this;
    };

    KSDUtils.showDialog = function (dialog_type, message) {
        /**Show update/error/Loading dialog while performing AJAX calls and on completion*/
        message = message || ksd_admin.ksd_labels.msg_loading;//Set default message
        //First hide all other dialogs
        jQuery('.ksd-dialog').hide();
        jQuery('.' + dialog_type).html(message);//Set the message
        jQuery('.' + dialog_type).fadeIn(400).delay(3000).fadeOut(400); //fade out after 3 seconds
    };

    KSDUtils.ajaxResponseErrorCheck = function (ajaxResponse) {
        //To catch cases when the ajax response is not json
        try {
            //to reduce cost of recalling parse
            respObj = JSON.parse(ajaxResponse);
        } catch (err) {
            this.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
            return true;
        }
        //Check for error in request.
        if ('undefined' !== typeof (respObj.error)) {
            this.showDialog("error", respObj.error.message);
            return true;
        }
        return false;
    };

    KSDUtils.isNumber = function () {
        return typeof n === "number" && isFinite(n) && n % 1 === 0;
    };

    /**
     * Capitalize the first letter in a string
     * @param {string} theString String to capitalize e.g. hello or HELLO
     * @returns string capitalizedString e.g. Hello (all other letters are switched to lowercase, the first to uppercase
     */
    KSDUtils.capitalizeFirstLetter = function (theString) {
        return theString.toLowerCase().replace(/\b[a-z]/g, function (letter) {
            return letter.toUpperCase();
        });
    };


    /*---------------------------------------------------------------*/
    /*************************************ANALYTICS*********************/
    /*---------------------------------------------------------------*/
    KSDAnalytics = function () {
        _this = this;
    };
    KSDAnalytics.init = function () {
        if( ksd_admin.admin_tab.substr(0, 4) !== "ksd-" ){//@since 1.6.4. Exclude non-KSD pages from stats
          return;  
        }
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
        ga( 'create', 'UA-48956820-3', 'auto' );
        ga( 'require', 'linkid', 'linkid.js' );
        if ("yes" !== ksd_admin.enable_anonymous_tracking) {//Disable tracking if the user hasn't allowed it
             window['ga-disable-UA-48956820-3'] = true;
        }      

        //Send the page view for the current page. This is called the first time the page is loaded
        //so we get the current admin_tab from ksd_admin.admin_tab
        this.sendPageView(ksd_admin.admin_tab);
    };
    /**
     * Send a page view to Google Analytics
     * @param {string} current_admin_tab ID of the screen view to send. e.g. ksd-tickets
     * @returns none
     */
    KSDAnalytics.sendPageView = function (current_admin_tab) {
        pageName = KSDUtils.capitalizeFirstLetter(current_admin_tab.replace("ksd-", "").replace(/\-/g, " "));
        if (pageName === "Kanzu Support Desk") {//For instances where user directly clicks the main KSD menu item, its title is "Kanzu Support Desk" and it displays the dashboard so we translate it here
            pageName = "Dashboard";
            current_admin_tab = "ksd-dashboard";
        }
        thePage = '/' + current_admin_tab;//NB: Page names must start with a / 
        pageTitle = pageName + " - Kanzu Support Desk";
        ga('send', 'pageview', {'page': thePage, 'title': pageTitle});
    };

    /*---------------------------------------------------------------*/
    /****************************SETTINGS****************************/
    /*---------------------------------------------------------------*/
    KSDSettings = function () {
        _this = this;
        this.init = function () {
            //Submit the settings
            this.submitSettingsForm();
            //Show/Hide some settings when some checkboxes are checked
            this.toggleViewsToHide();
            //Use an accordion in case we have multiple setting blocks
            this.enableAccordion();

            this.changeSubmitBtnVal();
            this.modifyLicense();
            this.handleAddons();
            this.enableUsageStats();
        };

        /*
         * 
         */
        this.changeSubmitBtnVal = function () {
            jQuery('.ksd-send-email :checkbox').click(function () {
                var $this = jQuery(this);
                var $that = jQuery('[name=ksd-submit-admin-new-ticket]');
                if ($this.is(':checked')) {
                    $that.val('Send')
                } else {
                    $that.val('Save')
                }
            });
        };
        
        /**
         * Enable usage & error statistics
         * @returns {NULL}
         */
        this.enableUsageStats = function () {
            jQuery('button.ksd_enable_usage_stats').click(function () {
                jQuery.post(ksd_admin.ajax_url,
                        {   action: 'ksd_enable_usage_stats'
                        },
                function (response) {
                    var respObj = {};
                    //To catch cases when the ajax response is not json
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                        return;
                    }
                    KSDUtils.showDialog("success", respObj);
                });
        });            
        };
        
        /**
         * Handle add-ons
         * @returns {undefined}
         */
        this.handleAddons = function(){
            //Move the dummy addons, add them to the real addons list
            jQuery('span.ksd-dummy-addons li').appendTo('ul.add-ons');
            //Show dialog when one is clicked
            jQuery('li.ksd-dummy a').click(function(e){ 
                e.preventDefault();
                var addonName = jQuery(this).parents('li.ksd-dummy').find('h3').text();
                jQuery('#ksd-dummy-plugin-dialog span.ksd-addon-name').text(addonName);
            jQuery('#ksd-dummy-plugin-dialog').dialog({
                modal: true,
                buttons: {
                    "Yes, add me to the waiting list": function () {
                        jQuery(this).dialog("close");                       
                        jQuery.post(ksd_admin.ajax_url,
                                {   action: 'ksd_send_feedback',
                                    ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                                    feedback_type: 'waiting_list',
                                    ksd_user_feedback: addonName
                                },
                        function (response) {
                            var respObj = {};
                            //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                                return;
                            }
                            KSDUtils.showDialog("success", respObj);
                        });
                    },
                    "It's interesting": function () {
                        jQuery(this).dialog("close");
                        //Enable Google Analytics temporarily and send this event to Google Analytics
                        window['ga-disable-UA-48956820-3'] = false;
                        ga('send', 'event', 'button', 'click', addonName.toLowerCase() );
                    }
                }
            });
            });
        };

        /*
         * Submit Settings form.
         */
        this.submitSettingsForm = function () {
            /**AJAX: Update settings**/
            jQuery('form#update-settings').submit(function (e) {
                e.preventDefault();
                var data;
                if (jQuery(this).find("input[type=submit]:focus").hasClass("ksd-reset")) {//The  reset button has been clicked
                    data = {action: 'ksd_reset_settings', ksd_admin_nonce: ksd_admin.ksd_admin_nonce}
                }
                else if (jQuery(this).find("input[type=submit]:focus").hasClass("ksd-submit")) {//The update button has been clicked
                    data = jQuery(this).serialize();//The action and nonce are hidden fields in the form
                }
                else {//Another button has been clicked. Like 'Activate License' and 'De-activate License'
                    return false;
                }
                KSDUtils.showDialog("loading");
                jQuery.post(ksd_admin.ajax_url,
                        data,
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                        });
            });


            //Add Tooltips for the settings panel
            jQuery(".help_tip").tooltip();
            jQuery("span.ksd-tkt-status a").tooltip();

        }//eof:submitSettingsForm

        /**
         * Hide or show child settings as the value of their parent  
         * setting changes
         */
        this.toggleViewsToHide = function () {
            var parentFieldsToToggle = ['show_support_tab', 'enable_new_tkt_notifxns', 'enable_recaptcha'];
            jQuery.each(parentFieldsToToggle, function (i, field) {
                //Toggle the view on click    
                jQuery('input[name=' + field + ']').click(function () {
                    jQuery("." + field).toggle("slide");
                });
                //Make sure the fields are hidden if the field's not checked
                if (!jQuery('input[name=' + field + ']').is(":checked")) {
                    jQuery("." + field).hide();
                }
            });
        };
        this.enableAccordion = function () {
            //Only use the accordion if more than one section exists
            if (jQuery('div.ksd-settings-accordion h3').length > 1) {
                jQuery('div.ksd-settings-accordion').accordion({
                    collapsible: true,
                    heightStyle: "content"
                });
            }
            else {//Otherwise, remove the label 'General'
                jQuery('div.ksd-settings-accordion h3').remove();
            }

        };
        
        /**
         * Activate/Deactivate plugin licenses
         * @returns {undefined}
         */
        this.modifyLicense = function () {
            //Activate/Deactivate license button. Match all buttons that end with _license_status (Basically all license buttons)
            jQuery("form.ksd-settings input[name$='_license_status']").click(function () {
                var targetLicenseSetting = jQuery(this).parents('div.setting');
                //Add a 'Loading button' next to the clicked button
                var targetLicenseStatusSpan = targetLicenseSetting.find('span.license_status');
                targetLicenseStatusSpan.html('');
                targetLicenseStatusSpan.addClass('loading');
                var licenseAction;
                if (jQuery(this).hasClass('ksd-activate_license')) {
                    licenseAction = 'activate_license';
                }
                else {
                    licenseAction = 'deactivate_license';
                }
                //$plugin_name, $plugin_author, $plugin_options_key, $license_key, $license_status_key
                var pluginName = targetLicenseSetting.find('span.plugin_name').text();
                var pluginAuthorUri = targetLicenseSetting.find('span.plugin_author_uri').text();
                var pluginOptionsKey = targetLicenseSetting.find('span.plugin_options_key').text();
                var licenseKey = targetLicenseSetting.find('input[type=text]').attr('name');
                var licenseStatusKey = targetLicenseSetting.find('input[type=submit]').attr('name');
                var theLicense = targetLicenseSetting.find("input[name$='_license_key']").val();
                //Send the request. The variables are from the Kanzu Support Desk Js localization
                jQuery.post(ksd_admin.ajax_url,
                        {action: 'ksd_modify_license',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                            license_action: licenseAction,
                            plugin_name: pluginName,
                            plugin_author_uri: pluginAuthorUri,
                            plugin_options_key: pluginOptionsKey,
                            license_key: licenseKey,
                            license_status_key: licenseStatusKey,
                            license: theLicense
                        },
                function (response) {
                    targetLicenseStatusSpan.removeClass('loading');
                    try {
                        var raw_response = JSON.parse(response);
                    } catch (err) {
                        targetLicenseStatusSpan.html(ksd_admin.ksd_labels.msg_error_refresh);
                        return;
                    }
                    targetLicenseStatusSpan.html(raw_response);
                }
                );
            });
        };

    };//eof:KSDSettings

    /*---------------------------------------------------------------*/
    /*-------------------DASHBOARD----------------------------------*/
    /*---------------------------------------------------------------*/
    KSDDashboard = function () {
        _this = this;

        this.init = function () {
            this.statistics();
            this.charts();
            this.notifications();
        };


        /**
         * 
         * Add click events to the dashboard summaries
         */
        _addClickEventToSummaries = function () {

            //Total Open Tickets
            jQuery("#admin-kanzu-support-desk ul.dashboard-statistics-summary li:eq(0)").click(function () {
                jQuery("#tabs").tabs("option", "active", 1);
                jQuery("#ticket-tabs").tabs("option", "active", 0);

                var tab_id = 1;
                var currentTabID = "#tickets-tab-1";
                var limit = jQuery(currentTabID + " .ksd-pagination-limit").val();
                var search_text = jQuery(currentTabID + " .ksd_tkt_search_input").val();//Get val from the class on the input field, no need for ID
                jQuery(currentTabID).addClass("pending");
                var curPage = _getCurrentPage(tab_id);
                _this.getTickets(currentTabID, search_text, limit, curPage - 1);

                jQuery('.admin-ksd-title h2').html('Tickets'); //@TODO Internationalize this
            });

            //Unassigned Tickets
            jQuery("#admin-kanzu-support-desk ul.dashboard-statistics-summary li:eq(1)").click(function () {
                jQuery("#tabs").tabs("option", "active", 1);
                jQuery("#ticket-tabs").tabs("option", "active", 2);

                var tab_id = 3;
                var currentTabID = "#tickets-tab-3";
                var limit = jQuery(currentTabID + " .ksd-pagination-limit").val();
                var search_text = jQuery(currentTabID + " .ksd_tkt_search_input").val();//Get val from the class on the input field, no need for ID
                jQuery(currentTabID).addClass("pending");
                var curPage = _getCurrentPage(tab_id);
                _this.getTickets(currentTabID, search_text, limit, curPage - 1);

                jQuery('.admin-ksd-title h2').html('Tickets');//@TODO Internationalize this
            });



        }
        /*
         * Show statistics summary.
         */
        this.statistics = function () {
            /**AJAX: Retrieve summary statistics for the dashboard**/
            if (jQuery("ul.dashboard-statistics-summary").hasClass("pending")) {
                jQuery.post(ksd_admin.ajax_url,
                        {action: 'ksd_get_dashboard_summary_stats',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                        },
                function (response) {
                    jQuery("ul.dashboard-statistics-summary").removeClass("pending");
                    try {
                        var raw_response = JSON.parse(response);
                    } catch (err) {
                        jQuery('ul.dashboard-statistics-summary').html(ksd_admin.ksd_labels.msg_error);
                        return;
                    }
                    if ('undefined' !== typeof (raw_response.error)) {
                        jQuery('ul.dashboard-statistics-summary').html(raw_response.error.message);
                        return;
                    }
                    var unassignedTickets = ('undefined' !== typeof raw_response.unassigned_tickets[0] ? raw_response.unassigned_tickets[0].unassigned_tickets : 0);
                    var openTickets = ('undefined' !== typeof raw_response.open_tickets[0] ? raw_response.open_tickets[0].open_tickets : 0)
                    var averageResponseTime = ('undefined' !== typeof raw_response.average_response_time ? raw_response.average_response_time : '00:00');
                    var the_summary_stats = "";
                    the_summary_stats += "<li class='ksd-dash-click'><span>" + ksd_admin.ksd_labels.dashboard_open_tickets + "</span>" + openTickets + "</li>";
                    the_summary_stats += "<li class='ksd-dash-click'><span>" + ksd_admin.ksd_labels.dashboard_unassigned_tickets + "</span>" + unassignedTickets + "</li>";
                    the_summary_stats += "<li><span>" + ksd_admin.ksd_labels.dashboard_avg_response_time + "</span>" + averageResponseTime + "</li>";
                    jQuery("ul.dashboard-statistics-summary").html(the_summary_stats);

                    //Add click events
                    _addClickEventToSummaries();
                });
            }
        }//eof:statistics

        /*Initialise charts*/
        this.charts = function () {
            try {
                /**The dashboard charts. These have their own onLoad method so they can't be run inside jQuery( document ).ready({});**/
                function ksdDrawDashboardGraph() {
                    jQuery.post(ksd_admin.ajax_url,
                            {action: 'ksd_dashboard_ticket_volume',
                                ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                            },
                    function (response) {
                        //IMPORTANT! Google Charts, without width & height explicitly specified, are drawn
                        //to fill the parent element. This doesn't work so well if the parent element is hidden
                        //while the drawing is happening. In such cases, the final chart will have default dimensions (400px x 200px)
                        //To work-around this, we first unhide our parent div just before drawing the chart
                        var ksdChartContainer = document.getElementById('dashboard');
                        var respObj = JSON.parse(response);
                        if ('undefined' !== typeof (respObj.error)) {
                            jQuery('#ksd_dashboard_chart').html(respObj.error.message);
                            return;
                        }
                        if ('undefined' !== typeof google.visualization && null !== ksdChartContainer) //First check if we can draw a Google Chart
                            ksdChartContainer.style.display = 'block';//Unhide the parent element
                        var ksdData = google.visualization.arrayToDataTable(respObj);
                        var ksdOptions = {
                            title: ksd_admin.ksd_labels.dashboard_chart_title
                        };
                        var ksdDashboardChart = new google.visualization.LineChart(document.getElementById('ksd_dashboard_chart'));
                        //Add a listener to know when drawing the chart is complete.                     
                        google.visualization.events.addListener(ksdDashboardChart, 'ready', function () {
                            if (!jQuery('ul.ksd-main-nav li:first').hasClass("ui-tabs-active")) {
                                ksdChartContainer.style.display = 'none'; //If our dashboard tab isn't the selected one, we hide it. 
                            }
                        });
                        ksdDashboardChart.draw(ksdData, ksdOptions);
                    });//eof: jQuery.port
                }
                google.setOnLoadCallback(ksdDrawDashboardGraph);
            } catch (err) {
                jQuery('#ksd_dashboard_chart').html(err);
            }
        };//eof:charts
        this.notifications = function () {
            //Show/Hide the notifications panel
            jQuery('.admin-ksd-title span.more_nav img').click(function (e) {
                e.preventDefault();
                jQuery(this).toggleClass("active");
                jQuery("#ksd-notifications").toggle("slide");
            });
            //Retrieve the notifications
            try {
                jQuery.post(ksd_admin.ajax_url,
                        {action: 'ksd_get_notifications',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                        },
                function (response) {
                    var respObj = JSON.parse(response);
                    if ('undefined' !== typeof (respObj.error)) {
                        jQuery('#ksd-notifications').html(respObj.error);
                        return;
                    }
                    //Parse the XML. We chose to do it here, rather than in the PHP (at the server end)
                    //for better performance (no impact on the server)
                    notificationsXML = jQuery.parseXML(respObj);
                    notificationData = '<ul>';
                    jQuery(notificationsXML).find("item").each(function (i, item) {
                        blogPost = jQuery(this);
                        notificationData += '<li>';
                        notificationData += '<a href="' + blogPost.find('link').text() + '" target="_blank" class="post-title">' + blogPost.find('title').text() + '</a>';
                        notificationData += '<span class="date-published">' + blogPost.find('pubDate').text() + '</span>';
                        notificationData += '<a href="' + blogPost.find('link').text() + '" target="_blank" class="excerpt"><p>' + blogPost.find('description').text().substr(0, 100) + '...</p></a>';
                        notificationData += '</li>';
                        return i < 2;//Stops the loop after the first 3 items are returned
                    });
                    notificationData += '</ul>';
                    //Add the entries to the div*/
                    jQuery("#ksd-notifications").html(notificationData);
                });
            } catch (err) {
                jQuery('#ksd-notifications').html(err);
            }
        };//eof:notifications
    };//eof:Dashboard

    /*---------------------------------------------------------------*/
    /*---------------------------HELP-------------------------------*/
    /*-------------------------------------------------------------*/
    KSDHelp = function () {
        _this = this;
        this.init = function () {
            //Submit feedback
            this.submitFeedbackForm();
            this.generateTourContent();
        };

        /*
         * Submit Feedback form.
         */
        this.submitFeedbackForm = function () {
            /**AJAX: Send Feedback**/
            jQuery('form#ksd-feedback').submit(function (e) {
                e.preventDefault();
                KSDUtils.showDialog("loading", ksd_admin.ksd_labels.msg_sending);
                jQuery.post(ksd_admin.ajax_url,
                        jQuery(this).serialize(), //The action and nonce are hidden fields in the form, 
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                        });
            });
            //All other feedback forms. They start with class ksd-feedback-
            jQuery("form[class^='ksd-feedback-']").submit(function (e) {
                e.preventDefault();
                KSDUtils.showDialog("loading", ksd_admin.ksd_labels.msg_sending);
                jQuery.post(ksd_admin.ajax_url,
                        jQuery(this).serialize(), //The action and nonce are hidden fields in the form, 
                        function (response) {
                            if (KSDUtils.ajaxResponseErrorCheck(response)) {
                                return;
                            }
                            KSDUtils.showDialog("success", JSON.parse(response));
                        });
            });
        };
        this.generateTourContent = function () {
            var pointerContentIndex = 0;
            if (ksd_admin.ksd_tour_pointers.ksd_intro_tour) {//If pointers are set, show them off 
                var pointer = ksd_admin.ksd_tour_pointers.ksd_intro_tour;

                /**
                 * Create a pointer using content defined at pointer[pointerContentIndex]
                 * and display on a particular tab (dashboard, tickets, etc). The tab to display
                 * it on is defined in pointer[pointerContentIndex].tab
                 * @param int pointerContentIndex
                 */
                generatePointer = function (pointerContentIndex) {
                    //Change the active tab
                    jQuery("#tabs").tabs("option", "active", pointer[pointerContentIndex].tab);
                    //Generate the pointer options
                    options = jQuery.extend(pointer[pointerContentIndex].options, {
                        close: function () {
                            /* jQuery.post( ksd_admin.ajax_url, {
                             pointer: 'ksd_intro_tour',
                             action: 'dismiss-wp-pointer'
                             });*/
                            //Disable tour mode
                            jQuery.post(ksd_admin.ajax_url, {
                                action: 'ksd_disable_tour_mode'
                            });
                        }
                    });
                    //Open the pointer
                    jQuery(pointer[pointerContentIndex].target).pointer(options).pointer('open');
                    //Inject a 'Next' button into the pointer
                    jQuery('a.close').after('<a href="#" class="ksd-next button-primary">' + ksd_admin.ksd_labels.pointer_next + '</a>');
                };

                generatePointer(pointerContentIndex);
                //Move to the next pointer when 'Next' is clicked
                //Event needs to be attached this way since the link was manually injected into the HTML
                jQuery('body').on('click', 'a.ksd-next', function (e) {
                    e.preventDefault();
                    //Close the current pointer
                    //jQuery( pointer[pointerContentIndex].target ).pointer('close');
                    //Manually hide the parent
                    jQuery(this).parents('.wp-pointer').hide();
                    if (pointerContentIndex <= (pointer.length - 2)) {//We subtract 2 because of how we are doing the incrementing; tour will automatically end after the array's contents are done
                        ++pointerContentIndex;
                    }
                    else {//End of the tour
                        //Dismiss the pointer in the WP db
                        /*jQuery.post( ksd_admin.ajax_url, {
                         pointer: 'ksd_intro_tour',
                         action: 'dismiss-wp-pointer'
                         });*/
                        //Disable tour mode
                        jQuery.post(ksd_admin.ajax_url, {
                            action: 'ksd_disable_tour_mode'
                        });
                        return;
                    }
                    //Open the next pointer
                    generatePointer(pointerContentIndex);

                });
            }
        };
    };

    /*---------------------------------------------------------------*/
    /*************************************TICKETS*********************/
    /*---------------------------------------------------------------*/
    KSDTickets = function () {
        _this = this;
        this.init = function () {

            this.uiTabs();
            this.uiListTickets();
            this.bulkTicketActions();
            this.newTicket();
            this.editTicketForm();

            this.attachDeleteTicketEvent();
            this.attachChangeTicketStatusEvents();
            this.attachAssignToEvents();
            this.attachChangeSeverityEvents();
            this.attachMarkReadUnreadEvents();
            this.uiSingleTicketView();

            //Search
            this.TicketSearch();

            //Pagination
            this.TicketPagination();

            //Page Refresh
            this.attachRefreshTicketsPage();
        };


        /*
         * Total ticket indicator in ticket filters
         */

        _totalTicketsPerFilter = function () {
            var data = {
                action: 'ksd_filter_totals',
                ksd_admin_nonce: ksd_admin.ksd_admin_nonce
            };

            jQuery.post(ksd_admin.ajax_url, data, function (response) {
                var respObj = {};

                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }

                if (jQuery.isArray(respObj)) {
                    jQuery("#ticket-tabs ul li:eq(0) span").html("(" + respObj[0].tab1 + ")");
                    jQuery("#ticket-tabs ul li:eq(1) span").html("(" + respObj[0].tab2 + ")");
                    jQuery("#ticket-tabs ul li:eq(2) span").html("(" + respObj[0].tab3 + ")");
                    jQuery("#ticket-tabs ul li:eq(3) span").html("(" + respObj[0].tab4 + ")");
                    jQuery("#ticket-tabs ul li:eq(4) span").html("(" + respObj[0].tab5 + ")");
                    jQuery("#ticket-tabs ul li:eq(5) span").html("(" + respObj[0].tab6 + ")");
                }

            });
        }

        /**
         * Get tickets. Show a loading dialog while the tickets are being retrieved
         * @param string current_tab The ID of the current tickets tab, including the # e.g. #tickets-tab-1
         * @param string search The search string specified
         * @param int limit How many tickets to display
         * @param int offset The offset
         * @param boolean overlayLoading Whether to overlay the 'Loading' dialog during ticket loading or not.
         *                  We overlay the loading dialog when we refresh, paginate or search in a ticket view. Otherwise, we don't
         * @returns N/A. Writes returned tickets to the UI
         */
        this.getTickets = function (current_tab, search, limit, offset, overlayLoading) {

            //Default values
            if (typeof (search) === 'undefined')
                search = "";
            if (typeof (limit) === 'undefined')
                limit = 20;
            if (typeof (offset) === 'undefined' || jQuery.isNumeric(offset) === false)
                offset = 0;
            if (typeof (overlayLoading) === 'undefined')
                overlayLoading = false;
            //Show a loading dialog
            if (overlayLoading) {
                jQuery('.ksd-loading-tickets-overlay').removeClass('hidden');
            } else {
                jQuery(current_tab).addClass('ksd-loading-tickets');
            }

            if (jQuery(current_tab).hasClass("pending"))//Check if the tab has been loaded before
            {
                var data = {
                    action: 'ksd_filter_tickets',
                    ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                    view: current_tab,
                    search: search,
                    limit: limit,
                    offset: offset
                };

                jQuery.post(ksd_admin.ajax_url, data, function (response) {

                    var respObj = {};
                    //To catch cases when the ajax response is not json
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                        return;
                    }

                    //Check for error in request.
                    if ('undefined' !== typeof (respObj.error)) {
                        KSDUtils.showDialog("error", respObj.error.message);
                        return;
                    }


                    if (jQuery.isArray(respObj)) {
                        tab_id = current_tab.replace("#tickets-tab-", "");
                        ticketListData = "";
                        ticketListData += '<div class="ksd-row-all-hide" id="ksd_row_all_' + tab_id + '">';
                        ticketListData += '<div  id="tkt_all_options"> \
                                                    <a href="#" class="trash" id="#">Trash All</a> | \
                                                    <a href="#" id="#" class="change_status">Change All Statuses</a> | \
                                                    <a href="#" id="#" class="assign_to">Assign All To</a> \
                                                </div>';
                        ticketListData += '</div>';

                        jQuery(current_tab + ' .ticket-list').html(ticketListData);

                        jQuery.each(respObj[0], function (key, value) {
                            var tkt_is_unread = '';//Show whether the ticket is unread
                            var changeReadStatusLink = '<a href="#" id="tkt_' + value.tkt_id + '" class="mark_unread">' + ksd_admin.ksd_labels.tkt_mark_unread + '</a>';
                            //Number of replies
                            kst_tkt_replies = "";
                            if (value.rep_count > 0) {
                                kst_tkt_replies = " (" + value.rep_count + ") ";
                            }
                            if (value.tkt_is_read < 1) {
                                tkt_is_unread = ' ksd-unread';//The space before the string is important. Don't remove it
                                changeReadStatusLink = '<a href="#" id="tkt_' + value.tkt_id + '" class="mark_read">' + ksd_admin.ksd_labels.tkt_mark_read + '</a>';
                            }


                            ticketListData = '<div class="ksd-row-data ticket-list-item ksd-' + (value.tkt_status).toLowerCase() + '-ticket ' + (value.tkt_severity).toLowerCase() + tkt_is_unread + '" id="ksd_tkt_id_' + value.tkt_id + '">';
                            ticketListData += '<div class="ticket-info">';
                            ticketListData += '<input type="checkbox" value="' + value.tkt_id + '" name="ticket_ids[]" id="ticket_checkbox_' + value.tkt_id + '">';
                            ticketListData += '<span class="ksd-tkt-status ' + (value.tkt_status).toLowerCase() + '"><a href="' + ksd_admin.ksd_tickets_url + '&ticket=' + value.tkt_id + '&action=edit" title="' + (value.tkt_status).toLowerCase() + '">' + (value.tkt_status).charAt(0) + '</a></span>';
                            ticketListData += '<span class="ksd-tkt-customer-name"><a href="' + ksd_admin.ksd_tickets_url + '&ticket=' + value.tkt_id + '&action=edit">' + value.tkt_cust_id + kst_tkt_replies + '</a></span>';
                            ticketListData += '<span class="subject-and-message-excerpt"><a class="ksd-tkt-subject"href="' + ksd_admin.ksd_tickets_url + '&ticket=' + value.tkt_id + '&action=edit">' + value.tkt_subject + '</a>';
                            ticketListData += '<a class="ksd-message-excerpt" href="' + ksd_admin.ksd_tickets_url + '&ticket=' + value.tkt_id + '&action=edit"> - ' + value.tkt_message_excerpt + '</a></span>';
                            ticketListData += '<span class="ticket-time">' + value.tkt_time_logged + '</span>';

                            ticketListData += '</div>';
                            ticketListData += '<div class="ticket-actions" id="tkt_' + value.tkt_id + '">';
                            ticketListData += '<a href="#" class="trash" id="tkt_' + value.tkt_id + '">' + ksd_admin.ksd_labels.tkt_trash + '</a> | ';
                            ticketListData += '<a href="#" id="tkt_' + value.tkt_id + '" class="change_status">' + ksd_admin.ksd_labels.tkt_change_status + '</a> | ';
                            ticketListData += '<a href="#" id="tkt_' + value.tkt_id + '" class="assign_to">' + ksd_admin.ksd_labels.tkt_assign_to + '</a> | ';
                            ticketListData += '<a href="#" id="tkt_' + value.tkt_id + '" class="change_severity">' + ksd_admin.ksd_labels.tkt_change_severity + '</a> | ';
                            ticketListData += changeReadStatusLink;
                            ticketListData += ksd_admin.ksd_agents_list;
                            ticketListData += '<ul class="status hidden"><li class="OPEN">' + ksd_admin.ksd_labels.tkt_status_open + '</li><li class="PENDING">' + ksd_admin.ksd_labels.tkt_status_pending + '</li><li class="RESOLVED">' + ksd_admin.ksd_labels.tkt_status_resolved + '</li></ul>';
                            ticketListData += '<ul class="severity hidden"><li class="LOW">' + ksd_admin.ksd_labels.tkt_severity_low + '</li><li class="MEDIUM">' + ksd_admin.ksd_labels.tkt_severity_medium + '</li><li class="HIGH">' + ksd_admin.ksd_labels.tkt_severity_high + '</li><li class="URGENT">' + ksd_admin.ksd_labels.tkt_severity_urgent + '</li></ul>';
                            ticketListData += '</div>';
                            ticketListData += '</div>';

                            jQuery(current_tab + ' .ticket-list').append(ticketListData);
                        });//eof:jQUery.each

                        /**Add class .alternate to every ticket that's not OPEN.*/
                        jQuery(".ticket-list .ksd-row-data:not(.ksd-open-ticket)").addClass("alternate");

                        RowCtrlEffects();
                    }
                    else {
                        jQuery(current_tab + ' #select-all-tickets').remove();
                        jQuery(current_tab + ' .ticket-list').addClass("empty").html(respObj);
                    }//eof:if

                    jQuery(current_tab).removeClass("pending");
                    if (overlayLoading) {
                        jQuery('.ksd-loading-tickets-overlay').addClass('hidden');
                    } else {
                        jQuery(current_tab).removeClass('ksd-loading-tickets');//Remove loading image
                    }



                    //Add Navigation
                    var tab_id = current_tab.replace("#tickets-tab-", "");
                    var total_rows = respObj[1];
                    var currentpage = offset + 1;
                    _loadTicketPagination(tab_id, currentpage, total_rows, limit);

                    //Refresh Totals
                    _totalTicketsPerFilter();


                });//eof:jQuery.post	
            }//eof:if                
        };
        /*
         * List all tickets
         */
        this.uiListTickets = function () {
            /*
             * 
             *Return the ticket row to normal size when mouse leaves the ticket options(ie trash, change status, assign) when
             */
            jQuery("#ticket-tabs").on('mouseleave', '.ksd-row-data', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                jQuery(this).parent().find(".ticket-actions ul").addClass("hidden");
            });

        }//eof:

        /*
         * Make changes to tickets in bulk
         * @returns {undefined}
         */
        this.bulkTicketActions = function () {
            //Show bulk update menu
            jQuery("#ticket-tabs").on('change', 'input[name="ticket_ids[]"]', function () {
                if (jQuery('input[name="ticket_ids[]"]:checkbox:checked').length > 0) {//If any ticket is checked
                    if (jQuery('.ticket-actions-top-menu').hasClass('hidden')) {
                        jQuery('.ticket-actions-top-menu').removeClass('hidden');
                    }
                }
                else {//No checkbox is checked
                    if (!jQuery('.ticket-actions-top-menu').hasClass('hidden')) {
                        jQuery('.ticket-actions-top-menu').addClass('hidden');
                    }
                }
            });
            //Toggle Bulk update sub-menu visibility
            jQuery('div.ticket-actions-top-menu a.change_status').click(function (e) {
                e.preventDefault();
                jQuery('div.ticket-actions-top-menu ul.status').toggleClass('hidden');
                jQuery('div.ticket-actions-top-menu ul.ksd_agent_list').addClass('hidden');
            });
            jQuery('div.ticket-actions-top-menu a.assign_to').click(function (e) {
                e.preventDefault();
                jQuery('div.ticket-actions-top-menu ul.ksd_agent_list').toggleClass('hidden');
                jQuery('div.ticket-actions-top-menu ul.status').addClass('hidden');
            });
            //Hide the bulk update sub-menus when they lose focus
            jQuery("div.ticket-actions-top-menu ul.status").bind("mouseleave", function () {
                jQuery(this).addClass('hidden');
            });
            jQuery("div.ticket-actions-top-menu ul.ksd_agent_list").bind("mouseleave", function () {
                jQuery(this).addClass('hidden');
            });
            //AJAX: Bulk actions
            //            //Get checked ticket IDs
            _getCheckedTicketIDs = function () {
                var tkt_IDs = [];
                jQuery('#ticket-tabs    input[name="ticket_ids[]"]:checkbox:checked').each(function () {
                    tkt_ID = jQuery(this).parent().parent().attr("id").replace("ksd_tkt_id_", "");
                    if ('undefined' !== typeof (tkt_ID)) {
                        tkt_IDs.push(tkt_ID);
                    }
                });
                return tkt_IDs;
            };
            //Bulk Change status
            jQuery('div.ticket-actions-top-menu ul.status li').click(function () {
                var tkt_IDs = _getCheckedTicketIDs();
                tktStatus = jQuery(this).attr("class");//We use the class so that the text can be internationalized
                _this.changeTicketStatus(tkt_IDs, tktStatus);
                jQuery('div.ticket-actions-top-menu').addClass('hidden');
            });
            //Bulk assign to
            jQuery('div.ticket-actions-top-menu ul.ksd_agent_list li').click(function () {
                var tkt_IDs = _getCheckedTicketIDs();
                assignTo = jQuery(this).attr("id");//The new assignee
                _this.reassignTicket(tkt_IDs, assignTo);
                jQuery('div.ticket-actions-top-menu').addClass('hidden');
            });
            //Bulk delete
            jQuery('div.ticket-actions-top-menu a.trash').click(function (e) {
                e.preventDefault();
                var tkt_IDs = _getCheckedTicketIDs();
                _this.deleteTicket(tkt_IDs);
                jQuery('div.ticket-actions-top-menu').addClass('hidden');
            });

            //---------------------------------------------------------------------------------
            /*All check box.
             * control_id: tkt_chkbx_all
             */
            jQuery("#ticket-tabs .tkt_chkbx_all").on("click", function () {
                //TODO:Show all options
                if (jQuery(this).prop('checked') === true) {
                    jQuery("#tkt_all_options").removeClass("ticket-actions");
                    jQuery('input:checkbox').not(this).prop('checked', this.checked);

                    //
                    tab_id = jQuery(this).attr("id").replace("tkt_chkbx_all_", "");
                    jQuery("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-hide').addClass("ksd-row-all-show");


                } else {
                    jQuery("#tkt_all_options").addClass("ticket-actions");
                    jQuery('input:checkbox').not(this).prop('checked', this.checked);

                    tab_id = jQuery(this).attr("id").replace("tkt_chkbx_all_", "");
                    jQuery("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-show').addClass("ksd-row-all-hide");
                }

            });

        };//eof

        /**
         * AJAX: Send an AJAX request to re-assign a ticket
         * @param int tkt_id The ticket ID
         * @param int assign_assigned_to The ID of the user to assign the ticket to
         */
        this.reassignTicket = function (tkt_id, assign_assigned_to, singleTicketView) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            jQuery.post(ksd_admin.ajax_url,
                    {action: 'ksd_assign_to',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        ksd_current_user_id: ksd_admin.ksd_current_user_id,
                        tkt_assign_assigned_to: assign_assigned_to
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         * AJAX. Mark a ticket as read/unread
         * @param int tkt_id The ticket's ID
         * @param int markAsRead 1 to mark ticket as read, 0 to mark it as unread
         * @param boolean singleTicketView Whether this is a single ticket view or not. Default is false
         * @returns {undefined}
         */
        this.markTicketReadUnread = function (tkt_id, markAsRead, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            jQuery.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_read_status',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_is_read: markAsRead
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){//If we are in the ticket grid
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });
        };

        /**
         * Change a ticket's severity
         * @param int tkt_id
         * @param string tkt_severity New severity
         */
        this.changeTicketSeverity = function (tkt_id, tkt_severity, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            jQuery.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_severity',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_severity: tkt_severity
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                 if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         * Attach the event that deletes single tickets
         * @returns {undefined}
         */
        this.attachDeleteTicketEvent = function () {
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.trash', function (event) {
                event.preventDefault();
                var tkt_id = jQuery(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.deleteTicket(tkt_id);
            });
        };
        //---------------------------------------------------------------------------------
        /**AJAX: Delete a ticket **/
        this.deleteTicket = function (tkt_id) {
            displayDialog = '#delete-dialog';
            if (jQuery.isArray(tkt_id)) {
                displayDialog += '-bulk';
            }
            jQuery(displayDialog).dialog({
                modal: true,
                buttons: {
                    Yes: function () {
                        jQuery(this).dialog("close");
                        KSDUtils.showDialog("loading");
                        jQuery.post(ksd_admin.ajax_url,
                                {action: 'ksd_delete_ticket',
                                    ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                                    tkt_id: tkt_id
                                },
                        function (response) {
                            var respObj = {};
                            //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh);
                                return;
                            }
                            //Check for error in request.
                            if ('undefined' !== typeof (respObj.error)) {
                                KSDUtils.showDialog("error", respObj.error.message);
                                return;
                            }
                            if (!jQuery.isArray(tkt_id)) {//Signle ticket deletion
                                jQuery('.ticket-list div#ksd_tkt_id_' + tkt_id).remove();
                            }
                            else {//Delete tickets in bulk
                                jQuery.each(tkt_id, function (index, the_ID) {
                                    jQuery('.ticket-list div#ksd_tkt_id_' + the_ID).remove();
                                });
                            }
                            KSDUtils.showDialog("success", respObj);
                        });
                    },
                    No: function () {
                        jQuery(this).dialog("close");
                    }
                }
            });
            jQuery("div.ui-widget-overlay").remove();
        };

        //--------------------------------------------------------------------------------------
        /**AJAX: Send a single ticket response when it's been typed and 'Reply' is hit**/
        //Also, update the private note when 'Update Note' is clicked  
        this.replyTicketAndUpdateNote = function (form) {
            var action = jQuery("input[name=action]").attr("value");
            KSDUtils.showDialog("loading");//Show a dialog message
            tinyMCE.triggerSave();//Very important. Without this, the reply's text won't be 'seen' by the serialize below
            jQuery.post(ksd_admin.ajax_url,
                    jQuery(form).serialize(), //The action, nonce and TicketID are hidden fields in the form
                    function (response) {
                        var respObj = {};
                        //To catch cases when the ajax response is not json
                        try {
                            //to reduce cost of recalling parse
                            respObj = JSON.parse(response);
                        } catch (err) {
                            KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                            return;
                        }

                        //Check for error in request.
                        if ('undefined' !== typeof (respObj.error)) {
                            KSDUtils.showDialog("error", respObj.error.message);
                            return;
                        }

                        switch (action) {
                            case "ksd_update_private_note":
                                KSDUtils.showDialog("success", respObj);
                                break;
                            default:
                                KSDUtils.showDialog("success", ksd_admin.ksd_labels.msg_reply_sent);
                                var d = new Date();
                                replyData = "<div class='ticket-reply'>";
                                replyData += "<span class='reply_author'></span>";
                                replyData += '<span class="reply_date">' + d.toLocaleString() + '</span>';
                                replyData += "<div class='reply_message'>";
                                replyData += tinyMCE.activeEditor.getContent();
                                replyData += "</div></div>";
                                jQuery("#ticket-replies").append(replyData);
                                //Clear the reply field
                                tinyMCE.activeEditor.setContent('');
                        }
                    });
        };

        /**
         * Format ticket replies. Hide extra content from
         * the previous message and generally make the displayed content
         * more user-friendly
         */
        this.formatTicketReplies = function () {
            /* #1 First match extra content from various email clients and wrap it in class 'ksd_extra'. We match the extra content
             based on knowing that content's structure. Currently matches Gmail (Android and Desktop) & Outlook. To be expanded
             -------------------------------------------------------------------------------------------*/
            //Match Outlook 2013 extra content  @TODO Add mobile outlook, outlook 2007 and 2010
            jQuery('p:contains("-----Original Message-----")').nextUntil("div").andSelf().wrapAll('<div class="ksd_extra"></div>');
            //Match Gmail ( Android and Desktop ) clients
            jQuery('div.gmail_quote').addClass('ksd_extra');
            //Match Yahoo desktop clients. Written separately from the rest merely for legibility
            jQuery('div.yahoo_quoted').addClass('ksd_extra');
            //@TODO Add more mail clients, IOS particularly

            /* #2 To the content we've wrapped in class 'ksd_extra' in #1 above, append the icon that'll be used to toggle the extra content*/
            jQuery('#ksd-single-ticket .ksd_extra').before('<div class="replies-more" title="' + ksd_admin.ksd_labels.lbl_toggle_trimmed_content + '"></div>');

            // #3 Add an event to that icon we appended
            jQuery('#ksd-single-ticket').on('click', '.replies-more', function () {
                jQuery(this).parents('.ticket-reply').find('.ksd_extra').toggle('slide');//Go up the DOM, find the ticket reply then find the extra content in it
            });

            //#4 Initially, hide all the extra content
            jQuery('.ksd_extra').toggle();
        };

        this.editTicketForm = function () {
            jQuery("form#edit-ticket").validate({
                submitHandler: function (form) {
                    _this.replyTicketAndUpdateNote(form);
                }
            });

            /*-------------------------------------------------------------------------------------------------
             * AJAX: Log New ticket
             */
            ksdLogNewTicketAdmin = function (form) {
                KSDUtils.showDialog("loading");//Show a dialog message
                jQuery.post(ksd_admin.ajax_url,
                        jQuery(form).serialize(), //The action and nonce are hidden fields in the form
                        function (response) {
                            var respObj = {};
                            //To catch cases when the ajax response is not json
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                                return;
                            }

                            //Check for error in request.
                            if ('undefined' !== typeof (respObj.error)) {
                                KSDUtils.showDialog("error", respObj.error.message);
                                return;
                            }
                            KSDUtils.showDialog("success", respObj);
                            //We send an email to the admin telling them about the new ticket. We do this by AJAX
                            //because our tests showed that wp_mail took in some cases 5 seconds to return a response
                            jQuery.post(ksd_admin.ajax_url,
                                    {action: 'ksd_notify_new_ticket',
                                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce
                                    },
                            function () {
                                //@TODO We currently don't do anything with this response
                            });

                            //Redirect to the Tickets page
                            window.location.replace(ksd_admin.ksd_tickets_url);
                        });
                ;
            };
            /**While working on a single ticket, switch between reply/forward and Add note modes
             * We define the action (used by AJAX) and change the submit button's text
             */
            jQuery('ul.edit-ticket-options li a').click(function (e) {
                e.preventDefault();
                action = jQuery(this).attr("href").replace("#", "");
                switch (action) {
                    case "forward_ticket":
                        submitButtonText = ksd_admin.ksd_labels.tkt_forward;
                        break;
                    case "update_private_note":
                        submitButtonText = ksd_admin.ksd_labels.tkt_update_note;
                        break;
                    default:
                        submitButtonText = ksd_admin.ksd_labels.tkt_reply;
                }
                jQuery("input[name=action]").attr("value", "ksd_" + action);
                jQuery("input[name=edit-ticket]").attr("value", submitButtonText);
            });

            /**For the Reply/Forward/Private Note tabs that appear when viewing a single ticket.*/
            //First check if the element exists
            if (jQuery("ul.edit-ticket-options").length) {
                jQuery("#edit-ticket-tabs").tabs();

            }


        }



        this.newTicket = function () {

            /*On focus, Toggle customer name, email and subject */
            _toggleFieldValues();
            //This mousedown event is very important; without it, the wp_editor value isn't sent by AJAX
            jQuery('form.ksd-new-ticket-admin :submit').mousedown(function () {
                tinyMCE.triggerSave();
            });
            /**Validate New Tickets before submitting the form by AJAX**/
            jQuery("form.ksd-new-ticket-admin").validate({
                submitHandler: function (form) {
                    ksdLogNewTicketAdmin(form);
                }
            });
            //Add Attachments
            jQuery('[id^="ksd-add-attachment-"]').click(function () {
                var targetUL = 'ksd-attachments';
                if (jQuery(this).hasClass('ksd_ticket_reply')) {//This is an attachment in single ticket view
                    targetUL = 'ksd-attachments-single-ticket';
                }
                if (this.window === undefined) {
                    this.window = wp.media({
                        title: ksd_admin.ksd_labels.tkt_attach_file,
                        multiple: true,
                        button: {text: ksd_admin.ksd_labels.tkt_attach}
                    });
                    var self = this; // Needed to retrieve our variable in the anonymous function below                    
                    this.window.on('select', function () {
                        var files = self.window.state().get('selection').toArray();
                        jQuery.each(files, function (key, attachmentRaw) {
                            attachment = attachmentRaw.toJSON();
                            attachmentLink = '<a href="' + attachment.url + '">' + attachment.filename + ' <span="ksd-attach-filesize"> ( ' + attachment.filesizeHumanReadable + ' )</span></a>';
                            attachmentFormInputUrl = '<input type="hidden" name="ksd-attachments[url][]" value="' + attachment.url + '" />';
                            attachmentFormInputTitle = '<input type="hidden" name="ksd-attachments[size][]" value="' + attachment.filesizeHumanReadable + '" />';
                            attachmentFormInputSize = '<input type="hidden" name="ksd-attachments[filename][]" value="' + attachment.filename + '" />';
                            attachmentFormInput = attachmentFormInputUrl + attachmentFormInputTitle + attachmentFormInputSize;
                            jQuery('ul#' + targetUL).append('<li>' + attachmentLink + '<span class="ksd-close-dialog"></span>' + attachmentFormInput + '</li>');
                        });
                    });
                }
                this.window.open();
                return false;
            });
            //On clicking close, delete the attachment
            jQuery('#admin-kanzu-support-desk').on('click', '.ksd-close-dialog', function () {
                jQuery(this).parent().remove();
            });
        }//eof:newTicket()



        this.uiTabs = function () {

            /**For the tickets tabs**/
            jQuery("#ticket-tabs").tabs();

            /*Switch the active tab depending on what page has been selected*/
            activeTab = 0;
            switch (ksd_admin.admin_tab) {
                case "ksd-tickets":
                    activeTab = 1;
                    break;
                case "ksd-new-ticket":
                    activeTab = 2;
                    break;
                case "ksd-settings":
                    activeTab = 3;
                    break;
                case "ksd-addons":
                    activeTab = 4;
                    break;
                case "ksd-help":
                    activeTab = 5;
                    break;
            }
            //If we are in tour mode, activate the dashboard
            if (ksd_admin.ksd_tour_pointers.ksd_intro_tour) {
                activeTab = 0;
            }
            jQuery("#tabs").tabs("option", "active", activeTab);
            //Set the title
            jQuery('.admin-ksd-title h2').html(ksd_admin.admin_tab.replace("ksd-", "").replace(/\-/g, " "));

            /**AJAX: Send the AJAX request to change ticket owner on selecting new person to 'Assign to'**/
            jQuery("#ticket-tabs").on('click', '.ticket-actions ul.assign_to li', function () {
                KSDUtils.showDialog("loading");
                var tkt_id = jQuery(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var assign_assigned_to = jQuery(this).attr("id");
                jQuery.post(ksd_admin.ajax_url,
                        {action: 'ksd_assign_to',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                            tkt_id: tkt_id,
                            ksd_current_user_id: ksd_admin.ksd_current_user_id,
                            tkt_assign_assigned_to: assign_assigned_to
                        },
                function (response) {
                    var respObj = {};
                    //To catch cases when the ajax response is not json
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                        return;
                    }

                    //Check for error in request.
                    if ('undefined' !== typeof (respObj.error)) {
                        KSDUtils.showDialog("error", respObj.error.message);
                        return;
                    }
                    KSDUtils.showDialog( "success", respObj );
                });
            });



            /**Change the title onclick of a side navigation tab*/
            jQuery("#tabs .ksd-main-nav li a").click(function () {
                jQuery('.admin-ksd-title h2').html(jQuery(this).attr('href').replace("#", "").replace("_", " "));//Remove the hashtag, replace _ with a space
                if ("yes" === ksd_admin.enable_anonymous_tracking) {
                    KSDAnalytics.sendPageView(jQuery(this).attr('href').replace("#", "ksd-").replace("_", "-"));//Make it match the admin_tab format e.g. ksd-dashboard, ksd-tickets, etc
                }
            });

            /**Pre-populate the first tab in the tickets view*/
            if (jQuery("#tickets-tab-1").hasClass("pending")) {
                _this.getTickets("#tickets-tab-1");
            }
            /**Do AJAX calls for filtering tickets on click of any of the tabs**/
            jQuery("#ticket-tabs li a").click(function () {
                _this.getTickets(jQuery(this).attr('href'));
                //Hide the bulk sub menu if it is displayed. addClass checks if a class exists before adding it
                jQuery('.ticket-actions-top-menu').addClass('hidden');
            });

        };

        /*
         * Changes a ticket's status
         */
        this.changeTicketStatus = function ( tkt_id, tkt_status, singleTicketView ) {
            singleTicketView = ( 'undefined' === typeof singleTicketView ? false : true );
            KSDUtils.showDialog("loading");
            jQuery.post(ksd_admin.ajax_url,
                    {action: 'ksd_change_status',
                        ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                        tkt_id: tkt_id,
                        tkt_status: tkt_status
                    },
            function (response) {
                var respObj = {};
                //To catch cases when the ajax response is not json
                try {
                    //to reduce cost of recalling parse
                    respObj = JSON.parse(response);
                } catch (err) {
                    KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                    return;
                }

                //Check for error in request.
                if ('undefined' !== typeof (respObj.error)) {
                    KSDUtils.showDialog("error", respObj.error.message);
                    return;
                }
                KSDUtils.showDialog("success", respObj);
                if( !singleTicketView ){
                    _this.ksdRefreshTicketsPage();//Refresh the page
                }
            });

        };

        /**
         * Attach an event to the items that change ticket status
         */
        this.attachChangeTicketStatusEvents = function () {
            /**AJAX: Send the AJAX request when a new status is chosen**/
            jQuery("#ticket-tabs").on('click', '.ticket-actions ul.status li', function () {
                var tkt_id = jQuery(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var tkt_status = jQuery(this).attr("class");
                _this.changeTicketStatus(tkt_id, tkt_status);
            });

            /**Hide/Show the change ticket options on click of a ticket's 'change status' item**/
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.change_status', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                var tkt_id = jQuery(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                jQuery("#tkt_" + tkt_id + " ul.status").toggleClass("hidden");
                jQuery(this).parent().find(".ksd_agent_list").addClass("hidden");
                jQuery(this).parent().find("ul.severity").addClass("hidden");
            });

            /**In single ticket view, Hide/Show the change status options*/
            if (jQuery("#ksd-single-ticket").length) {
                jQuery(".ksd-top-nav").on('click', 'a.change_status', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    jQuery("ul.status").toggleClass("hidden");
                });
                jQuery(".ksd-top-nav ul.status").bind("mouseleave", function () {
                    jQuery(this).addClass('hidden');
                });
                jQuery(".ksd-top-nav").on('click', 'ul.status li', function () {
                    var tkt_id = jQuery.urlParam('ticket');
                    var tkt_status = jQuery(this).attr("class");
                    _this.changeTicketStatus(tkt_id, tkt_status, true );

                });
            }
        };
        this.attachAssignToEvents = function () {
            //---------------------------------------------------------------------------------
            /**Hide/Show the assign to options on click of a ticket's 'Assign To' item**/
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.assign_to', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                //jQuery(".ticket-actions a.change_status'").hide();
                var tkt_id = jQuery(this).parent().attr('id').replace("tkt_", ""); //Get the ticket ID
                jQuery("#tkt_" + tkt_id + " ul.ksd_agent_list").toggleClass("hidden");
                jQuery(this).parent().find(".status").addClass("hidden");
                jQuery(this).parent().find(".severity").addClass("hidden");

            });
            //Re-assign a ticket 
            jQuery("#ticket-tabs").on('click', '.ticket-actions ul.ksd_agent_list li', function () {
                var tkt_id = jQuery(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var assign_assigned_to = jQuery(this).attr("id");
                _this.reassignTicket(tkt_id, assign_assigned_to);
            });
            /**In single ticket view, Hide/Show the agent list when 'Assign to' is clicked*/
            if (jQuery("#ksd-single-ticket").length) {
                jQuery(".ksd-top-nav").on('click', 'a.assign_to', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    jQuery("ul.ksd_agent_list").toggleClass("hidden");
                });
                jQuery(".ksd-top-nav ul.ksd_agent_list").bind("mouseleave", function () {
                    jQuery(this).addClass('hidden');
                });
                jQuery(".ksd-top-nav").on('click', 'ul.ksd_agent_list li', function () {
                    var tkt_id = jQuery.urlParam('ticket');
                    var assign_assigned_to = jQuery(this).attr("id");
                    _this.reassignTicket(tkt_id, assign_assigned_to, true );
                });
            }
            ;
        };

        /**
         * Attach events to items used to change ticket read/unread status
         * @returns {undefined}
         */
        this.attachMarkReadUnreadEvents = function () {
            //Mark ticket read 
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.mark_read', function (event) {
                event.preventDefault();
                var tkt_id = jQuery(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.markTicketReadUnread(tkt_id, 1);
            });
            //Mark ticket unread
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.mark_unread', function (event) {
                event.preventDefault();
                var tkt_id = jQuery(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                _this.markTicketReadUnread(tkt_id, 0);
            });
            //In single page view 
            if (jQuery("#ksd-single-ticket").length) {
                jQuery(".ksd-top-nav").on('click', 'a.mark_unread', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    var tkt_id = jQuery.urlParam('ticket');
                    _this.markTicketReadUnread(tkt_id, 0, true );
                });
            }
            ;
        };

        /**
         * Attach events to the items used to change ticket severity
         */
        this.attachChangeSeverityEvents = function () {
            //Hide/Show the change severity menu in ticket grid
            jQuery("#ticket-tabs").on('click', '.ticket-actions a.change_severity', function (event) {
                event.preventDefault();//Important otherwise the page skips around
                var tkt_id = jQuery(this).attr('id').replace("tkt_", ""); //Get the ticket ID
                jQuery("#tkt_" + tkt_id + " ul.severity").toggleClass("hidden");
                jQuery(this).parent().find(".ksd_agent_list").addClass("hidden");
                jQuery(this).parent().find("ul.status").addClass("hidden");
            });
            /**AJAX: In ticket grid, change severity on click of a single ticket**/
            jQuery("#ticket-tabs").on('click', '.ticket-actions ul.severity li', function () {
                var tkt_id = jQuery(this).parent().parent().attr("id").replace("tkt_", "");//Get the ticket ID
                var tkt_severity = jQuery(this).attr("class");
                _this.changeTicketSeverity(tkt_id, tkt_severity );
            });

            /**In single ticket view, Hide/Show the severity list when 'Change Severity' is clicked*/
            if (jQuery("#ksd-single-ticket").length) {
                jQuery(".ksd-top-nav").on('click', 'a.change_severity', function (event) {
                    event.preventDefault();//Important otherwise the page skips around
                    jQuery("ul.severity").toggleClass("hidden");
                });
                jQuery(".ksd-top-nav ul.severity").bind("mouseleave", function () {
                    jQuery(this).addClass('hidden');
                });
                jQuery(".ksd-top-nav").on('click', 'ul.severity li', function () {
                    var tkt_id = jQuery.urlParam('ticket');
                    var tkt_severity = jQuery(this).attr("class");
                    _this.changeTicketSeverity(tkt_id, tkt_severity, true );
                });
            }
            ;
        };

        this.uiSingleTicketView = function () {
            /**AJAX: In single ticket view mode, get the current ticket's description, sender and subject and any private notes*/
            if (jQuery("#ksd-single-ticket .description").hasClass("pending")) {
                jQuery.post(ksd_admin.ajax_url,
                        {action: 'ksd_get_single_ticket',
                            ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                            tkt_id: jQuery.urlParam('ticket')//We get the ticket ID from the URL
                        },
                function (response) {
                    var respObj = {};
                    //To catch cases when the ajax response is not json
                    try {
                        //to reduce cost of recalling parse
                        respObj = JSON.parse(response);
                    } catch (err) {
                        KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                        return;
                    }

                    //Check for error in request.
                    if ('undefined' !== typeof (respObj.error)) {
                        KSDUtils.showDialog("error", respObj.error.message);
                        return;
                    }
                    the_ticket = respObj;
                    jQuery("#ksd-single-ticket h1.ksd-single-ticket-subject").html(the_ticket.tkt_subject);
                    jQuery("#ksd-single-ticket span.author").text(the_ticket.tkt_cust_id);//We use text, not HTML because the <> in the email would be misinterpreted
                    jQuery("#ksd-single-ticket span.date").html(the_ticket.tkt_time_logged);//@TODO Format this
                    jQuery("#ksd-single-ticket .description").removeClass("pending").html(the_ticket.tkt_message).text();
                    jQuery("#ksd-single-ticket textarea[name=tkt_private_note]").val(the_ticket.tkt_private_note);
                    //The Attachments
                    if (!jQuery.isEmptyObject(the_ticket.attachments)) {
                        jQuery("#ksd-single-ticket .description").append('<ul id="ksd-attachments"></ul>');
                        jQuery.each(the_ticket.attachments, function (key, attachment) {
                            jQuery('ul#ksd-attachments').append('<li><a href="' + attachment.attach_url + '">' + attachment.attach_filename + ' ( ' + attachment.attach_size + ' )</a>');
                        });
                    }
                    jQuery("#ticket-replies p.loading").html(ksd_admin.ksd_labels.msg_still_loading);
                    //Make the 'Back' button visible
                    jQuery(".top-nav li.back").removeClass("hidden");

                    //Now get the responses. For cleaner code and to remove reptition in the returned results, we use multiple
                    //queries instead of a JOIN. The impact on speed is negligible
                    jQuery.post(ksd_admin.ajax_url,
                            {action: 'ksd_get_ticket_replies',
                                ksd_admin_nonce: ksd_admin.ksd_admin_nonce,
                                tkt_id: jQuery.urlParam('ticket')//We get the ticket ID from the URL
                            },
                    function (the_replies) {
                        var respObj = {};
                        //To catch cases when the ajax response is not json
                        try {
                            //to reduce cost of recalling parse
                            respObj = JSON.parse(the_replies);
                        } catch (err) {
                            KSDUtils.showDialog("error", ksd_admin.ksd_labels.msg_error_refresh );
                            return;
                        }
                        the_replies = respObj;
                        //Check for error in request.
                        if ('undefined' !== typeof (respObj.error)) {
                            KSDUtils.showDialog("error", respObj.error.message);
                            return;
                        }

                        repliesData = "";
                        jQuery.each(respObj, function (key, value) {
                            repliesData += "<div class='ticket-reply'>";
                            repliesData += "<span class='reply_author'>" + value.rep_created_by + "</span>";
                            repliesData += "<span class='reply_date'>" + value.rep_date_created + "</span>";
                            repliesData += "<div class='reply_message'>" + value.rep_message + "</div>";
                            //The Reply's Attachments
                            if (!jQuery.isEmptyObject(value.attachments)) {
                                repliesData += '<ul id="ksd-attachments">';
                                jQuery.each(value.attachments, function (key, attachment) {
                                    repliesData += '<li><a href="' + attachment.attach_url + '">' + attachment.attach_filename + ' ( ' + attachment.attach_size + ' )</a></li>';
                                });
                                repliesData += '</ul>';
                            }
                            repliesData += "</div>";
                        });
                        jQuery("#ticket-replies").html(repliesData);
                        //Toggle the color of the reply background
                        // jQuery("#ticket-replies div.ticket-reply").filter(':even').addClass("alternate");
                        //Clean-up the replies to make them more user--friendly
                        _this.formatTicketReplies();
                        //Scroll to the bottom
                        jQuery('html, body').animate({scrollTop: jQuery(".edit-ticket").offset().top}, 1400, "swing");
                    });
                });
            }
        };//eof:this.uiSingleTicketView





        _toggleFieldValues = function () {

            /**Toggle the form field values for new tickets on click**/
            function toggle_form_field_input(event) {
                if (jQuery(this).val() === event.data.old_value) {
                    jQuery(this).val(event.data.new_value);
                }
            }
            ;
            //The fields
            var new_form_fields = {
                "ksd_tkt_subject": ksd_admin.ksd_labels.tkt_subject,
                "ksd_cust_fullname": ksd_admin.ksd_labels.tkt_cust_fullname,
                "ksd_cust_email": ksd_admin.ksd_labels.tkt_cust_email
            };
            //Attach events to the fields  
            jQuery.each(new_form_fields, function (field_name, form_value) {
                jQuery('form.ksd-new-ticket-admin input[name=' + field_name + ']').on('focus', {
                    old_value: form_value,
                    new_value: ""
                }, toggle_form_field_input);
                jQuery('form.ksd-new-ticket-admin input[name=' + field_name + ']').on('blur', {
                    old_value: "",
                    new_value: form_value
                }, toggle_form_field_input);
            });
        };

        this.TicketPagination = function () {


            //start:Limit
            //Removed mouseout. Was sending multiple AJAX calls at the same time. 
            jQuery(".ksd-pagination-limit").bind("mouseleave", function () {
                var limit = jQuery(this).val();

                var tab_id = jQuery(this).attr("id").replace("ksd_pagination_limit_", "");
                var search_text = jQuery("input[name=ksd_tkt_search_input_" + tab_id + "]").val();
                var tab_id_name = "#tickets-tab-" + tab_id;
                //alert("limit:" + limit + " search:" + search_text);
                jQuery(tab_id_name).addClass("pending");
                _this.getTickets("#tickets-tab-" + tab_id, search_text, limit, 0, true);

            });


            jQuery(".ksd-pagination-limit").bind("keypress", function (e) {
                if (e.keyCode === 13) { //Enter key
                    var limit = jQuery(this).val();

                    var tab_id = jQuery(this).attr("id").replace("ksd_pagination_limit_", "");
                    var search_text = jQuery("input[name=ksd_tkt_search_input_" + tab_id + "]").val();
                    var tab_id_name = "#tickets-tab-" + tab_id;
                    //alert("limit:" + limit + " search:" + search_text);
                    jQuery(tab_id_name).addClass("pending");
                    _this.getTickets("#tickets-tab-" + tab_id, search_text, limit, 0, true);
                }


            });
            //End:Limit

        };


        //AJAX:: When the refresh button is hit
        this.ksdRefreshTicketsPage = function () {
            var tab_id = jQuery("#ticket-tabs").tabs("option", "active");
            var currentTabID = "#tickets-tab-" + ++tab_id;//Get the tab we are in. tab_id is from a zero-based index so we first increment it to get the correct ID
            var limit = jQuery(currentTabID + " .ksd-pagination-limit").val();
            var search_text = jQuery(currentTabID + " .ksd_tkt_search_input").val();//Get val from the class on the input field, no need for ID
            jQuery(currentTabID).addClass("pending");
            var curPage = _getCurrentPage(tab_id);
            _this.getTickets(currentTabID, search_text, limit, curPage - 1, true);
        };

        this.attachRefreshTicketsPage = function () {
            jQuery('.ksd-ticket-refresh button').click(function () {
                _this.ksdRefreshTicketsPage( );
            });
        };

        this.TicketSearch = function () {

            jQuery(".ksd-tkt-search-btn").click(function () {
                var tab_id = jQuery(this).attr("id").replace("ksd_tkt_search_btn_", "");
                var search_text = jQuery("input[name=ksd_tkt_search_input_" + tab_id + "]").val();
                var tab_id_name = "#tickets-tab-" + tab_id;

                //get pagination
                var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();

                jQuery(tab_id_name).addClass("pending");
                _this.getTickets("#tickets-tab-" + tab_id, search_text, limit, 0, true);

            });

            jQuery(".ksd_tkt_search_input").bind("keypress", function (e) {
                if (e.keyCode === 13) { //Enter key
                    var tab_id = jQuery(this).attr("name").replace("ksd_tkt_search_input_", "");
                    var search_text = jQuery("input[name=ksd_tkt_search_input_" + tab_id + "]").val();
                    var tab_id_name = "#tickets-tab-" + tab_id;
                    //get pagination
                    var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();

                    jQuery(tab_id_name).addClass("pending");
                    _this.getTickets("#tickets-tab-" + tab_id, search_text, limit);
                }
            });

        };


        _getTabId = function (tab_id) {
            var tab_id_name = "#tickets-tab-" + tab_id;
            return tab_id_name;
        };
        /*Add effects to ticket row
         * Add border to the ksd-row-ctrl table row
         * */
        RowCtrlEffects = function () {

            jQuery(".ksd-row-ctrl").bind("hover mouseover focus", function () {

                var id = jQuery(this).attr("id");
                var tkt_id = jQuery(this).attr("id").replace("ksd_tkt_ctrl_", "");
                jQuery("#ksd_tkt_id_" + tkt_id).addClass("ksd-row-ctrl-hover");


            });

            jQuery(".ksd-row-ctrl").mouseout(function () {
                var id = jQuery(this).attr("id");
                var tkt_id = jQuery(this).attr("id").replace("ksd_tkt_ctrl_", "");
                jQuery("#ksd_tkt_id_" + tkt_id).removeClass("ksd-row-ctrl-hover");
            });


            /*All checkbox**/
            jQuery("#ticket-tabs .tkt_chkbx_all").on("click", function () {
                //TODO:Show all options
                if (jQuery(this).prop('checked') === true) {
                    jQuery("#tkt_all_options").removeClass("ticket-actions");
                    jQuery('input:checkbox').not(this).prop('checked', this.checked);

                    //
                    tab_id = jQuery(this).attr("id").replace("tkt_chkbx_all_", "");
                    jQuery("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-hide').addClass("ksd-row-all-show");


                } else {
                    jQuery("#tkt_all_options").addClass("ticket-actions");
                    jQuery('input:checkbox').not(this).prop('checked', this.checked);

                    tab_id = jQuery(this).attr("id").replace("tkt_chkbx_all_", "");
                    jQuery("#ksd_row_all_" + tab_id).removeClass('ksd-row-all-show').addClass("ksd-row-all-hide");
                }

            });

        };


        /*
         * 
         * @param {type} tab_id
         * @returns {undefined}
         */
        _getCurrentPage = function (tab_id) {
            var curpage = jQuery("#ksd_pagination_" + tab_id + " ul li .current-nav").html();
            //return (KSDUtils.isNumber(curpage)) ? curpage : 1;
            return parseInt(curpage);
        };


        _getPagLimt = function (tab_id) {
            var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();
            return limit;
        };

        /**
         * Renders the table pagination
         * 
         * @param {type} tab_id
         * @param {type} current_page
         * @param {type} total_results
         * @param {type} limit
         * @returns {undefined}
         */
        _loadTicketPagination = function (tab_id, current_page, total_results, limit) {

            //@TODO: Why is this coming as o instead of 0.
            if (total_results === "o" || total_results === "0")
                return;
            var pages = (total_results / limit);
            jQuery("#ksd_pagination_" + tab_id + " ul li").remove();
            jQuery("#ksd_pagination_" + tab_id + " ul").append('\
                        <li><a rel="external" href="#"><<</a></li>  \
                        <li><a rel="external" href="#"><</a></li>');

            for (i = 0; i < pages; i++) {
                currentclass = (i === current_page - 1) ? "current-nav" : "";
                ii = i + 1;
                jQuery("#ksd_pagination_" + tab_id + " ul").append(' \
                            <li><a rel="external" href="#" class="' + currentclass + '">' + ii + '</a></li> \
                        ');
            }

            jQuery("#ksd_pagination_" + tab_id + " ul").append('\
                        <li><a rel="external" href="#">></a></li>  \
                        <li><a rel="external" href="#">>></a></li>');


            //Attach click events
            jQuery("#ksd_pagination_" + tab_id + " ul li a").click(function () {
                var cpage = jQuery(this).html();
                var current_page = _getCurrentPage(tab_id);
                var limit = _getPagLimt(tab_id);
                var pages = Math.ceil(total_results / limit);

                //Prev, Next
                if (cpage === ">" || cpage === "&gt;") {
                    cpage = current_page + 1;
                }
                if (cpage === ">>" || cpage === '&gt;&gt;') {
                    cpage = Math.ceil(total_results / limit);
                }
                if (cpage === "<" || cpage === '&lt;') {
                    cpage = current_page - 1;
                }
                if (cpage === "<<" || cpage === '&lt;&lt;') {
                    cpage = 1;
                }

                if (cpage < 1 || cpage > pages || cpage === current_page) {
                    return;
                }

                //get pagination
                var limit = jQuery("#ksd_pagination_limit_" + tab_id).val();

                //search
                var search_text = jQuery("input[name=ksd_tkt_search_input_" + tab_id + "]").val();

                jQuery(_getTabId(tab_id)).addClass("pending");
                _this.getTickets(_getTabId(tab_id), search_text, limit, cpage - 1);

            });
        };

    };



    //Analytics
    KSDAnalytics.init();

    //Settings
    Settings = new KSDSettings();
    Settings.init();

    //Dashboard
    Dashboard = new KSDDashboard();
    Dashboard.init();

    //Help
    KSDHelpObj = new KSDHelp();
    KSDHelpObj.init();

    //Tickets
    Tickets = new KSDTickets();
    Tickets.init();


});
