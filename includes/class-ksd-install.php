<?php
/**
 * Holds all installation & deactivation-related functionality.  
 * On activation, activate is called.
 * On de-activation, 
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Install' ) ) :

class KSD_Install {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
        
        /**
         * The DB version
         * @since 1.5.0
         * @var int
         */
        protected static $ksd_db_version = 100;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() { 
		//Re-direct on plugin activation
		add_action( 'admin_init', array( $this, 'redirect_to_dashboard'    ) );

                //Upgrade settings when the plugin is upgraded
                add_filter( 'ksd_upgrade_settings',  array( $this, 'upgrade_settings' ) );
               
                //Upgrade everything else apart from plugin settings 
                add_action('ksd_upgrade_plugin',array( $this, 'upgrade_plugin' ));
               
	}
 
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 */
	public static function activate() { 

            // Bail if activating from network, or bulk. @since 1.1.0
            if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
            }
            
            //Check for re-activation.  
            $settings   =   Kanzu_Support_Desk::get_settings();
            if ( isset( $settings['kanzu_support_version'] ) ){//Reactivation or upgrade
                if ( $settings['kanzu_support_version'] == KSD_VERSION ) {//Bail out if it's a re-activation
                    return;
                }          
                //Check if it's an upgrade. If it is, run the updates. @since 1.1.0
                if ( $settings['kanzu_support_version'] != KSD_VERSION ) { 
                    do_action ( 'ksd_upgrade_plugin', $settings['kanzu_support_version'] );//Holds all upgrade-related changes except changes to the settings. We send the current version to the action           
                    $settings['kanzu_support_version'] =  KSD_VERSION;   //Update the version
                    $upgraded_settings = apply_filters( 'ksd_upgrade_settings', $settings );
                    Kanzu_Support_Desk::update_settings( $upgraded_settings );                            
                    set_transient( '_ksd_activation_redirect', 1, 60 * 60 );// Redirect to welcome screen
                    return;
                 }
            }
            else{
                //This is a new installation. Yippee! 
                self::create_tables();
                self::set_default_options(); 	
                self::log_initial_tickets();
                self::create_roles();//@since 1.5.0                        
                set_transient( '_ksd_activation_redirect', 1, 60 * 60 );// Redirect to welcome screen
            }
            
	}
        
        
        /**
         * Do de-activation stuff. Currently, doesn't do a thing
         */
        public static function deactivate (){
            //Currently doesn't do a thing
        }        
        
                
       /**
	 * Redirect to a welcome page on activation
	 */
	public static function redirect_to_dashboard(){
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_ksd_activation_redirect' ) ){
			return;
                }
		// Delete the redirect transient
		delete_transient( '_ksd_activation_redirect' );
 
		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) ){
			return;
                }
		if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'kanzu-support-desk.php' ) ) ){
			return;
                }
		wp_redirect( admin_url( 'admin.php?page='.KSD_SLUG ) );
		exit;		
	}
        
        /**
         * Upgrade the plugin's settings
         * @param Array $settings The current plugin settings
         * @return Array $settings The upgraded settings array
         * @since 1.1.0
         */
        public function upgrade_settings( $settings ){
            //Compare the user's current settings array against our new default array and pick-up any settings they don't have 
            //We'd have loved to use array_diff_key for this but it only exists for PHP 5 >= 5.1.0
            //For any setting that doesn't exist, we define it and assign it the default value @since 1.5.0
            foreach ( self::get_default_options() as $setting_key => $setting_default_value ){
                if( !isset( $settings[$setting_key] ) ){
                   $settings[$setting_key] =  $setting_default_value;
                }
            }
            return $settings;
        }
        
        /**
         * During plugin upgrade, this makes all the required changes
         * apart from plugin setting changes which are done by {@link upgrade_settings}
         * Note that any changes to the DB are reflected by an increment in the Db number
         * @param {string} $previous_version The previous version
         * @since 1.2.0
         */
        public function upgrade_plugin( $previous_version ){
            global $wpdb;  
            $wpdb->hide_errors();	
            //@since 1.5.0 Switch case based on target version removed. @TODO Update this to be version conscious
            $dbChanges = array();//Holds all DB change queries
            //Add 'NEW' to tkt_status ENUM, change the default tkt_status from 'OPEN' to 'NEW'
            $dbChanges[]="ALTER TABLE `{$wpdb->prefix}kanzusupport_tickets` CHANGE `tkt_status` `tkt_status` ENUM('NEW','OPEN','ASSIGNED','PENDING','RESOLVED') DEFAULT 'NEW';";
            
            //Drop the foreign key constraint. With it, the KSD user won't be able to delete WP users
            $dbChanges[]="ALTER TABLE `{$wpdb->prefix}kanzusupport_tickets` DROP FOREIGN KEY `tkts_custid_fk`;";            
            
            //Change tkt_cust_id's attributes to match those of WP_users.ID
            $dbChanges[]="ALTER TABLE `{$wpdb->prefix}kanzusupport_tickets` CHANGE `tkt_cust_id` `tkt_cust_id` BIGINT(20) UNSIGNED NOT NULL;";
            
            //Create new roles
            self::create_roles();            
       
            if( count( $dbChanges ) > 0 ){  //Make the Db changes. We use $wpdb->query instead of dbDelta because of
                                            //how strict and verbose the dbDelta alternative is. We'd
                                            //need to rewrite CREATE table statements for dbDelta.
                  foreach ( $dbChanges as $query ){
                        $wpdb->query( $query );     
                  }
            }
            //Migrate customers from our customers table to WP_users. We do this  after deleting the foreign key
            $this->move_customers_to_wp_users();     

        }
 
       /**
	* Create KSD tables
        * @TODO Test new installation with this new FK constraint
        * @since 1.0.0
	*/
        private static function create_tables() {
            global $wpdb;        
		$wpdb->hide_errors();		            
                //@since 1.5.3 customers table removed
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
                $kanzusupport_tables = "
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`tkt_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`tkt_subject` VARCHAR(512) NOT NULL,                                 
				`tkt_message` TEXT NOT NULL,
                                `tkt_message_excerpt` TEXT NOT NULL, 
				`tkt_channel` ENUM('STAFF','FACEBOOK','TWITTER','SUPPORT_TAB','EMAIL','CONTACT_FORM') DEFAULT 'STAFF',
				`tkt_status` ENUM('NEW','OPEN','ASSIGNED','PENDING','RESOLVED') DEFAULT 'NEW',
				`tkt_severity` ENUM ('URGENT', 'HIGH', 'MEDIUM','LOW') DEFAULT 'LOW', 
				`tkt_time_logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 				
                                `tkt_cust_id` BIGINT(20) UNSIGNED NOT NULL, 
                                `tkt_assigned_by` BIGINT(20) NOT NULL, 
                                `tkt_assigned_to` BIGINT(20) NULL, 
				`tkt_time_updated` TIMESTAMP NULL, 
				`tkt_updated_by` BIGINT(20) NOT NULL,                                 
				`tkt_private_note` TEXT,
                                KEY (`tkt_assigned_to`,`tkt_assigned_by`,`tkt_cust_id`)
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`rep_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`rep_tkt_id` BIGINT(20) NOT NULL ,
				`rep_type` INT ,/*@TODO To hold forwards*/
				`rep_is_cc` BOOLEAN DEFAULT FALSE,
				`rep_is_bcc` BOOLEAN DEFAULT FALSE,
				`rep_date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`rep_created_by` BIGINT(20) NOT NULL,
				`rep_date_modified` TIMESTAMP NULL,
				`rep_message` TEXT NOT NULL,
                                CONSTRAINT `rep_tktid_fk`
                                FOREIGN KEY (`rep_tkt_id`) REFERENCES {$wpdb->prefix}kanzusupport_tickets(`tkt_id`)
                                ON DELETE CASCADE 
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_assignments` (
				`assign_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`assign_tkt_id` BIGINT(20),
				`assign_assigned_to` BIGINT(20),
				`assign_date_assigned` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`assign_assigned_by` BIGINT(20),
                                CONSTRAINT `assign_tktid_fk`
                                FOREIGN KEY ( `assign_tkt_id` ) REFERENCES {$wpdb->prefix}kanzusupport_tickets(`tkt_id`)
                                ON DELETE CASCADE 
				);
                                ";
      dbDelta($kanzusupport_tables);                     
 }
 
             private static function set_default_options() {                    
                
                 add_option( KSD_OPTIONS_KEY, self::get_default_options() );
                    
            }
            
            /**
             * Create custom user roles
             * @since 1.5.0
             */
            private static function create_roles(){
                add_role( 'ksd_customer', __( 'KSD Customer', 'kanzu-support-desk' ), array(
				'read' 		=> true,
				'edit_posts' 	=> false,
				'delete_posts' 	=> false
			) );
            }
            
            /**
             * Migrate customers from {$wpdb->prefix}kanzusupport_customers to {$wpdb->prefix}users
             * @since 1.5.0
             */
            private function move_customers_to_wp_users(){            
               require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );
               $ksd_admin =  KSD_Admin::get_instance();
               $ksd_admin->do_admin_includes();
               $CC = new KSD_Customers_Controller();
               $filter = "%d";
               $value_parameters[] = 1;
               $all_customers = $CC->get_customers( $filter, $value_parameters );//Get all the customers
               
               foreach( $all_customers as $customer ){
                    $username = sanitize_user( preg_replace('/@(.)+/','',$customer->cust_email ) );//Derive a username from the emailID
                    //Ensure username is unique. Adapted from WooCommerce
                    $append     = 1;
                    $new_username = $username;
                    
                    while ( username_exists( $username ) ) { 
			$username = $new_username . $append;
			$append ++;
                    }
                    $password = wp_generate_password();//Generate a random password                   
                    
                    $userdata = array(
                        'user_login'    => $username,
                        'user_pass'     => $password,  
                        'user_email'    => $customer->cust_email,
                        'display_name'  => empty( $customer->cust_lastname ) ? $customer->cust_firstname : $customer->cust_firstname.' '.$customer->cust_lastname,
                        'first_name'    => $customer->cust_firstname,
                        'role'          => 'ksd_customer',
                        'last_name'     => $customer->cust_lastname
                    );
                    $user_id = wp_insert_user( $userdata ) ;
                    if( !is_wp_error($user_id) ) {
                        //Get all tickets created by this customer and update the ID
                        global $wpdb;
                        $TC = new KSD_Tickets_Controller(); 
                        $update_query = "UPDATE `{$wpdb->prefix}kanzusupport_tickets` SET `tkt_cust_id` = {$user_id} WHERE `tkt_cust_id` = {$customer->cust_id};";
                        $TC->exec_query( $update_query );//Chose to use a custom query since this is just a one-off
                    }
               }
            }
            
            /**
             * Get default settings
             */
            public static function get_default_options(){
                $user_info = get_userdata(1);//Get the admin user's information. Used to set default email
                return  array (
                        /** KSD Version info ********************************************************/
                        'kanzu_support_version'             => KSD_VERSION,
                        'kanzu_support_db_version'          => self::$ksd_db_version,
                    
                        /** Tickets **************************************************************/
                    
                        'enable_new_tkt_notifxns'           => "yes",
                        'ticket_mail_from_name'             => $user_info->display_name,//Defaults to the admin display name 
                        'ticket_mail_from_email'            => $user_info->user_email,//Defaults to the admin email
                        'ticket_mail_subject'               => __("Your support ticket has been received","kanzu-support-desk"),
                        'ticket_mail_message'               => __("Thank you for getting in touch with us. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk"),
                        'recency_definition'                => "1",
                        'show_support_tab'                  => "yes",
                        'tab_message_on_submit'             => __("Thank you. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk"),
                        'tour_mode'                         => "yes", //@since 1.1.0
                        'enable_recaptcha'                  => "no",//@since 1.3.1 Not on by default since user needs to create & provide reCAPTCHA site & secret keys
                        'recaptcha_site_key'                => "",
                        'recaptcha_secret_key'              => "",
                        'recaptcha_error_message'           => sprintf ( __("Sorry, an error occurred. If this persists, kindly get in touch with the site administrator on %s","kanzu-support-desk"), $user_info->user_email ),
                        'enable_anonymous_tracking'         => "no", //@since 1.3.2,
                        'auto_assign_user'                  => '',   //@since 1.5.0. Used to auto-assign new tickets when set 
                        'ticket_management_roles'           => 'administrator' //@since 1.5.0. Who can manage your tickets
                    );
            }
            

            /**
             * Log initial tickets so that dashboard line graph shows and user
             * gets more details on the product
             */
            public static function log_initial_tickets ( ){
                
                global $current_user;
                get_currentuserinfo();
                
                
                $email          = $current_user->user_email;
                $fullname       = $current_user->user_firstname.' ' .$current_user->user_lastname;
                $display_name   = $current_user->display_name;
                
                $date           = date_create( date('Y-m-d') );
                $date3          = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('2 days'));
                $date4          = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('3 days'));
                $date5          = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('4 days'));
                
                $tickets = array(    
                    array(
                        'subject'       => __( "Welcome to Kanzu Support Desk.","kanzu-support-desk" ),
                        'message'       => sprintf( __( "Hi %s,<br />"
                                        ."Welcome to the Kanzu Support Desk (KSD) community *cue Happy Music and energetic dancers!*. Thanks for choosing us. We are all about making it simple for you to provide amazing customer support."
                                        ."We can't wait for you to get started!<br /><br />"
                                        . "The KSD Team.","kanzu-support-desk" ), $display_name ),
                        'channel'       => "STAFF",
                        'status'        => "NEW",
                        'severity'      => 'HIGH',
                        'email'         => $email,
                        'fullname'      => $fullname,
                        'time_logged'   =>  date_format($date, 'Y-m-d h:i:s')                               
                    ),
                    array(
                        'subject'       => __( "Quick Intro to Kanzu Support Desk Features","kanzu-support-desk" ),
                        'message'       => sprintf( __( 'We arranged a simple walk-through to get you started. From the on-screen instructions, click "Next" to proceed with the '
                        . 'introductory tour. If you would like to get a much deeper appreciation of how everything works, check out our <a href="%s" target="_blank">documentation</a>.<br /><br />'
                        . 'The KSD Team','kanzu-support-desk' ), 'http://www.kanzucode.com/documentation' ),
                        'channel'       => 'STAFF',
                        'status'        => 'OPEN',
                        'severity'      => 'URGENT',
                        'email'         => $email,
                        'fullname'      => $fullname,
                        'time_logged'   => date_format($date, 'Y-m-d h:i:s')
                    ),
                    array(
                        'subject'       => __( "KSD Documentation","kanzu-support-desk" ),
                        'message'       => sprintf( __( 'We made every effort to make KSD simple but powerful.<br />'
                                            . 'Learn how to get even more out of KSD from our rich resources <a href="%s" target="_blank">here</a>.<br /><br />'
                                            . 'The KSD Team.','kanzu-support-desk' ), 'http://www.kanzucode.com/documentation' ),
                        'channel'       => 'STAFF',
                        'status'        => 'OPEN',
                        'severity'      => 'LOW',
                        'email'         => $email,
                        'fullname'      => $fullname,
                        'time_logged'   => date_format($date3, 'Y-m-d h:i:s')
                    ),
                    array(
                        'subject'       => __( "KSD Add-ons and other goodies.","kanzu-support-desk" ),
                        'message'       => __( "Kanzu Support Desk can go even a notch higher;"
                                            . " we have a neat set of add-ons that power-up your experience. Check out the add-ons tab to get a load of them!<br /><br />"
                                            . "The KSD Team","kanzu-support-desk" ),
                        'channel'       => 'STAFF',
                        'status'        => 'OPEN',
                        'severity'      => 'LOW',
                        'email'         => $email,
                        'fullname'      => $fullname,
                        'time_logged'   => date_format($date4, 'Y-m-d h:i:s')
                    ),
                    array(
                        'subject'       => __( "Get in touch. Seriously","kanzu-support-desk" ),
                        'message'       => sprintf( __( '%1$s, this cannot work without you *sob sob*. We sit by our KSD installation hitting refresh constantly (and sipping coffee). Get in touch. <br/>'
                                            . 'What is your experience with Kanzu Support Desk? What do you like? What do you love? What do you want us to fix or improve?<br />'
                                            . 'We would love to hear from you. <a href="%2$s">Click to send us an email</a><br /><br />'
                                            . 'The KSD Team','kanzu-support-desk'), $display_name, 'mailto:feedback@kanzucode.com' ),
                        'channel'       => 'STAFF',
                        'status'        => 'PENDING',
                        'severity'      => 'LOW',
                        'email'         => $email,
                        'fullname'      => $fullname,
                        'time_logged'   => date_format($date5, 'Y-m-d h:i:s')
                    ),
                );
                
                foreach ( $tickets as $tkt ){
                    $new_ticket                         = new stdClass(); 
                    $new_ticket->tkt_subject            = $tkt['subject'];
                    $new_ticket->tkt_message            = $tkt['message'];
                    $new_ticket->tkt_channel            = $tkt['channel'];
                    $new_ticket->tkt_status             = $tkt['status'];
                    $new_ticket->tkt_severity           = $tkt['severity'];
                    $new_ticket->cust_email             = $tkt['email'];
                    $new_ticket->cust_fullname          = $tkt['fullname'];
                    $new_ticket->tkt_time_logged        = $tkt['time_logged'];
                    $new_ticket->tkt_logged_by          = $current_user->ID;
                    $new_ticket->tkt_assigned_to        = $current_user->ID;
                        
                    //Log the ticket
                    do_action( 'ksd_log_new_ticket', $new_ticket );
                    
                }
                
            }

}

endif;

return new KSD_Install();
