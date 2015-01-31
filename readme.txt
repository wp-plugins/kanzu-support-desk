=== Kanzu Support Desk ===
Contributors: kanzucode
Donate link: http://kanzucode.com/
Tags: ticket,case,system,support,help,helpdesk,ticket system,support system,crm,contact
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Kanzu Support Desk (KSD) is an all-in-one support desk (ticketing) solution that looks and feels like email.

== Description ==

Great customer care is at the heart of every good product or service. Kanzu Support Desk breathes fresh life into ticketing solutions
so that you and your team can focus on what you do best-being awesome. This nifty and pretty plugin boasts of a very intuitive 
interface that feels like email so you don't have to worry about a learning curve-get up and running immediately!
We do the heavy-lifting for you so you can focus on your customers. KSD was built with **SIMPLICITY** in the driving seat. 
You'll love how simple and powerful it is. Ok ok, enough already! So...

What's under the hood? Let's see:

* Multiple channels for ticket-creation: Front-end via a support tab, backend via a pretty form, via Email as an optional add-on
* Unlimited number of agents supported
* Beautiful graphs to let you in on your performance
* Customers receive email notifications on ticket creation
* Multiple customizable ticket views 
* Private notes on tickets
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
Please check out [our documentation here](http://kanzucode.com/work/kanzu-support-desk-docs/) for a more detailed walk-through should you need it

== Frequently Asked Questions ==

= Can I run KSD on my .wordpress.com site? =

No, you cannot unfortunately

= Can my customers log tickets by sending me an email? =

With an optional add-on activated, yes they can. By default though, they can use a form on your website

= Where can I find KSD documentation and user guides? =

For help setting-up and configuring KSD, please refer to our [user guide](http://kanzucode.com/work/kanzu-support-desk-docs/)

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