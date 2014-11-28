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
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
 
		//Re-direct on plugin activation
		add_action( 'admin_init', array( $this, 'redirect_to_dashboard'    ) );
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
            //Check for re-activation. Will later be used to check for upgrades
            $settings   =   Kanzu_Support_Desk::get_settings();
            if ( $settings['kanzu_support_version'] == KSD_VERSION ) {//Bail out if it's a re-activation
                return;
            }
               self::create_tables();
               self::set_default_options(); 	
               // Redirect to welcome screen
               set_transient( '_ksd_activation_redirect', 1, 60 * 60 );
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
	* Create KSD tables
	*/
        private static function create_tables() {
            global $wpdb;        
		$wpdb->hide_errors();		            
             
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
                $kanzusupport_tables = "
                    		CREATE TABLE `{$wpdb->prefix}kanzusupport_customers` (  
				`cust_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`cust_user_id` BIGINT(20),
                                `cust_email` VARCHAR(100) NOT NULL,
				`cust_firstname` VARCHAR(100) ,
				`cust_lastname` VARCHAR(100),
				`cust_company_name` VARCHAR(128),
				`cust_phone_number` VARCHAR(100),
				`cust_about` TEXT,
				`cust_account_status` ENUM('ENABLED','DISABLED') DEFAULT 'ENABLED',/*Whether account is enabled or disabled*/
				`cust_creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
				`cust_created_by` BIGINT(20), 
				`cust_lastmodification_date` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
				`cust_modified_by` BIGINT(20),
                                UNIQUE ( `cust_email` )
				);
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`tkt_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`tkt_subject` VARCHAR(512) NOT NULL,                                 
				`tkt_message` TEXT NOT NULL,
                                `tkt_message_excerpt` TEXT NOT NULL, 
				`tkt_channel` ENUM('STAFF','FACEBOOK','TWITTER','SUPPORT_TAB','EMAIL','CONTACT_FORM') DEFAULT 'STAFF',
				`tkt_status` ENUM('OPEN','ASSIGNED','PENDING','RESOLVED') DEFAULT 'OPEN',
				`tkt_severity` ENUM ('URGENT', 'HIGH', 'MEDIUM','LOW') DEFAULT 'LOW', 
				`tkt_time_logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 				
                                `tkt_cust_id` BIGINT(20) NOT NULL, 
                                `tkt_assigned_by` BIGINT(20) NOT NULL, 
                                `tkt_assigned_to` BIGINT(20) NULL, 
				`tkt_time_updated` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP, 
				`tkt_updated_by` BIGINT(20) NOT NULL,                                 
				`tkt_private_note` TEXT,
                                KEY (`tkt_assigned_to`,`tkt_assigned_by`,`tkt_cust_id`),
                                CONSTRAINT `tkts_custid_fk`
                                FOREIGN KEY (`tkt_cust_id`) REFERENCES {$wpdb->prefix}kanzusupport_customers(`cust_id`)
                                ON DELETE NO ACTION    
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`rep_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`rep_tkt_id` BIGINT(20) NOT NULL ,
				`rep_type` INT ,/*@TODO To hold forwards*/
				`rep_is_cc` BOOLEAN DEFAULT FALSE,
				`rep_is_bcc` BOOLEAN DEFAULT FALSE,
				`rep_date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`rep_created_by` BIGINT(20) NOT NULL,
				`rep_date_modified` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
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
             * Get default settings
             */
            public static function get_default_options(){
                $user_info = get_userdata(1);//Get the admin user's information. Used to set default email
                return  array (
                        /** KSD Version info ********************************************************/
                        'kanzu_support_version'             => KSD_VERSION,
                    
                        /** Tickets **************************************************************/
                    
                        'enable_new_tkt_notifxns'           => "yes",
                        'ticket_mail_from_name'             => $user_info->display_name,//Defaults to the admin display name 
                        'ticket_mail_from_email'            => $user_info->user_email,//Defaults to the admin email
                        'ticket_mail_subject'               => __("Your support ticket has been received","kanzu-support-desk"),
                        'ticket_mail_message'               => __("Thank you for getting in touch with us. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk"),
                        'recency_definition'                => __("1","kanzu-support-desk"),
                        'show_support_tab'                  => "yes",
                        'tab_message_on_submit'             => __("Thank you. Your support request has been opened. Please allow at least 24 hours for a reply.","kanzu-support-desk")

                    );
            }
 
}

endif;

return new KSD_Install();
