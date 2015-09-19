=== Kanzu Support Desk ===
Contributors: kanzucode
Donate link: https://kanzucode.com/
Tags: admin,administration,customer service,ticket,case,system,support,help,helpdesk,ticket system,support system,crm,contact
Requires at least: 3.0.1
Tested up to: 4.3
Stable tag: 2.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Kanzu Support Desk (KSD) is a customer service ( support ticket ) solution that allows you to respond effectively to every customer query in under 5 minutes.

== Description ==

Great customer care is at the heart of every good product or service. Kanzu Support Desk breathes fresh life into ticketing solutions
so that you and your team can focus on what you do best-being awesome. This nifty and pretty plugin boasts of a native WordPress 
interface so you don't have to worry about a learning curve-get up and running immediately!
We do the heavy-lifting for you so you can focus on your customers. KSD was built with **SIMPLICITY** in the driving seat. 
You'll love how simple and powerful it is. Ok ok, enough already! So...

What's under the hood? Let's see:

* Very simple user interface
* Multiple channels for ticket-creation: Front-end via a support tab, backend via a pretty form, via Email as an optional add-on
* Unlimited number of agents supported
* Beautiful graphs to let you in on your performance
* You and your customers receive email notifications on ticket creation
* Private notes support on tickets
* Simple ticket re-assignment 
* Ticket severity supported 
* Translation-ready 

This isn't a feature but it is worth mentioning that a great support desk plugin ought to have an awesome support team behind it. This one does,
if we can say so ourselves :-)

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'kanzu-support-desk'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `kanzu-support-desk.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `kanzu-support-desk.zip`
2. Extract the `kanzu-support-desk` directory to your computer
3. Upload the `kanzu-support-desk` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= After activation =
You'll be redirected to **Kanzu Support Desk > Dashboard**. This'll display your performance statistics when you receive new tickets. Navigate to **Kanzu Support Desk > Settings** to change your settings.
Add shortcode **[ksd_support_form]** anywhere you want the support form to be displayed to your customers.

To add CAPTCHA to the form, go [here](https://www.google.com/recaptcha/admin), get Google reCAPTCHA keys and then add them to your KSD settings

Please check out [our documentation here](https://kanzucode.com/documentation/wordpress-customer-service-plugin-ksd-getting-started/) for a more detailed walk-through should you need it

= Follow the action =
Be an active part of the plugin growth by getting involved in the [KSD Roadmap](https://trello.com/b/zLkHwBz2/kanzu-support-desk-development)<br />
Contribute to the dev process on [GitHub](https://github.com/kanzucode/kanzu-support-desk)<br />
Get the latest in KSD and WP in general from [@KanzuCode on Twitter](https://twitter.com/KanzuCode)

== Frequently Asked Questions ==

= Can I run KSD on my .wordpress.com site? =

No, you cannot unfortunately

= Can my customers log tickets by sending me an email? =

With an optional add-on activated, yes they can. By default though, they can use a form on your website

= Where can I find KSD documentation and user guides? =

For help setting-up and configuring KSD, please refer to our [user guide](https://kanzucode.com/documentation/wordpress-customer-service-plugin-ksd-getting-started/)

= Where can I get support? =

If you get stuck, please ask for help on the [KSD Support Forum](http://kanzucode.com/forums/forum/wp-kanzu-support-desk/)

= Will KSD work with my theme? =

Yes; KSD will work with any theme

== Screenshots ==

1. The dashboard showing ticket volumes
2. Creation of a new ticket from the back-end
3. The settings panel
4. The ticket grid. 
5. The details of a single ticket and its corresponding replies
6. Changing a ticket's status
7. Private note support

== Changelog ==
= 2.0.4, September 19, 2015 =
 * Support CCs in tickets
 * BUG FIX | Do add-on updates seamlessly

= 2.0.3, September 05, 2015 =
 * BUG FIX | Generate debug file correctly

= 2.0.2, September 05, 2015 =
 * BUG FIX | Agent could not send a reply
 * Add reply count to ticket grid

= 2.0.1, September 03, 2015 =
 * Correct 'Settings' link in plugins screen
 * Remove logic for KSD < 1.5.0
 * Fix access permissions: Unauthenticated user shouldn't see ticket(s)

= 2.0.0, August 29, 2015 =
 * Overhaul: Switched from custom tables to custom post types for all ticket info
 * Customers can reply and follow ticket progress from your website 
 * Customers required to create accounts before submitting tickets

= 1.7.0, July 29, 2015 =
 * Better notifications on new tickets/replies
 * Ticket cc feature added
 * Internationalized validation error messages  
 * Added KSD customer buttons to settings
 * Added 'Generate debug file' to settings
 * Ticket & Replies formatted for display and email sending. Better HTML support
 * Auto-reply HTML support added
 * Optional notification email
 * KSD now has a Logo

= 1.6.8, July 17, 2015 =
 * BUG FIX | Send notifications on new ticket logged
 * Mail reply wrap updated

= 1.6.7, June 30, 2015 =
 * Intro tour updated. Adds intro message from CEO
 * Tracking message updated to show usage & error stats

= 1.6.6, June 27, 2015 =
 * BUG FIX | Save plugin license info correctly

= 1.6.5, June 26, 2015 =
 * BUG FIX | Plugin activation/deactivation fixed

= 1.6.4, June 25, 2015 =
 * Make support button text configurable
 * BUG FIX | Allow Google Analytics disabling/activation
 * BUG FIX | Track only KSD pages
 * Support HTML email replies
 * Highlight add-on submenu, populate with extra add-ons

= 1.6.3, June 24, 2015 =
 * BUG FIX | Show refresh message when nonce expires
 * Plugin updates handled by KSD plugin

= 1.6.2, June 10, 2015 =
 * Mark tickets as read/unread
 * Sort tickets by last time updated
 * Add customer email in single ticket view

= 1.6.1, June 01, 2015 =
 * BUG FIX | Ticket logging without Google reCAPTCHA fixed. Cost us all growth thus far

= 1.6.0, May 27, 2015 =
 * Added attachments to tickets & replies
 * Bulk update options (change status, severity, re-assign, delete ) added
 * HTML replies supported
 * Internationalization of single ticket view options
 * Ticket grid default list increased to 20 from 5

= 1.5.5, May 16, 2015 =
 * Change of ticket status colors to more intuitive ones
 * Addition of pre-ticket logging filter
 * Support for the KSD Rules add-on

= 1.5.4, April 15, 2015 =
 * Ticket importation from CSV files added
 * Renamed show support tab and updated explanation
 * Notify primary admin on new ticket creation
 * Internalization strings updated to use single quotes

= 1.5.3, March 21, 2015 =
 * BUG FIX | Better support for localization
 * Customers table no longer created at installation

= 1.5.2, February 25, 2015 =
 * BUG FIX | Get correct role-based agent list in single ticket view

= 1.5.1, February 24, 2015 =
 * BUG FIX | Added missing icons (more_top,ellipsis), updated loading_dialog.GIF to loading_dialog.gif
 
= 1.5.0, February 24, 2015 =
 * Added auto-assign feature for new tickets
 * Migrated customers from KSD customers table to wp_users
 * Role-based ticket management added

= 1.4.0, February 10, 2015 =
 * Added Analytics
 * Added sweet notifications panel
 * Added client-side validation for Google reCAPTCHA  
 * Introductory tour updated to be more user-friendly
 * Fixed typo. occured updated to occurred 
 * Documentation links updated

= 1.3.1, February 05, 2015 =
 * CAPTCHA added to front-end form

= 1.3.0, January 31, 2015 =
* BUG FIX | Saving checkbox settings corrected
* [ksd_support_form] shortcode added!
* Edit ticket options (Change status, severity, owner) in single ticket view

= 1.2.1, January 26, 2015 =
* BUG FIX | Save messages & replies containing apostrophes properly
* Style single ticket view, delete dialog
* Update documentation URLs

= 1.2.0, January 24, 2015 =
* Default tickets pre-populated on installation
* In tickets, show total number of tickets in each ticket filter
* Severity and status indicators added
* BUG FIX | Sanitization of ticket message and replies now done to allow HTML content
* 'NEW' ticket status added, 'ASSIGNED' removed from available ticket status options
* In tickets, show 'Loading' dialog on initial load and on filter selection
* 'New Ticket' tab re-arranged for easier use
* Dashboard summary statistics re-styled and made clickable
* Dashboard graph date format changed to DD-MM-YYYY
* Ticket grid re-styled to highlight ticket subject & OPEN tickets
* On the ticket grid, added number of replies per ticket 

= 1.1.3 =
* BUG FIX | Eliminated subject/message length error returned for tickets not logged by add-ons  

= 1.1.2 =
* BUG FIX | Removed JSON_NUMERIC_CHECK which is only supported in PHP >=5.3
* BUG FIX | Dashboard graph wasn't being generated on sites with SSL (HTTPS)

= 1.1.1 =
* BUG FIX | MySQL <=5.5 tables weren't being created
* Proper styling for the settings view
* Gracefully handle errors in dashboard AJAX response 

= 1.1.0 =
* Introductory tour on activation
* 1/12/14 Tickets logged by an action
* Feedback form added to help tab
* Newsletter opt-in added
* Add-on list retrieved from KSD add-on feed

= 1.0.0, November 21, 2014 =
* Launched.

== Upgrade Notice ==
= 2.0.3 =
 * BUG FIX | Generate debug file correctly

= 2.0.2 =
 * BUG FIX | Agent could not send a reply
 * Add reply count to ticket grid

= 2.0.1 =
 * Correct 'Settings' link in plugins screen
 * Remove logic for KSD < 1.5.0

= 2.0.0 =
 * Customers can view and follow ticket progress from your website

= 1.6.8 =
 * BUG FIX | Notification on new tickets. Mail ticket reply wrapping improved

= 1.6.7 =
 * Intro message updated, tracking label and message also updated 

= 1.6.6 =
 * BUG FIX | Save plugin license information correctly

= 1.6.5 =
 * BUG FIX | Plugin activation/deactivation fixed

= 1.6.3 =
 * Support button text made configurable. Bug fixes & add-on list

= 1.6.3 =
 * BUG FIX | Show refresh message when nonce expires

= 1.6.2 =
 * Mark tickets as read/unread

= 1.6.1 =
 * Ticket logging without Google reCAPTCHA fixed

= 1.6.0 =
 * Attachments now supported. Bulk changes to tickets supported

= 1.5.5 =
 * Change of ticket status colors to more intuitive ones

= 1.5.4 =
 * Ticket importation from CSV added, notify primary admin on new ticket creation

= 1.5.3 =
 * BUG FIX | Better support for localization

= 1.5.2 =
 * BUG FIX | Get correct role-based agent list in single ticket view

= 1.5.1 =
 * BUG FIX | Added missing icons (more_top,ellipsis), updated loading_dialog.GIF to loading_dialog.gif
 
= 1.5.0 =
 * Auto-assign feature for new tickets, role-based ticket management

= 1.4.0 =
* New Notifications panel to keep you updated & client-side validation for Google reCAPTCHA

= 1.3.1 =
* CAPTCHA form added to front-end form

= 1.3.0 =
* [ksd_support_tab] short code added,single ticket view ticket edits added

= 1.2.1 =
* BUG FIX | Save messages & replies containing apostrophes properly, style single ticket & delete dialog

= 1.2.0 =
* Ticket grid re-styled to be prettier and more intuitive, dashboard summary statistics bolder & clickable

= 1.1.3 =
* BUG Fix - Eliminated message/subject length error on logging new tickets 

= 1.1.2 =
* Support for PHP < 5.3 added, support for graphs on sites with SSL (HTTPS)

= 1.1.1 =
* Create KSD tables, gracefully handle errors in dashboard AJAX response & better styling for settings

= 1.1.0 =
* Feedback options added, optional add-ons updated, intro tour on activation

= 1.0.0 =
* Join the Kanzu Support club
