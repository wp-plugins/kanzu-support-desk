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
        protected static $ksd_db_version = 112;

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
                
                //Migration check
                //@TODO: Reassess why it's best put later.
                                
                add_action('admin_notices', array($this, 'data_migration_v2'));

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
                    set_transient( '_ksd_upgrade_redirect', 1, 60 * 60 );// Redirect to welcome screen. Only do tthis for upgrades that have a special intro message
                    return;
                 }
            }
            else{
                //This is a new installation. Yippee! 
                self::set_default_options(); 	
                self::create_support_pages();
                self::log_initial_tickets();
                self::create_roles();//@since 1.5.0                        
                set_transient( '_ksd_activation_redirect', 1, 60 * 60 );// Redirect to welcome screen
            }
            flush_rewrite_rules();//Because of the custom post types    
	}
        
        
        /**
         * Do de-activation stuff. Currently, doesn't do a thing
         */
        public static function deactivate (){
            flush_rewrite_rules();//Because of the custom post types   
        }        
        
                
       /**
	 * Redirect to a welcome page on activation
	 */
	public static function redirect_to_dashboard(){
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_ksd_activation_redirect' ) && ! get_transient( '_ksd_upgrade_redirect' ) ){
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
               
                if ( get_transient( '_ksd_upgrade_redirect' ) ){//Display version-specific welcome message
                    delete_transient( '_ksd_upgrade_redirect' );
                    $sanitized_version =  str_replace('.', '', KSD_VERSION ) ;
                    wp_redirect( admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-dashboard&ksd-intro=v'.$sanitized_version ) );  
                    exit;	
                }
		wp_redirect( admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-dashboard&ksd-intro=1' ) );  
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
         * @TODO Update this to be even more version conscious
         * NOTE: ALWAYS UPDATE THE DB VERSION IF YOU ALTER ANY OF THE TABLES
         */
        public function upgrade_plugin( $previous_version ){
            global $wpdb;  
            $wpdb->hide_errors();
            $dbChanges = array();//Holds all DB change queries
            $sanitized_version =  str_replace('.', '', $previous_version) ;
 
            if ( $sanitized_version < 160 ){//In 1.6.0, we added attachments. Consider changing these ifs to a switch case
                //@since $this->ksd_db_version 110. Added attachments table
                $dbChanges[]= KSD_Install::create_attachments_table();
            }
            if ( $sanitized_version < 162 ){
                //@since $this->ksd_db_version 111 Added tkt_is_read & made tkt_time_updated not null , @1.6.2
               $dbChanges[]= "ALTER TABLE `{$wpdb->prefix}kanzusupport_tickets` ADD `tkt_is_read` BOOLEAN NOT NULL DEFAULT FALSE ;";
               $dbChanges[]= "ALTER TABLE `{$wpdb->prefix}kanzusupport_tickets` CHANGE `tkt_time_updated` `tkt_time_updated` TIMESTAMP NOT NULL;";
            }
 
            if( count( $dbChanges ) > 0 ){  //Make the Db changes. We use $wpdb->query instead of dbDelta because of
                                            //how strict and verbose the dbDelta alternative is. We'd
                                            //need to rewrite CREATE table statements for dbDelta.
                  foreach ( $dbChanges as $query ){
                        $wpdb->query( $query );     
                  }
            }
        }
 
        /**
         * Adds admin notice informing user that there is data to be migrated to new KSD version
         * 
         * Migration stages:
         * ksd_v2migration_deletetables  - deletion of tables left
         * ksd_v2migration_done - migration completed
         * 
         * @since 2.0.0
         */
        public function data_migration_v2(){
            global $wpdb;
            $ksd_v2migration_status  = get_option( 'ksd_v2migration_status' );
            if( 'ksd_v2migration_done' == $ksd_v2migration_status ) return ; 
            
            $result = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}kanzusupport_tickets'" );
            
            //@TODO: Show for migration stage only for now. To be removed in the next version.
            if( count($result) > 0 && 'ksd_v2migration_deletetables' !== $ksd_v2migration_status ){ 
                add_action('ksd_display_settings', function(){ 
                    include_once KSD_PLUGIN_DIR . 'includes/admin/views/html-admin-settings-migration.php';
                });
                
                if( 'ksd_v2migration_deletetables' == $ksd_v2migration_status ){
                ?>
                <div class="notice">
                    <p><?php  printf(__( 'We have noticed that you still have tables from a Kanzu Support Desk version prior to %s! Click <a href="' . admin_url() .'/edit.php?post_type=ksd_ticket&page=ksd-settings&active_tab=migration">here</a> to completely delete them.', 'kanzu-support-desk' ), 'v2.0'); ?></p>
                </div>
                <?php
                }else{ ?>
                <div class="notice">
                    <p><?php printf( __( 'Kanzu Support Desk - We have noticed that you still have data left over from a migration to v2.0! Click <a href="' . admin_url() .'/edit.php?post_type=ksd_ticket&page=ksd-settings&active_tab=migration">here</a> to migrate it into the current version.', 'kanzu-support-desk' ), 'v2.0'); ?></p>
                </div>
                <?php 
                }

            }
        }
        
       /**
	* Create KSD tables
        * @since 1.0.0
	*/
        private static function create_tables() {
            global $wpdb;        
		//$wpdb->hide_errors();		            
                //@since 1.5.3 customers table removed
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
                $kanzusupport_tables = "
				CREATE TABLE `{$wpdb->prefix}kanzusupport_tickets` (
				`tkt_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				`tkt_subject` VARCHAR(512) NOT NULL,                                 
				`tkt_message` TEXT NOT NULL,
                                `tkt_message_excerpt` TEXT NOT NULL, 
				`tkt_channel` ENUM('admin-form','FACEBOOK','TWITTER','SUPPORT_TAB','EMAIL','CONTACT_FORM') DEFAULT 'admin-form',
				`tkt_status` ENUM('NEW','OPEN','ASSIGNED','PENDING','RESOLVED') DEFAULT 'NEW',
				`tkt_severity` ENUM ('URGENT', 'HIGH', 'MEDIUM','LOW') DEFAULT 'LOW', 
				`tkt_time_logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 				
                                `tkt_cust_id` BIGINT(20) UNSIGNED NOT NULL, 
                                `tkt_assigned_by` BIGINT(20) NOT NULL, 
                                `tkt_assigned_to` BIGINT(20) NULL, 
				`tkt_time_updated` TIMESTAMP NOT NULL, 
				`tkt_updated_by` BIGINT(20) NOT NULL,
                                `tkt_cc` VARCHAR(255) DEFAULT NULL,
				`tkt_private_note` TEXT,
                                `tkt_is_read` BOOLEAN NOT NULL DEFAULT FALSE,
                                KEY (`tkt_assigned_to`,`tkt_assigned_by`,`tkt_cust_id`)
				);	
				CREATE TABLE `{$wpdb->prefix}kanzusupport_replies` (
				`rep_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`rep_tkt_id` BIGINT(20) NOT NULL ,
				`rep_type` INT ,/*@TODO To hold forwards*/
				`rep_date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`rep_created_by` BIGINT(20) NOT NULL,
				`rep_date_modified` TIMESTAMP NULL,
				`rep_message` TEXT NOT NULL,
                                `rep_cc` VARCHAR(255) DEFAULT NULL,
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
        //Add the attachments table. The SQL is in a separate function since it is also used in $this->upgrade_plugin()  
        $kanzusupport_tables.=self::create_attachments_table(); 
        
        dbDelta($kanzusupport_tables);                     
        }
        private static function create_attachments_table(){
            global $wpdb;   
            $sql="
            CREATE TABLE `{$wpdb->prefix}kanzusupport_attachments` (
            `attach_id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `attach_tkt_id` BIGINT(20) NULL ,
            `attach_rep_id` BIGINT(20) NULL ,
            `attach_url` VARCHAR(100) NOT NULL,
            `attach_size` VARCHAR(10) NULL,
            `attach_filename` TEXT NOT NULL,
            CONSTRAINT `attach_tktid_fk`
            FOREIGN KEY (`attach_tkt_id`) REFERENCES {$wpdb->prefix}kanzusupport_tickets(`tkt_id`)
            ON DELETE CASCADE,
            CONSTRAINT `attach_repid_fk`
            FOREIGN KEY (`attach_rep_id`) REFERENCES {$wpdb->prefix}kanzusupport_replies(`rep_id`)
            ON DELETE CASCADE
            );";
            return $sql;
        }
 
        
            private static function set_default_options() {                    
                
                 add_option( KSD_OPTIONS_KEY, self::get_default_options() );
                 add_option( 'ksd_activation_time', date( 'U' ) );//Log activation time
                    
            }
            
            /**
             * Create custom user roles
             * @since 1.5.0
             */
            private static function create_roles(){
                add_role(       'ksd_customer', __( 'KSD Customer', 'kanzu-support-desk' ), array(
				'read' 		=> true,
				'edit_posts' 	=> false,
				'delete_posts' 	=> false
			) );
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
                        'enable_notify_on_new_ticket'       => "yes",//@since 1.5.4. Email sent to primary admin on new ticket creation
                        'notify_email'                      => $user_info->user_email,//@since 1.7.0 The default email to send notifications to
                        'ticket_mail_from_name'             => $user_info->display_name,//Defaults to the admin display name 
                        'ticket_mail_from_email'            => $user_info->user_email,//Defaults to the admin email
                        'ticket_mail_subject'               => __( 'Your support ticket has been received', 'kanzu-support-desk' ),
                        'ticket_mail_message'               => __( 'Thank you for getting in touch with us. Your support request has been opened. Please allow at least 24 hours for a reply.', 'kanzu-support-desk' ),
                        'recency_definition'                => "1",
                        'show_support_tab'                  => "yes",
                        'support_button_text'               => "Support",
                        'tab_message_on_submit'             => __( 'Thank you. Your support request has been opened. Please allow at least 24 hours for a reply.', 'kanzu-support-desk' ),
                        'tour_mode'                         => "no", //@since 1.1.0
                        'enable_recaptcha'                  => "no",//@since 1.3.1 Not on by default since user needs to create & provide reCAPTCHA site & secret keys
                        'recaptcha_site_key'                => "",
                        'recaptcha_secret_key'              => "",
                        'recaptcha_error_message'           => sprintf ( __( 'Sorry, an error occurred. If this persists, kindly get in touch with the site administrator on %s', 'kanzu-support-desk' ), $user_info->user_email ),
                        'enable_anonymous_tracking'         => "no", //@since 1.3.2,
                        'auto_assign_user'                  => '',   //@since 1.5.0. Used to auto-assign new tickets when set 
                        'ticket_management_roles'           => 'administrator', //@since 1.5.0. Who can manage your tickets
                        'enable_customer_signup'            => "yes",//@since 2.0.0
                        'page_submit_ticket'                => 0,//@since 2.0.0 ID of the 'Submit ticket' page
                        'page_my_tickets'                   => 0//@since 2.0.0 //ID of the 'My tickets page'
                    );
            }
            

            /**
             * Log initial tickets so that dashboard line graph shows and user
             * gets more details on the product
             */
            public static function log_initial_tickets (){
                
                global $current_user;
                get_currentuserinfo();
                
                $date                   = date_create( date('Y-m-d') );
                $date_two_days_later    = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('2 days'));
                $date_three_days_later  = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('3 days'));
                $date_four_days_later   = date_sub( date_create( date('Y-m-d h:m:i')), date_interval_create_from_date_string('4 days'));
                
                $tickets = array(    
                    array(
                        'ksd_tkt_subject'           => __( 'Welcome to Kanzu Support Desk', 'kanzu-support-desk' ),
                        'ksd_tkt_message'           => sprintf( __( "Hi %s,<br />"
                                        ."Welcome to the Kanzu Support Desk (KSD) community *cue Happy Music and energetic dancers!*. Thanks for choosing us. We are all about making it simple for you to provide amazing customer support."
                                        ."We can't wait for you to get started!<br /><br />"
                                        . "The KSD Team.","kanzu-support-desk" ), $current_user->display_name ),
                        'ksd_tkt_channel'           => "sample-ticket",
                        'ksd_tkt_status'            => "new",
                        'ksd_tkt_severity'          => 'high',     
                        'ksd_tkt_time_logged'       =>  date_format( $date, 'Y-m-d h:i:s' ),
                        'ksd_tkt_assigned_to'       => $current_user->ID,
                        'ksd_cust_email'            => $current_user->user_email
                    ),
                    array(
                        'ksd_tkt_subject'           => __( 'Quick Intro to Kanzu Support Desk Features', 'kanzu-support-desk' ),
                        'ksd_tkt_message'           => sprintf( __( 'We arranged a simple walk-through to get you started. From the on-screen instructions, click "Next" to proceed with the '
                        . 'introductory tour. If you would like to get a much deeper appreciation of how everything works, check out our <a href="%s" target="_blank">documentation</a>.<br /><br />'
                        . 'The KSD Team','kanzu-support-desk' ), 'http://www.kanzucode.com/documentation' ),
                        'ksd_tkt_channel'           => 'sample-ticket',
                        'ksd_tkt_status'            => 'open',
                        'ksd_tkt_severity'          => 'urgent',
                        'ksd_tkt_time_logged'       => date_format( $date, 'Y-m-d h:i:s' ),
                        'ksd_tkt_assigned_to'       => $current_user->ID,
                        'ksd_cust_email'            => $current_user->user_email
                    ),
                    array(
                        'ksd_tkt_subject'           => __( 'KSD Documentation', 'kanzu-support-desk' ),
                        'ksd_tkt_message'           => sprintf( __( 'We made every effort to make KSD simple but powerful.<br />'
                                            . 'Learn how to get even more out of KSD from our rich resources <a href="%s" target="_blank">here</a>.<br /><br />'
                                            . 'The KSD Team.','kanzu-support-desk' ), 'http://www.kanzucode.com/documentation' ),
                        'ksd_tkt_channel'           => 'sample-ticket',
                        'ksd_tkt_status'            => 'open',
                        'ksd_tkt_severity'          => 'low',
                        'ksd_tkt_time_logged'       => date_format( $date_two_days_later, 'Y-m-d h:i:s' ),
                        'ksd_tkt_assigned_to'       => $current_user->ID,
                        'ksd_cust_email'            => $current_user->user_email
                    ),
                    array(
                        'ksd_tkt_subject'           => __( 'KSD Add-ons and other goodies.', 'kanzu-support-desk' ),
                        'ksd_tkt_message'           => __( "Kanzu Support Desk can go even a notch higher;"
                                            . " we have a neat set of add-ons that power-up your experience. Check out the add-ons tab to get a load of them!<br /><br />"
                                            . "The KSD Team","kanzu-support-desk" ),
                        'ksd_tkt_channel'           => 'sample-ticket',
                        'ksd_tkt_status'            => 'open',
                        'ksd_tkt_severity'          => 'low',
                        'ksd_tkt_time_logged'       => date_format( $date_three_days_later, 'Y-m-d h:i:s' ),
                        'ksd_tkt_assigned_to'       => $current_user->ID,
                        'ksd_cust_email'            => $current_user->user_email
                    ),
                    array(
                        'ksd_tkt_subject'           => __( 'Get in touch. Seriously', 'kanzu-support-desk' ),
                        'ksd_tkt_message'           => sprintf( __( '%1$s, this cannot work without you *sob sob*. We sit by our KSD installation hitting refresh constantly (and sipping coffee). Get in touch. <br/>'
                                            . 'What is your experience with Kanzu Support Desk? What do you like? What do you love? What do you want us to fix or improve?<br />'
                                            . 'We would love to hear from you. <a href="%2$s" class="button button-large button-primary">Click to send us an email</a><br /><br />'
                                            . 'The KSD Team','kanzu-support-desk' ), $current_user->display_name, 'mailto:feedback@kanzucode.com' ),
                        'ksd_tkt_channel'           => 'sample-ticket',
                        'ksd_tkt_status'            => 'pending',
                        'ksd_tkt_severity'          => 'low',
                        'ksd_tkt_time_logged'       => date_format( $date_four_days_later, 'Y-m-d h:i:s' ),
                        'ksd_tkt_assigned_to'       => $current_user->ID,
                        'ksd_cust_email'            => $current_user->user_email
                    ),
                );
                
                foreach ( $tickets as $sample_ticket ){
                    do_action( 'ksd_log_new_ticket', $sample_ticket );
                }
                
            }
            
            /**
             * Create the support pages
             * @since 2.0.0
             * @TODO Add this to the upgrade process
             */
            public static function create_support_pages(){
                $submit_ticket = wp_insert_post(
			array(
				'post_title'     => __( 'Submit Ticket', 'kanzu-support-desk' ),
				'post_content'   => '[ksd_support_form]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);
                
                $my_tickets = wp_insert_post(
			array(
				'post_title'     => __( 'My Tickets', 'kanzu-support-desk' ),
				'post_content'   => '[ksd_my_tickets]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);     
             //Update the settings
            $updated_settings = Kanzu_Support_Desk::get_settings();//Get current settings
            $updated_settings['page_submit_ticket'] = $submit_ticket;
            $updated_settings['page_my_tickets']    = $my_tickets;
            update_option( KSD_OPTIONS_KEY, $updated_settings );
            }

}

endif;

return new KSD_Install();