<?php
/**
 * Admin side of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Admin' ) ) :

class KSD_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   


        /**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu. Also define the AJAX callbacks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
                
                //Add the attachments button
                add_action('media_buttons', array( $this, 'add_attachments_button' ), 15 );
                
                //Load add-ons
                add_action( 'ksd_load_addons', array( $this, 'load_ksd_addons' ) );
                
		// Add an action link pointing to the settings page.
		add_filter( 'plugin_action_links_' . plugin_basename( KSD_PLUGIN_FILE ), array( $this, 'add_action_links' ) );		
                 
		//Handle AJAX calls
		add_action( 'wp_ajax_ksd_filter_tickets', array( $this, 'filter_tickets' ));
                add_action( 'wp_ajax_ksd_filter_totals', array( $this, 'filter_totals' ));
                add_action( 'wp_ajax_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
		add_action( 'wp_ajax_ksd_delete_ticket', array( $this, 'delete_ticket' ));
		add_action( 'wp_ajax_ksd_change_status', array( $this, 'change_status' ));
                add_action( 'wp_ajax_ksd_change_severity', array( $this, 'change_severity' ));                
                add_action( 'wp_ajax_ksd_assign_to', array( $this, 'assign_to' ));
                add_action( 'wp_ajax_ksd_reply_ticket', array( $this, 'reply_ticket' ));
                add_action( 'wp_ajax_ksd_get_single_ticket', array( $this, 'get_single_ticket' ));   
                add_action( 'wp_ajax_ksd_get_ticket_replies', array( $this, 'get_ticket_replies_and_notes' ));   
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' )); 
                add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ));  
                add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' )); 
                add_action( 'wp_ajax_ksd_reset_settings', array( $this, 'reset_settings' )); 
                add_action( 'wp_ajax_ksd_update_private_note', array( $this, 'update_private_note' ));  
                add_action( 'wp_ajax_ksd_send_feedback', array( $this, 'send_feedback' ));  
                add_action( 'wp_ajax_ksd_disable_tour_mode', array( $this, 'disable_tour_mode' ));              
                add_action( 'wp_ajax_ksd_get_notifications', array( $this, 'get_notifications' ));  
                add_action( 'wp_ajax_ksd_notify_new_ticket', array( $this, 'notify_new_ticket' ));  
                add_action( 'wp_ajax_ksd_bulk_change_status', array( $this, 'bulk_change_status' )); 
                add_action( 'wp_ajax_ksd_change_read_status', array( $this, 'change_read_status' ));                
                add_action( 'wp_ajax_ksd_modify_license', array( $this, 'modify_license_status' )); 
                add_action( 'wp_ajax_ksd_enable_usage_stats', array( $this, 'enable_usage_stats' )); 
                add_action( 'wp_ajax_ksd_update_ticket_info', array( $this, 'update_ticket_info' )); 
                add_action( 'wp_ajax_ksd_get_ticket_activity', array( $this, 'get_ticket_activity' ));           
                add_action( 'wp_ajax_ksd_migrate_to_v2', array($this, 'migrate_to_v2'));
                add_action( 'wp_ajax_ksd_deletetables_v2', array($this, 'deletetables_v2'));
                
                
                //Generate a debug file
                add_action( 'ksd_generate_debug_file', array( $this, 'generate_debug_file' ) );                  
                
                //Register KSD tickets importer
                add_action( 'admin_init', array( $this, 'ksd_importer_init' ) );
                
                //Do actions called in $_POST
                add_action( 'init', array( $this, 'do_post_and_get_actions' ) );          

                
                //Add ticket categories
                add_action( 'init', array( $this,'create_ticket_category' ), 0 );
                
                //Add contextual help messages
                add_action( 'contextual_help', array ( $this, 'add_contextual_help' ), 10, 3 );
                
                //In 'Edit ticket' view, customize the screen
                add_action( 'add_meta_boxes', array( $this, 'edit_metaboxes' ), 10, 2 );    
                
                //Add items to the submitdiv in 'edit ticket' view
                add_action( 'post_submitbox_misc_actions', array( $this, 'edit_submitdiv') );
                
                //Save ticket and its information
                add_filter( 'wp_insert_post_data' , array( $this, 'save_ticket_info' ) , '99', 2 );
                
                //Alter messages
                add_filter( 'post_updated_messages', array( $this, 'ticket_updated_messages' ) );
                
                //Add KSD Importer to tool box
                add_action( 'tool_box',  array( $this, 'add_importer_to_toolbox' ) );
                
                //Get final status for ticket logged by importation
                add_action( 'ksd_new_ticket_imported', array( $this, 'new_ticket_imported', 10, 2 ) );  
                
                //Modify ticket deletion and un-deletion messages
                add_filter( 'bulk_post_updated_messages', array( $this, 'ticket_bulk_update_messages' ), 10, 2 );
                
                //Add custom views
                add_filter( 'views_edit-ksd_ticket', array( $this, 'ticket_views' ) );
                
                //Add CC button to tinyMCE editor
                $this->add_tinymce_cc_button();
                
                //Add headers to the tickets grid
                add_filter('manage_ksd_ticket_posts_columns', array( $this, 'add_tickets_headers' ) );
                //Populate the new columns
                add_action( 'manage_ksd_ticket_posts_custom_column', array( $this, 'populate_ticket_columns' ), 10, 2 );
                //Add sorting to the new columns
                add_filter( 'manage_edit-ksd_ticket_sortable_columns', array( $this, 'ticket_table_sortable_columns' ));
                //Remove some default columns
                add_filter( 'manage_edit-ksd_ticket_columns', array( $this, 'ticket_table_remove_columns' ));
                
                add_filter( 'request', array( $this, 'ticket_table_columns_orderby' ) );
                //Add ticket filters to the table grid drop-down
                add_action( 'restrict_manage_posts', array( $this, 'ticket_table_filter_headers' ) );
                add_filter( 'parse_query', array( $this, 'ticket_table_apply_filters' ) );
                //Display ticket status next to the ticket title
                add_filter( 'display_post_states', array( $this, 'display_ticket_statuses_next_to_title' ) );
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_styles() {	
            wp_enqueue_style( KSD_SLUG .'-admin-css', KSD_PLUGIN_URL.'/assets/css/ksd-admin.css', array(), KSD_VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_scripts() { 
		//Load the script for Google charts. Load this before the next script. 
                wp_enqueue_script( KSD_SLUG . '-admin-gcharts', '//www.google.com/jsapi', array(), KSD_VERSION ); 
                wp_enqueue_script( KSD_SLUG . '-admin-js', KSD_PLUGIN_URL.'/assets/js/ksd-admin.js', array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION ); 
		
                //Variables to send to the admin JS script
                $ksd_admin_tab = ( isset( $_GET['page'] ) ?  $_GET['page']  : "" );//This determines which tab to show as active
                
               
                
                //Get intro tour messages if we are in tour mode @since 1.1.0
                $tour_pointer_messages['ksd_intro_tour'] =  $this->load_intro_tour();
                
                //This array allows us to internationalize (translate) the words/phrases/labels displayed in the JS 
                $admin_labels_array = array();
                $admin_labels_array['dashboard_chart_title']        = __('Incoming Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_open_tickets']       = __('Total Open Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_unassigned_tickets'] = __('Unassigned Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_avg_response_time']  = __('Avg. Response Time','kanzu-support-desk');
                $admin_labels_array['tkt_trash']                    = __('Trash','kanzu-support-desk');
                $admin_labels_array['tkt_assign_to']                = __('Assign To','kanzu-support-desk');
                $admin_labels_array['tkt_change_status']            = __('Change Status','kanzu-support-desk');
                $admin_labels_array['tkt_change_severity']          = __('Change Severity','kanzu-support-desk');
                $admin_labels_array['tkt_mark_read']                = __('Mark as Read','kanzu-support-desk');
                $admin_labels_array['tkt_mark_unread']              = __('Mark as Unread','kanzu-support-desk');
                $admin_labels_array['tkt_subject']                  = __('Subject','kanzu-support-desk');
                $admin_labels_array['tkt_cust_fullname']            = __('Customer Name','kanzu-support-desk');
                $admin_labels_array['tkt_cust_email']               = __('Customer Email','kanzu-support-desk');
                $admin_labels_array['tkt_reply']                    = __('Send','kanzu-support-desk');
                $admin_labels_array['tkt_forward']                  = __('Forward','kanzu-support-desk');
                $admin_labels_array['tkt_update_note']              = __('Add Note','kanzu-support-desk');
                $admin_labels_array['tkt_attach_file']              = __('Attach File','kanzu-support-desk');
                $admin_labels_array['tkt_attach']                   = __('Attach','kanzu-support-desk');                
                $admin_labels_array['tkt_status_open']              = __('OPEN','kanzu-support-desk');
                $admin_labels_array['tkt_status_pending']           = __('PENDING','kanzu-support-desk');
                $admin_labels_array['tkt_status_resolved']          = __('RESOLVED','kanzu-support-desk');                
                $admin_labels_array['tkt_severity_low']             = __('LOW','kanzu-support-desk');
                $admin_labels_array['tkt_severity_medium']          = __('MEDIUM','kanzu-support-desk');
                $admin_labels_array['tkt_severity_high']            = __('HIGH','kanzu-support-desk');
                $admin_labels_array['tkt_severity_urgent']          = __('URGENT','kanzu-support-desk');              
                $admin_labels_array['msg_still_loading']            = __('Loading Replies...','kanzu-support-desk');
                $admin_labels_array['msg_loading']                  = __('Loading...','kanzu-support-desk');
                $admin_labels_array['msg_sending']                  = __('Sending...','kanzu-support-desk');
                $admin_labels_array['msg_reply_sent']               = __('Reply Sent!','kanzu-support-desk');
                $admin_labels_array['msg_error']                    = __('Sorry, an unexpected error occurred. Kindly retry. Thank you.','kanzu-support-desk');
                $admin_labels_array['msg_error_refresh']            = __('Sorry, but something went wrong. Please try again or reload the page.','kanzu-support-desk');
                $admin_labels_array['pointer_next']                 = __('Next','kanzu-support-desk');
                $admin_labels_array['lbl_toggle_trimmed_content']   = __('Toggle Trimmed Content','kanzu-support-desk');
                $admin_labels_array['lbl_tickets']                  = __( 'Tickets','kanzu-support-desk' );
                $admin_labels_array['lbl_CC']                       = __( 'CC','kanzu-support-desk' );
                $admin_labels_array['lbl_replytoall']               = __( 'Reply to all','kanzu-support-desk' );
                $admin_labels_array['lbl_save']                     = __( 'Save','kanzu-support-desk' );
                $admin_labels_array['lbl_update']                   = __( 'Update','kanzu-support-desk' );
                $admin_labels_array['lbl_created_on']               = __( 'Created on','kanzu-support-desk' );
                
                //jQuery form validator internationalization
                $admin_labels_array['validator_required']           = __( 'This field is required.','kanzu-support-desk' );
                $admin_labels_array['validator_email']              = __( 'Please enter a valid email address.','kanzu-support-desk' );
                $admin_labels_array['validator_minlength']          = __( 'Please enter at least {0} characters.','kanzu-support-desk' );
                $admin_labels_array['validator_cc']                 = __( 'Please enter a comma separated list of valid email addresses.', 'kanzu-support-desk' );
                
                //Messages for migration to v2.0.0
                $admin_labels_array['msg_migrationv2_started']      = __('Migration of your tickets and replies has started. This may take some time. Please wait...','kanzu-support-desk');
                $admin_labels_array['msg_migrationv2_deleting']     = __('Deleting tickets. This may take sometime. Please wait...','kanzu-support-desk');
                
                $ticket_info = array( 'status_list' => $this->get_submitdiv_status_options() );
                
                //Get current settings
                $settings = Kanzu_Support_Desk::get_settings();

                //Localization allows us to send variables to the JS script
                wp_localize_script( KSD_SLUG . '-admin-js',
                                    'ksd_admin',
                                    array(  'admin_tab'                 =>  $ksd_admin_tab,
                                            'ajax_url'                  =>  admin_url( 'admin-ajax.php'),
                                            'ksd_admin_nonce'           =>  wp_create_nonce( 'ksd-admin-nonce' ),
                                            'ksd_tickets_url'           =>  admin_url( 'admin.php?page=ksd-tickets'),
                                            'ksd_agents_list'           =>  self::get_agent_list( $settings['ticket_management_roles'] ),
                                            'ksd_current_user_id'       =>  get_current_user_id(),
                                            'ksd_labels'                =>  $admin_labels_array,
                                            'ksd_tour_pointers'         =>  $tour_pointer_messages,
                                            'enable_anonymous_tracking' =>  $settings['enable_anonymous_tracking'],
                                            'ksd_ticket_info'           =>  $ticket_info,
                                            'ksd_current_screen'        =>  $this->get_current_ksd_screen(),
                                            'ksd_version'               =>  KSD_VERSION
                                        )
                                    );

	}
        
        /**
         * Do all KSD actions present in the $_POST & $_GET superglobals.
         * These functions are called on init
         * @since 1.7.0
         */
        public function do_post_and_get_actions(){
            if ( isset( $_POST['ksd_action'] ) ) {
                do_action( $_POST['ksd_action'], $_POST );
            }
            if ( isset($_GET['ksd_action'] ) ) {
                do_action( $_GET['ksd_action'], $_GET);
            }
        }

        

        
        /**
         * Update ticket messages  displayed
         * @since 2.0.0
         * @global type $post
         * @global type $post_ID
         * @param type $messages
         * @return type
         */
        public function ticket_updated_messages( $messages ) {
            global $post, $post_ID;
            $messages['ksd_ticket'] = array(
                0   => '',
                1   => sprintf(__('Ticket updated. <a href="%s">View ticket</a>'), esc_url(get_permalink($post_ID))),
                2   => __('Custom field updated.'),
                3   => __('Custom field deleted.'),
                4   => __('Ticket updated.'),
                5   => isset($_GET['revision']) ? sprintf(__('Ticket restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6   => sprintf(__('Ticket published. <a href="%s">View ticket</a>'), esc_url(get_permalink($post_ID))),
                7   => __('Ticket saved.'),
                8   => sprintf(__('Ticket submitted. <a target="_blank" href="%s">Preview ticket</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9   => sprintf(__('Ticket scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ticket</a>'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10  => sprintf(__('Ticket draft updated. <a target="_blank" href="%s">Preview ticket</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            );
            return $messages;
        }
        
        /**
         * Modify ticket bulk update messages
         * @since 2.0.0
         */
        public function ticket_bulk_update_messages( $bulk_messages, $bulk_counts ){
            $bulk_messages['ksd_ticket'] = array(
                    'updated'   => _n( '%s ticket updated.', '%s tickets updated.', $bulk_counts['updated'] ),
                    'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 ticket not updated, somebody is editing it.' ) :
                                       _n( '%s ticket not updated, somebody is editing it.', '%s tickets not updated, somebody is editing them.', $bulk_counts['locked'] ),
                    'deleted'   => _n( '%s ticket permanently deleted.', '%s tickets permanently deleted.', $bulk_counts['deleted'] ),
                    'trashed'   => _n( '%s ticket moved to the Trash.', '%s tickets moved to the Trash.', $bulk_counts['trashed'] ),
                    'untrashed' => _n( '%s ticket restored from the Trash.', '%s tickets restored from the Trash.', $bulk_counts['untrashed'] ),
            );    
            return $bulk_messages;
        }
        
        /**
         * Create categories/labels used for tickets
         * @return string
         */
        public function create_ticket_category() {
            $args = array(
                'hierarchical'  => true,
            );
            register_taxonomy( 'ticket_category', 'ksd_ticket', $args );
        }
        
                
        /**
         * Get the current KSD screen
         * @since 2.0.0
         */
        private function get_current_ksd_screen( $screen = null ){
            $current_ksd_screen_id = 'not_a_ksd_screen';
            if ( null == $screen ){
                $screen = get_current_screen();
            }
            if ( 'ksd_ticket' !== $screen->post_type ) {
                 return $current_ksd_screen_id;
            } 
            switch( $screen->id ){
                 case 'edit-ksd_ticket'://Ticket Grid
                     $current_ksd_screen_id = 'ksd-ticket-list';
                    break;
                case 'ksd_ticket'://Single ticket view and Add new ticket
                    if( 'add' == $screen->action ){//Add new ticket
                        $current_ksd_screen_id = 'ksd-add-new-ticket';
                    }else{//Single ticket view
                        $current_ksd_screen_id = 'ksd-single-ticket-details';
                    }
                    break;
                case 'edit-ticket_category'://Categories
                    $current_ksd_screen_id = 'ksd-edit-categories';
                    break;
                case 'edit-product':
                    $current_ksd_screen_id = 'ksd-edit-products';
                    break;
                case 'ksd_ticket_page_ksd-dashboard':
                    $current_ksd_screen_id = 'ksd-dashboard';
                    break;
                 case 'ksd_ticket_page_ksd-settings'://Settings
                     $current_ksd_screen_id = 'ksd-settings';
                     break;
                 case 'ksd_ticket_page_ksd-addons'://Add-ons
                     $current_ksd_screen_id = 'ksd-addons';
                     break;
            }
            return $current_ksd_screen_id;
        }
        
        /**
         * Add contextual help messages
         * @param string $contextual_help
         * @param int $screen_id
         * @param Object $screen
         * @return string $contextual_help The contextual help
         * @since 2.0.0
         */
        public function add_contextual_help( $contextual_help, $screen_id, $screen ) { 
            $current_ksd_screen = $this->get_current_ksd_screen( $screen );
            if ( 'not_a_ksd_screen' == $current_ksd_screen ) {
                 return $contextual_help;
            }            
            switch( $current_ksd_screen ){
                case 'ksd-ticket-list': 
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p> <p> <b> %s </b> %s </p><p> <b> %s </b> %s </p></span>',
                                                __( 'Tickets', 'kanzu-support-desk'),
                                                __( 'All your tickets are displayed here. View the details of a single ticket by clicking on it.', 'kanzu-support-desk'),
                                                __( 'Filtering', 'kanzu-support-desk'),
                                                __( 'Filter tickets using ticket status or severity.', 'kanzu-support-desk'),
                                                __( 'Sorting', 'kanzu-support-desk'),
                                                __( 'Re-order tickets by clicking on the header of the column you would like to order by', 'kanzu-support-desk') 
                                        );                            
                    break;
                case 'ksd-add-new-ticket': 
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p><p> <b> %s :</b> %s </p></span>',
                                                __( 'New Ticket', 'kanzu-support-desk'),
                                                __( 'Add details for a new ticket. Use the publish button to make the ticket publically visible', 'kanzu-support-desk'),
                                                __( 'Save', 'kanzu-support-desk'),
                                                __( 'When you save a ticket and do not publish it, it will NOT be visible to the customer. Use this for tickets that you are still making changes to', 'kanzu-support-desk')
                                        );  
                case 'ksd-single-ticket-details':
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p><p> <b> %s :</b> %s </p></span>',
                                                __( 'Reply ticket/Edit ticket information', 'kanzu-support-desk'),
                                                __( 'Modify ticket information', 'kanzu-support-desk'),
                                                __( 'Modify the details of a ticket in the "Ticket Information" box. Change status, severity, assignee and other ticket information. Use the Update button to save your changes', 'kanzu-support-desk'),
                                                __( 'Reply your customer', 'kanzu-support-desk'),
                                                __( 'Type a reply and use the Send button to send your reply to your customer', 'kanzu-support-desk'),
                                                __( 'Private Notes', 'kanzu-support-desk'),
                                                __( 'Save a private note that will be viewed by other agents. Customers are NOT able to view private notes', 'kanzu-support-desk')
                                        ); 
                
                    break;
                case 'ksd-edit-categories':
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                                __( 'Ticket Categories', 'kanzu-support-desk'),
                                                __( 'Add/Edit/Delete ticket categories. Use categories to organize your tickets', 'kanzu-support-desk')    
                                        );  
                    break;
                case 'ksd-edit-products': 
                   $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                                __( 'Ticket Products', 'kanzu-support-desk'),
                                                __( 'Add/Edit/Delete ticket products. Use products to identify which of your products the ticket is attached to', 'kanzu-support-desk')    
                                        );  
                    break;
                case 'ksd-dashboard': 
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                                __( 'Ticket Dashboard', 'kanzu-support-desk'),
                                                __( 'Shows an overview of your performance', 'kanzu-support-desk')    
                                        ); 
                    break;
                case 'ksd-settings': 
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                                __( 'KSD Settings', 'kanzu-support-desk'),
                                                __( 'Customize your KSD experience by modifying your settings. Each setting has a help message next to it.', 'kanzu-support-desk')    
                                        );                     
                    break;
                case 'ksd-addons': 
                    $contextual_help = sprintf( '<span><h2> %s </h2> <p> %s </p></span>',
                                                __( 'KSD Add-ons', 'kanzu-support-desk'),
                                                __( 'Take your customer support to the next level by activatinng an add-on.', 'kanzu-support-desk')    
                                        );                      
                    break;
            }
            return $contextual_help;
        }
        
        /**
         * Modify the metaboxes on the ticket edit screen
         * @since 2.0.0
         */
        public function edit_metaboxes( $post_type, $post ){
            if( $post_type !== 'ksd_ticket' ){
                return;
            }
            
            //Remove unwanted metaboxes
            $metaboxes_to_remove = array ( 'submitdiv' , 'authordiv', 'postcustom', 'postexcerpt', 'trackbacksdiv', 'tagsdiv-post_tag'  );
            foreach ( $metaboxes_to_remove as  $remove_metabox ){
                remove_meta_box(  $remove_metabox, 'ksd_ticket', 'side' ); 
            }
            //Add a custom submitdiv
            $publish_callback_args = array( 'revisions_count' => 0, 'revision_id' => NULL   );   
            add_meta_box( 'submitdiv', __( 'Ticket Information' ), 'post_submit_meta_box', null, 'side', 'high', $publish_callback_args );
            
            if( $post->post_status !== 'auto-draft' ){//For ticket updates            
            //Add main metabox for ticket replies
            add_meta_box(
                    'ksd-ticket-messages', 
                    __('Ticket Messages', 'kanzu-support-desk'),  
                    array( $this,'output_meta_boxes' ),   
                    'ksd_ticket',  
                    'normal',  
                    'high'          
                );   
            //For ticket activity
            add_meta_box(
                    'ksd-ticket-activity', 
                    __('Ticket Activity', 'kanzu-support-desk'),  
                    array( $this,'output_meta_boxes' ),   
                    'ksd_ticket',  
                    'side',  
                    'high'          
                ); 
            }            
        }
        
        /**
         * Edit the submitddiv box displayed in the sidebar of tickets
         * @since 2.0.0
         * @global type $post
         */
        public function edit_submitdiv(){
            global $post;
            if ( $post->post_type !== 'ksd_ticket' ) {
                return;
            }
            include_once( KSD_PLUGIN_DIR .  "includes/admin/views/metaboxes/html-ksd-ticket-info.php");
        }
        
        
        /**
         * Output ticket meta boxes
         * @param Object The WP_Object
         * @param Array $metabox The metabox array
         * @since 2.0.0
         */
        public function output_meta_boxes( $the_ticket, $metabox  ){
            //If this is the ticket messages metabox, format the content for viewing
            if( $metabox['id'] == 'ksd-ticket-messages' ) {
                $the_ticket->content = $this->format_message_content_for_viewing( $the_ticket->content );
            }
            include_once( KSD_PLUGIN_DIR .  "includes/admin/views/metaboxes/html-".$metabox['id'].".php");
        }     
        

        
        /**
         * Save ticket information
         * This ensures that at all times, tickets only have one of 
         * our predefined statuses (new, open, pending, draft or resolved)
         * Also, it saves our metavalues
         * Note that this implements a filter; every return statement MUST return $data
         * @since 2.0.0
         */
        public function save_ticket_info( $data , $postarr ){
            if ( 'ksd_ticket' !== $data[ 'post_type' ] ) {//Only handle our tickets
                return $data;
            }
            if ( 'auto-draft' == $data[ 'post_status' ] ){//Stop processing if it is a new ticket                
                return $data;
            }
            //Noticed another parameter to pick up auto-drafts
            if ( isset ( $postarr['auto_draft'] ) ){
                if ( $postarr['auto_draft'] ){
                    return $data;
                }
            }
            if ( wp_is_post_revision( $postarr['ID'] ) ){
		return $data;                
            }
            if ( wp_is_post_autosave( $postarr['ID'] ) ){ 
                return $data;
            }      
            //Set post_author to customer
            if( isset( $postarr['_ksd_tkt_info_customer'] ) ) {
                    $data['post_author'] = $postarr['_ksd_tkt_info_customer']; 
            }
            //Save the ticket's meta information
            $this->save_ticket_meta_info( $postarr['ID'], $postarr['post_title'], $postarr );
            
            if ( 'publish' == $data['post_status'] ){//Change published tickets' statuses from 'publish' to KSD native ticket statuses
                $post_status = ( 'auto-draft' == $postarr['hidden_ksd_post_status'] ? 'open' : $postarr['hidden_ksd_post_status'] );
                $data['post_status'] = $post_status;
            }   
            return $data;
        }
        
        /**
         * Save a ticket's meta information. This includes severity, assignee, etc
         * @param int $tkt_id The ticket ID
         * @param string $tkt_title The ticket title
         * @since 2.0.0
         * 
         */
        private function save_ticket_meta_info( $tkt_id, $tkt_title, $meta_array ){
            $ksd_meta_keys = array (
                '_ksd_tkt_info_severity'    => 'low',
                '_ksd_tkt_info_assigned_to' => 0,
                '_ksd_tkt_info_channel'     => 'admin-form',
                '_ksd_tkt_info_cc'          => ''
            );
            //Save ticket customer meta information in the activity list. This is all we do with the _ksd_tkt_info_customer field
            if( isset( $meta_array[ '_ksd_tkt_info_customer' ] ) ){
                $this->update_ticket_activity( '_ksd_tkt_info_customer', $tkt_title, $tkt_id, wp_get_current_user()->ID, $meta_array['_ksd_tkt_info_customer'] );
            }
 
            //Update the other meta information  
            foreach ( $ksd_meta_keys as $tkt_info_meta_key => $tkt_info_default_value ){
                if( !isset( $meta_array[$tkt_info_meta_key] ) ){
                    continue;//Only do this if the value exists
                }

                $tkt_info_old_value = get_post_meta( $tkt_id, $tkt_info_meta_key, true );  
                
                if ( '' ==  $tkt_info_old_value  ){//This is a new ticket. 
                    $tkt_info_meta_value = ( $tkt_info_default_value == $meta_array[$tkt_info_meta_key] ? $tkt_info_default_value : $meta_array[$tkt_info_meta_key] );
                    add_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_meta_value, true ); 
                    continue;
                }
                if(  $tkt_info_old_value == $meta_array[$tkt_info_meta_key] ){
                    continue;                                
                }
                
                $this->update_ticket_activity( $tkt_info_meta_key, $tkt_title, $tkt_id, $tkt_info_old_value, $meta_array[$tkt_info_meta_key] );

                update_post_meta( $tkt_id, $tkt_info_meta_key, $meta_array[ $tkt_info_meta_key ] ); 
            }
            
        }
        


        /**
         * Get a list of agents
         * @param string $roles The WP Roles with access to KSD
         * @param boolean $as_options Return the agent list as select options. 
         * @return An unordered list of agents or select options depending on $as_options
         */
        public static function get_agent_list( $roles="", $as_options = false ){
            if( empty( $roles ) ){
                $settings = Kanzu_Support_Desk::get_settings();
                $roles = $settings['ticket_management_roles'];
            }
            include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-users-controller.php");//@since 1.5.0 filter the list to return users in certain roles
            $UC = new KSD_Users_Controller();
            $tmp_user_IDs = $UC->get_users_with_roles( $roles );
            foreach ( $tmp_user_IDs as $userID ){
                $user_IDs[] = $userID->user_id;
            }
            $agents_list = ( !$as_options ? "<ul class='ksd_agent_list hidden'>" : "" );//The available list of agents
                foreach (  get_users( array( 'include' => $user_IDs ) ) as $agent ) {
                    $agents_list .= ( !$as_options ? "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>" : "<option value=".$agent->ID.">".esc_html( $agent->display_name )."</option>" );
                }
             if( !$as_options ){
                 $agents_list .= "</ul>";
             }
             return $agents_list;
        }
        
        /**
         * Get the KSD severity list
         * @since 2.0.0
         */
        public function get_severity_list(){
            return array (
                'low'       => __( 'Low','kanzu-support-desk' ), 
                'medium'    => __( 'Medium','kanzu-support-desk' ), 
                'high'      => __( 'High','kanzu-support-desk' ), 
                'urgent'    => __( 'Urgent','kanzu-support-desk' )   
            );          
        }
        
        /**
         * Get the KSD status list
         * @since 2.0.0
         */
        public function get_status_list(){
            return array (
                'open'      => __( 'Open','kanzu-support-desk' ), 
                'pending'   => __( 'Pending','kanzu-support-desk' ), 
                'resolved'  => __( 'Resolved','kanzu-support-desk' )
                );
        }
        
        /***
         * Create options for the status select item in the
         * submitdiv on the edit/reply ticket view
         * @since 2.0.0
         */
        private function get_submitdiv_status_options(){
            $status_options = '';
            foreach ( $this->get_status_list() as $status => $status_label ){
                $status_options.="<option value='{$status}'>{$status_label}</option>";
            }
            //Add a 'draft' status
            $status_options.="<option value='draft'>"._x( 'Draft', 'status of a ticket','kanzu-support-desk' )."</option>";
            return $status_options;            
        }
	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings' ) . '">' . __( 'Settings', 'kanzu-support-desk' ) . '</a>'
			),
			$links
		);

	}        
                        
	
	/**
	 * Add menu items in the admin panel 
	 */
	public function add_menu_pages() {
		//Add the top-level admin menu
		$page_title = __('Kanzu Support Desk','kanzu-support-desk');
		$capability = 'manage_options';
		$menu_slug  = 'edit.php?post_type=ksd_ticket';
		$function   = 'output_admin_menu_dashboard';
		
		//Add the ticket sub-pages. 
		$ticket_types = array();
		$ticket_types[ 'ksd-dashboard' ]  =   __( 'Dashboard','kanzu-support-desk' );
		$ticket_types[ 'ksd-settings' ]   =   __( 'Settings','kanzu-support-desk' );
		$ticket_types[ 'ksd-addons' ]     =   '<span style="color:orange;">' .__( 'Add-ons','kanzu-support-desk' ). '</span>';           
		
		foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
                    add_submenu_page( $menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array( $this,$function ) );                        		
                } 
                //Remove ticket tags
                remove_submenu_page( 'edit.php?post_type=ksd_ticket','edit-tags.php?taxonomy=post_tag&amp;post_type=ksd_ticket' );  
	}
        
        /**
         * Add the button used to add attachments to a ticket
         * @param string $editor_id The editor ID
         */
        public function add_attachments_button( $editor_id ){            
            if( !isset( $_GET['page'] ) ){
                return;
            }
            if ( strpos ( $editor_id , 'ksd_' ) !== false ){//Check that we are modifying a KSD wp_editor. Don't modify wp_editor for posts, pages, etc 
                echo "<a href='#' id='ksd-add-attachment-{$editor_id}' class='button {$editor_id}'>".__( 'Add Attachment','kanzu-support-desk' )."</a>";
            }
        }               
	
	/**
	 * Display the main Kanzu Support Desk admin dashboard
	 */
	public function output_admin_menu_dashboard(){
		$this->do_admin_includes();             
                if( isset( $_GET['ksd-intro'] ) ){
                    include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-intro.php');         
                }
                else{
                    include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-wrapper.php');   
                }
	}
        
 
	/**
	 * Include the files we use in the admin dashboard
	 */
        public function do_admin_includes() {		
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-tickets-controller.php");
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-users-controller.php");
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-assignments-controller.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-attachments-controller.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-replies-controller.php");  
	}
        
        
        /**
         * Returns total tickets in each ticket filter category ie All, Resolved, etc...
         */
        public function filter_totals(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                  die ( __('Busted!','kanzu-support-desk') );                         
            }          
            try{
                $this->do_admin_includes();                
                $settings = Kanzu_Support_Desk::get_settings();
                $recency = $settings['recency_definition'];                
                $tickets = new KSD_Tickets_Controller(); 
                $response  = $tickets->get_filter_totals( get_current_user_id() ,$recency );   
            }catch( Exception $e ){
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
            } 
                echo json_encode( $response );
                die();// IMPORTANT: don't leave this out
        }
        
	/** 
	 * Filter tickets in the 'tickets' view 
	 */
	public function filter_tickets() {		 
	  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );                         
          }
          
                try{
                    $this->do_admin_includes();
                    $value_parameters   =   array();
                    switch( $_POST[ 'ksd_view' ] ):
                            case '#tickets-tab-2': //'All Tickets'
                                    $filter=" tkt_status != 'RESOLVED'";
                            break;
                            case '#tickets-tab-3'://'Unassigned Tickets'
                                    $filter = " tkt_assigned_to IS NULL ";			
                            break;
                            case '#tickets-tab-4'://'Recently Updated' i.e. Updated in the last hour. 
                                    $settings = Kanzu_Support_Desk::get_settings();
                                    $value_parameters[] = $settings['recency_definition'];
                                    $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR)"; 
                            break;
                            case '#tickets-tab-5'://'Recently Resolved'.i.e Resolved in the last hour. 
                                    $settings = Kanzu_Support_Desk::get_settings();
                                    $value_parameters[] = $settings['recency_definition'];
                                    $filter=" tkt_time_updated < DATE_SUB(NOW(), INTERVAL %d HOUR) AND tkt_status = 'RESOLVED'"; 
                            break;
                            case '#tickets-tab-6'://'Resolved'
                                    $filter=" tkt_status = 'RESOLVED'";
                            break;
                            default://'My Unresolved'
                                    $filter = " tkt_assigned_to = ".get_current_user_id()." AND tkt_status != 'RESOLVED'";				
                    endswitch;


                    $offset =   sanitize_text_field( $_POST['offset'] );
                    $limit  =   sanitize_text_field( $_POST['limit'] );
                    $search =   sanitize_text_field( $_POST['search'] );

                    //search
                    if( $search != "" ){
                        $filter .= " AND UPPER(tkt_subject) LIKE UPPER(%s) ";
                        $value_parameters[] = '%'.$search.'%';
                    }

                    //order
                    $filter .= " ORDER BY tkt_time_updated DESC ";//@since 1.6.2 sort by tkt_time_updated

                    //limit
                    $count_filter = $filter; //Query without limit to get the total number of rows
                    $count_value_parameters =   $value_parameters;
                    $filter .= " LIMIT %d , %d " ;
                    $value_parameters[] = $offset;//The order of items in $value_parameters is very important. 
                    $value_parameters[] = $limit;//The order of placeholders should correspond to the order of entries in the array
                
                    //Results count
                    $tickets = new KSD_Tickets_Controller(); 
                    $count   = $tickets->get_pre_limit_count( $count_filter,$count_value_parameters );
                    $raw_tickets = $this->filter_ticket_view( $filter,$value_parameters );
                    
                    if( empty( $raw_tickets ) ){
                        $response = __( 'Nothing to see here. Great work!', 'kanzu-support-desk');
                    }    else{
                        
                        $response = array(
                            0 => $raw_tickets,
                            1 => $count
                        );
                         
                    }

                    echo json_encode($response);
                    die();// IMPORTANT: don't leave this out
                    
                   
                }catch( Exception $e){
                    $response = array(
                        'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                    );
                    echo json_encode($response);	
                    die();// IMPORTANT: don't leave this out
                }    
	}
	/**
	 * Filters tickets based on the view chosen
         * @param string $filter The filter [Everything after the WHERE clause] using placeholders %s and %d
         * @param Array $value_parameters The values to replace the $filter placeholders
	 */
	public function filter_ticket_view( $filter = "", $value_parameters=array() ) {
		$tickets = new KSD_Tickets_Controller();                 
                //$tickets_raw = $tickets->get_tickets( $filter,$value_parameters ); 	
                $tickets_raw = $tickets->get_tickets_n_reply_cnt( $filter,$value_parameters ); 	
                //Process the tickets for viewing on the view. Replace the username and the time with cleaner versions
                foreach ( $tickets_raw as $ksd_ticket ) {
                    $this->format_ticket_for_viewing( $ksd_ticket );
                }                
                return $tickets_raw;
	}
        
	/**
         * Retrieve a single ticket and all its replies
         */
        public function get_single_ticket() {

            if (!wp_verify_nonce($_POST['ksd_admin_nonce'], 'ksd-admin-nonce')) {
                die(__('Busted!', 'kanzu-support-desk')); //@TODO Change this to check referrer
            }
            $this->do_admin_includes();
            try {
                $response = get_post( $_POST['tkt_id'] );
                //@TODO Mark the ticket as read. Use custom field
                $this->do_change_read_status( $_POST['tkt_id'] );
            } catch ( Exception $e) {
                $response = array(
                    'error' => array('message' => $e->getMessage(), 'code' => $e->getCode())
                );
            }
            echo json_encode( $response );
            die(); // IMPORTANT: don't leave this out
        }

        /**
         * Retrieve a ticket's replies
         * Called by AJAX
         * @since 2.0.0
         */
        public function get_ticket_replies_and_notes() {
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce') ) {
                die( __('Busted!', 'kanzu-support-desk') );
            }
            $this->do_admin_includes();
            try {                
                $replies = $this->do_get_ticket_replies_and_notes( $_POST['tkt_id'] );
            } catch ( Exception $e ) {
                $replies = array(
                    'error' => array( 'message' => $e->getMessage(), 'code' => $e->getCode() )
                );
            }
            echo json_encode( $replies );
            die(); // IMPORTANT: don't leave this out
        }
        
        /**
         * Get a customer's tickets
         * @param int $customer_id
         * @since 2.0.0
         */
        public function get_customer_tickets( $customer_id ){
            $this->do_admin_includes();          
            $my_ticket_args =  'post_type=ksd_ticket&author='.$customer_id;//',                            
            return new WP_Query( $my_ticket_args ); 
        }
        
        /**
         * Get ticket's replies and private notes
         * @param int $tkt_id The ticket ID
         * @param boolean $get_notes Whether to get private notes or not
         * @since 2.0.0
         */
        public function do_get_ticket_replies_and_notes( $tkt_id, $get_notes = true ){
            if ( $get_notes ){
                $args = array( 'post_type' => array ( 'ksd_reply','ksd_private_note' ), 'post_parent' => $tkt_id, 'post_status' => array ( 'private', 'publish' ) );
            }
            else{
                $args = array( 'post_type' => 'ksd_reply', 'post_parent' => $tkt_id );
            }
            $replies = get_posts( $args );//@TODO Test this. Might need to change it to new WP_Query

            //Replace the reply author ID with the display name and get the reply's attachments
            foreach ( $replies as $reply ) {
                $reply->post_author = get_userdata( $reply->post_author )->display_name;
                //@TODO Get the reply's attachments
                
                //Change the time to somoething more human-readable
                $reply->post_date = date_i18n( __( 'M j, Y @ H:i' ), strtotime( $reply->post_date ) ); 
                
                //Format the message for viewing
                $reply->post_content = $this->format_message_content_for_viewing( $reply->post_content );
            }    
            return $replies;
        }
        
        /**
         * Get a single ticket's activity
         * @since 2.0.0
         */
        public function get_ticket_activity(){
           if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce') ) {
                die( __('Busted!', 'kanzu-support-desk') );
            }
            $this->do_admin_includes();
            try {
                $args = array( 'post_type' => 'ksd_ticket_activity', 'post_parent' => sanitize_key( $_POST['tkt_id'] ), 'post_status' => 'private' );
                $ticket_activities = get_posts( $args );
                
                if( count( $ticket_activities ) > 0 && !empty ( $_POST['tkt_id'] ) ) {
                    //Replace the post_author IDs with names
                    foreach ( $ticket_activities as $activity ) {
                        $activity->post_author = get_userdata( $activity->post_author )->display_name;
                        $activity->post_date = date_i18n( __( 'M j, Y @ H:i' ), strtotime( $activity->post_date ) );
                    }
                }
                else{
                    $ticket_activities = __( 'No activity yet.','kanzu-support-desk' );
                }
            } catch ( Exception $e ) {
                $ticket_activities = array(
                    'error' => array( 'message' => $e->getMessage(), 'code' => $e->getCode() )
                );
            }
            echo json_encode( $ticket_activities );
            die(); // IMPORTANT: don't leave this out            
        }

        /**
	 * Delete a ticket
	 */
	public function delete_ticket(){
            try{
                if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                             die ( __('Busted!','kanzu-support-desk') );
                }
                    $this->do_admin_includes();
                $tickets = new KSD_Tickets_Controller();

                if (!is_array($_POST['tkt_id'])) {
                    if ( $tickets->delete_ticket($_POST['tkt_id']) ) {
                        echo json_encode(__('Deleted', 'kanzu-support-desk'));
                    } else {
                        throw new Exception(__('Failed', 'kanzu-support-desk'), -1);
                    }
                } else {
                if ( is_array( $tickets->bulk_delete_tickets( $_POST['tkt_id'] ) )) {
                        echo json_encode(__('Tickets Deleted', 'kanzu-support-desk'));
                    } else {
                        throw new Exception(__('Ticket Deletion Failed', 'kanzu-support-desk'), -1);
                    }
                }
                die();// IMPORTANT: don't leave this out
            }catch( Exception $e){
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=>$e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
	}
	
	/**
	 * Change a ticket's status
	 */
	public function change_status(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                    die ( __('Busted!','kanzu-support-desk') );
            }
            
            try{
                $this->do_admin_includes();	
                $tickets = new KSD_Tickets_Controller();	
                
                if( !is_array( $_POST['tkt_id'] ) ){//Single ticket update
                    $updated_ticket = new stdClass();
                    $updated_ticket->tkt_id = $_POST['tkt_id'];
                    $updated_ticket->new_tkt_status = $_POST['tkt_status'];
                    
                    if( $tickets->update_ticket( $updated_ticket ) ){
                        echo json_encode( __( 'Updated', 'kanzu-support-desk'));
                    }else {
                        throw new Exception( __( 'Failed', 'kanzu-support-desk') , -1);
                    }
                }
                else{//Update tickets in bulk
                    $updateArray = array( "tkt_status" => $_POST['tkt_status'] );
                    if( is_array( $tickets->bulk_update_ticket(  $_POST['tkt_id'], $updateArray ) )  ){
                        echo json_encode( __( 'Tickets Updated', 'kanzu-support-desk'));
                    }else {
                        throw new Exception( __( 'Updates Failed', 'kanzu-support-desk') , -1);
                    }
                }
		die();// IMPORTANT: don't leave this out
            }catch( Exception $e){ 
                $response = array( 
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
	}
   

        /**
         * Change ticket's severity
         * @throws Exception
         */
        public function change_severity(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                    die ( __('Busted!','kanzu-support-desk') );
            }
            
            try{
                $this->do_admin_includes();	
                $updated_ticket = new stdClass();
		$updated_ticket->tkt_id = $_POST['tkt_id'];
		$updated_ticket->new_tkt_severity = $_POST['tkt_severity'];
                
		$tickets = new KSD_Tickets_Controller();	
                
                if( $tickets->update_ticket( $updated_ticket ) ){
                    echo json_encode( __( 'Updated', 'kanzu-support-desk'));
                }else {
                    throw new Exception( __( 'Failed', 'kanzu-support-desk') , -1);
                }
		die();// IMPORTANT: don't leave this out
            }catch( Exception $e){ 
                $response = array( 
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
	}
        
        /**
	 * Change a ticket's assignment
	 */
	public function assign_to(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                    die ( __('Busted!','kanzu-support-desk') );
            }
           try{
               $this->do_admin_includes();
                $assign_ticket = new KSD_Tickets_Controller();
                if (!is_array($_POST['tkt_id'])) {//Single ticket re-assignment
                    $updated_ticket = new stdClass();
                    $updated_ticket->tkt_id = $_POST['tkt_id'];
                    $updated_ticket->new_tkt_assigned_to = $_POST['tkt_assign_assigned_to'];
                    $updated_ticket->new_tkt_assigned_by = $_POST['ksd_current_user_id'];
                    if ($assign_ticket->update_ticket($updated_ticket)) {
                        //Add the event to the assignments table
                        $this->do_ticket_assignment($updated_ticket->tkt_id, $updated_ticket->new_tkt_assigned_to, $updated_ticket->new_tkt_assigned_by);
                        echo json_encode(__('Re-assigned', 'kanzu-support-desk'));
                    } else {
                        throw new Exception(__('Failed', 'kanzu-support-desk'), -1);
                    }
                } else {//Bulk re-assignment
                    $update_array = array ( 
                        'tkt_assigned_to' => $_POST['tkt_assign_assigned_to'],
                        'tkt_assigned_by' => $_POST['ksd_current_user_id']
                            );
                   if( is_array( $assign_ticket->bulk_update_ticket($_POST['tkt_id'], $update_array ) ) ){
                       //Add event to assignments table
                       foreach ( $_POST['tkt_id'] as $tktID ){
                        $this->do_ticket_assignment( $tktID, $update_array['tkt_assigned_to'], $update_array['tkt_assigned_by'] );
                       }
                       echo json_encode(__('Tickets Re-assigned', 'kanzu-support-desk'));
                    } else {
                        throw new Exception(__('Ticket Re-assignment Failed', 'kanzu-support-desk'), -1);
                    }                    
                }
                die();// IMPORTANT: don't leave this out
           }catch( Exception $e){
               $response = array(
                   'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
               );
               echo json_encode($response);	
               die();// IMPORTANT: don't leave this out
           }  
	}
        
        /**
         * Add a reply to a single ticket
         * @param Array $ticket_reply_array The ticket reply Array. This exists wnen this function is called
         * by an add-on
         * Note that add-ons have to provide tkt_id too. It's retrieved in the check before this function
         * is called
         */
        
        public function reply_ticket( $ticket_reply_array=null ){
            //In add-on mode, this function was called by an add-on
            $add_on_mode = ( is_array( $ticket_reply_array ) ? true : false );
            
            //Front end reply nonce check
            if( isset( $_POST['ksd_new_reply_nonce'] ) ){
                $_POST['ksd_admin_nonce'] = $_POST['ksd_new_reply_nonce'];
            }
            
            if ( ! $add_on_mode ){//Check for NONCE if not in add-on mode     
                if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
	 		 die ( __('Busted!','kanzu-support-desk') );
                }
            }
            $this->do_admin_includes();
                
                try{
                    $new_reply = array(); 
                    //If this was called by an add-on, populate the $_POST array
                    if ( $add_on_mode ){
                        $_POST = $ticket_reply_array;
                        $new_reply['post_author'] = $_POST['ksd_rep_created_by'];
                    }
                    else{
                         $new_reply['post_author'] = get_current_user_id();
                    }
                    $parent_ticket_ID = sanitize_text_field( $_POST['tkt_id'] );                    
                    $new_reply['post_title']      = wp_strip_all_tags( $_POST['ksd_reply_title'] );               
                    $new_reply['post_parent']     = $parent_ticket_ID;    
                    //Add KSD reply defaults
                    $new_reply['post_type']       = 'ksd_reply';
                    $new_reply['post_status']     = 'publish';
                    $new_reply['comment_status']  = 'closed ';      
                    
                    $cc = null;
                    if( isset($_POST['ksd_tkt_cc']) && $_POST['ksd_tkt_cc'] != __( 'CC','kanzu-support-desk' ) ){
                        $new_reply['rep_cc']       = sanitize_text_field( $_POST[ 'ksd_tkt_cc' ] );
                        $cc = $_POST['ksd_tkt_cc'];
                    }
                
                    if( isset( $_POST['ksd_rep_date_created'])){//Set by add-ons
                        $new_reply['post_date'] =  $this->validate_post_date( sanitize_text_field( $_POST['ksd_rep_date_created'] ) );  
                    }

                    $new_reply['post_content']	 = wp_kses_post( stripslashes( $_POST['ksd_ticket_reply'] )  );
                    if ( strlen( $new_reply['post_content'] ) < 2 && ! $add_on_mode ){//If the response sent it too short
                       throw new Exception( __("Error | Reply too short", 'kanzu-support-desk'), -1 );
                    }
                   //Add the reply to the replies table                         
                   $new_reply_id = wp_insert_post( $new_reply );
                            
                    //Update the main ticket's tkt_time_updated field.  
                    $parent_ticket = get_post( $parent_ticket_ID );
                    $parent_ticket->post_modified = current_time('mysql');
                    wp_update_post( $parent_ticket );
                    
                    //Do notifications
                    if ( $parent_ticket->post_author == $new_reply['post_author'] ){//This is a reply from the customer. Notify the assignee
                        $notify_user = $this->get_ticket_assignee_to_notify( $parent_ticket_ID );
                    }
                    else{//This is a reply from an agent. Notify the customer               
                        $notify_user = get_userdata( $parent_ticket->post_author );                        
                    }
                    $this->send_email( $notify_user->user_email, $new_reply['post_content'].Kanzu_Support_Desk::output_ksd_signature( $parent_ticket_ID, false ), 'Re: '.$parent_ticket->post_title, $cc );//NOTE: Prefix the reply subject with Re:    
                    
                    if( $add_on_mode ){
                       do_action( 'ksd_new_reply_logged', $_POST['ksd_addon_tkt_id'], $new_reply_id );
                       return;//End the party if this came from an add-on. All an add-on needs if for the reply to be logged
                   }

                   if ( $new_reply_id > 0 ){
                      //Add 'post_author' to the response
                       $new_reply['post_author'] = get_userdata ( get_current_user_id() )->display_name;
                      echo json_encode(  $new_reply  );
                   }else{
                       throw new Exception( __("Error", 'kanzu-support-desk'), -1 );
                   }
                   die();// IMPORTANT: don't leave this out
                }catch( Exception $e){
                    $response = array(
                        'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                    );
                    echo json_encode($response);	
                    die();// IMPORTANT: don't leave this out
                }  

        }
        
        /**
         * Validate the post date before saving a post. This is usually set by add-ons
         * Adapted from wp-includes/post.php
         * @param Date $post_date
         * @return Date in form 0000-00-00 00:00:00
         * @since 2.0.0
         */
        private function validate_post_date( $post_date ) {
            if ( empty( $post_date ) || '0000-00-00 00:00:00' == $post_date ){
               return current_time('mysql');
            }
            // validate the date
            $mm = substr( $post_date, 5, 2);
            $jj = substr( $post_date, 8, 2);
            $aa = substr( $post_date, 0, 4);
            $valid_date = wp_checkdate($mm, $jj, $aa, $post_date);
            if ( !$valid_date ) {
                return current_time('mysql');
            }
            return $valid_date;
        }

        /**
         * Filter KSD ticket message content in new tickets and in replies before it is displayed
         * or before it is sent in an email
         * This ensures that:
         *                   Double line-breaks in the text are converted into HTML paragraphs (<p>...</p>). 
         *                   Returns given text with transformations of quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol. 
         * @param string $raw_message The ticket message 
         * @return string The formatted message
         * @since 1.7.0
         */
        private function format_message_content_for_viewing( $raw_message ){
            return wpautop( wptexturize( str_replace( ']]>', ']]&gt;', $raw_message ) ) );//wpautop does the <p> replacements, wptexturize does the transformations
        }
        
        /**
         * Log new tickets or replies initiated by add-ons
         * Generally, this is called whenever a new ticket is logged
         * using the action ksd_log_new_ticket [and not the AJAX version
         * of the same action]
         * @param Array $new_ticket New ticket array. Can also be a reply array
         * @since 1.0.1
         */
        public function do_log_new_ticket( $new_ticket ){      
            $this->do_admin_includes();
            $TC = new KSD_Tickets_Controller();
            //Check if this was initiated from our notify_email, in which case it is a reply/new ticket from an agent  
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            if( $ksd_settings['notify_email'] == $new_ticket->cust_email ){
                $agent_initiated_ticket = true;
            }
            //First check if the ticket initiator exists in our users table. 
            $customer_details = get_user_by ( 'email', $new_ticket['ksd_cust_email'] );
            if ( $customer_details ){//Customer's already in the Db, get their customer ID  
               //Check whether it is a new ticket or a reply. We match against subject and ticket initiator
                $new_ticket['ksd_tkt_cust_id'] = $customer_details->ID;
                $value_parameters   = array();
                $filter             = " post_title = %s AND post_status != %d AND post_author = %d ";
                
                $value_parameters[] = sanitize_text_field( str_ireplace( "Re:", "", $new_ticket['ksd_tkt_subject'] ) ) ;  //Remove the Re: prefix from the subject of replies. @TODO Stands a very tiny chance of replacing other RE's in the subject                                                                                                                    //Note that we use str_ireplace because it is less expensive than preg_replace
                $value_parameters[] = 'resolved' ;
                $value_parameters[] = $new_ticket['ksd_tkt_cust_id'] ;
                
                $the_ticket = $TC->get_tickets( $filter, $value_parameters );
                if ( count( $the_ticket ) > 0  ){//This is a reply
                    //Create the array the reply function expects
                    $new_ticket['tkt_id']                       = $the_ticket[0]->ID;
                    $new_ticket['ksd_reply_title']              = $new_ticket['ksd_tkt_subject'];                      
                    $new_ticket['ksd_ticket_reply']             = $new_ticket['ksd_tkt_message'];  
                    $new_ticket['ksd_rep_created_by']           = $new_ticket['ksd_tkt_cust_id'];  
                    $new_ticket['ksd_rep_date_created']         = $new_ticket['ksd_tkt_time_logged'];  
                    $this->reply_ticket( $new_ticket ); 
                    return; //die removed because it was triggering a fatal error in add-ons
               }

            }
            if ( $agent_initiated_ticket ){//This is a new ticket from an agent. We attribute it to the primary admin in the system
                $new_ticket->tkt_cust_id = 1;
            }
            //This is a new ticket
            $this->log_new_ticket( $new_ticket, true );                        
        }
        
        /**
         * Convert a reply's object into a $_POST array
         * @param int $ticket_ID The parent ticket's ID
         * @param Object $reply The reply's object
         * @since 1.7.0
         */
        private function convert_reply_object_to_post( $ticket_ID, $reply ){
            $_POST                          = array();
            $_POST['tkt_id']                = $ticket_ID; //The ticket ID
            $_POST['ksd_ticket_reply']      = $reply->tkt_message; //Get the reply
            $_POST['ksd_rep_created_by']    = $reply->tkt_cust_id; //The customer's ID
            $_POST['ksd_rep_date_created']  = $reply->tkt_time_logged; //@since 1.6.2
            $_POST['ksd_addon_tkt_id']      = $reply->addon_tkt_id; //The add-on's ID for this ticket

            if ( isset( $reply->tkt_cc ) ) {
                $_POST['ksd_tkt_cc'] = $reply->tkt_cc;
            }
            return $_POST;
        }
        
        
        /**
         * Change a new ticket object into a $_POST array. $POST arrays are 
         * used by the functions that log new tickets & replies
         * Add-ons on the other hand supply $new_ticket objects. This function
         * is a bridge between the two
         * @param Object $new_ticket New ticket object
         * @return Array $_POST An array used by the functions that log new tickets. This
         *                      array is basically the same as the object but has ksd_ prefixing all keys 
         */
        private function convert_ticket_object_to_post( $new_ticket ){
            $_POST = array();
            foreach ( $new_ticket as $key => $value ) {
               $_POST[ 'ksd_'.$key ] = $value;
            }
            return $_POST;
        }
        
        /**
         * Send agent replies to customers 
         * @param int $customer_ID The customer's ID
         * @param string $message The message to send to the customer
         * @param string $subject The message subject
         * @return N/A
         */
        private function send_agent_reply( $customer_ID, $message, $subject ){
            $cust_info = get_userdata( $customer_ID );
            $this->send_email( $cust_info->user_email, $message, 'Re: '.$subject );        
        }
        
        /**
         * Get the ticket assignee of a new reply or ticket
         * If no assignee exists, return the primary admin
         * @return Object User
         * @since 2.0.0
         */
        private function get_ticket_assignee_to_notify( $tkt_id ){
            //$parent_ticket_ID, $new_reply['post_content'], 'Re: '.$parent_ticket->post_title,$cc
            $assignee_id = get_post_meta( $tkt_id, '_ksd_tkt_info_assigned_to', true );
            if ( empty( $assignee_id ) || 0 != $assignee_id ){//No assignee
                $assignee_id = 1;
            }
            return get_userdata( $assignee_id );            
        }
        
        /**
         * Log new tickets.  The different channels (admin side, front-end) all 
         * call this method to log the ticket. Other plugins call $this->do_new_ticket_logging  through
         * an action
         * @param Array $new_ticket_raw A new ticket array. This is present when ticket logging was initiated 
         *              by an add-on and from the front-end
         * @param boolean $from_addon Whether the ticket was initiated by an addon or not
         */
        public function log_new_ticket( $new_ticket_raw=null, $from_addon = false ){
                if ( null == $new_ticket_raw ){
                    $new_ticket_raw = $_POST;
                }
               /* if( ! $from_addon ){//Check for NONCE if not in add-on mode
                    if ( ! wp_verify_nonce( $new_ticket_raw['new-ticket-nonce'], 'ksd-new-ticket' ) ){
                             die ( __('Busted!','kanzu-support-desk') );
                    }
                }//@TODO Update this
                */
                
		$this->do_admin_includes();
            
                try{
            	$supported__ticket_channels = array ( "admin-form","support-tab","email","sample-ticket" );   
                $tkt_channel                = sanitize_text_field( $new_ticket_raw['ksd_tkt_channel']);
                if ( !in_array( $tkt_channel, $supported__ticket_channels ) ){
                    throw new Exception( __('Error | Unsupported channel specified','kanzu-support-desk'), -1 );
                }                  
                            
                $ksd_excerpt_length = 30;//The excerpt length to use for the message
                               
                //Apply the pre-logging filter  
                $new_ticket_raw = apply_filters( 'ksd_insert_ticket_data', $new_ticket_raw  );
                
                //We sanitize each input before storing it in the database
                $new_ticket = array(); 
                $new_ticket['post_title']    	    = sanitize_text_field( stripslashes( $new_ticket_raw[ 'ksd_tkt_subject' ] ) );
                $sanitized_message                  = wp_kses_post( stripslashes( $new_ticket_raw[ 'ksd_tkt_message' ] ) );
                $new_ticket['post_excerpt']         = wp_trim_words( $sanitized_message, $ksd_excerpt_length );
                $new_ticket['post_content']         = $sanitized_message;                
                $new_ticket['post_status']          = ( isset( $new_ticket_raw[ 'ksd_tkt_status' ] ) && in_array( $new_ticket_raw[ 'ksd_tkt_status' ], array( 'new','open','pending', 'draft','resolved' )) ? sanitize_text_field( $new_ticket_raw[ 'ksd_tkt_status' ] ) : 'open' ); 
                
                if( isset( $new_ticket_raw[ 'ksd_tkt_time_logged' ] )){//Set by add-ons
                    $new_ticket['post_date']        = $new_ticket_raw[ 'ksd_tkt_time_logged' ];
                }//No need for an else; if this isn't specified, the current time is automatically used
                            
                
                //Server side validation for the inputs. Only holds if we aren't in add-on mode
                if ( ( ! $from_addon && strlen( $new_ticket['post_title'] ) < 2 || strlen( $new_ticket['post_content'] ) < 2 ) ) {
                     throw new Exception( __('Error | Your subject and message should be at least 2 characters','kanzu-support-desk'), -1 );
                }  
                               
                //Get the settings. We need them for tickets logged from the support tab
                $settings = Kanzu_Support_Desk::get_settings();                


                //Return a different message based on the channel the request came on
                $output_messages_by_channel = array();
                $output_messages_by_channel[ 'admin-form' ] = __( 'Ticket Logged. Sending notification...', 'kanzu-support-desk');
                $output_messages_by_channel[ 'support-tab' ] = $output_messages_by_channel[ 'email' ] = $settings['ticket_mail_message'];
                $output_messages_by_channel[ 'sample-ticket' ] = __( 'Sample tickets logged.', 'kanzu-support-desk');
    
                global $current_user;
                if ( $current_user->ID > 0 ) {//If it is a valid user
                    $new_ticket['post_author']  = $current_user->ID;
                    $cust_email                 = $current_user->user_email;
                }
                elseif ( isset ( $new_ticket_raw['ksd_tkt_cust_id'] ) ){//From addons
                    //@TODO Agents should not log tickets via add-ons otherwise the customer bug arises
                    $new_ticket['post_author']  = $new_ticket_raw['ksd_tkt_cust_id'];
                    $cust_email                 = $new_ticket_raw['ksd_cust_email'];
                }
                else{//The customer isn't in the Db. Let's add them. This is from an add-on
                    $cust_email           = sanitize_email( $new_ticket_raw[ 'ksd_cust_email' ] );//Get the provided email address
                    //Check that it is a valid email address. Don't do this check in add-on mode
                    if ( !is_email( $cust_email )){
                         throw new Exception( __('Error | Invalid email address specified','kanzu-support-desk') , -1);
                    } 
                    $new_customer = new stdClass();
                    $new_customer->user_email           = $cust_email;
                    //Check whether one or more than one customer name was provided
                    if( false === strpos( trim( sanitize_text_field( $new_ticket_raw[ 'ksd_cust_fullname' ] ) ), ' ') ){//Only one customer name was provided
                       $new_customer->first_name   =   sanitize_text_field( $new_ticket_raw[ 'ksd_cust_fullname' ] );
                    }
                    else{
                       preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $new_ticket_raw[ 'ksd_cust_fullname' ] ), $new_customer_fullname );
                        $new_customer->first_name   = $new_customer_fullname[1];
                        $new_customer->last_name   = $new_customer_fullname[2];//We store everything besides the first name in the last name field
                    }
                    //Add the customer to the user table and get the customer ID
                    $new_ticket['post_author']    =  $this->create_new_customer( $new_customer );
                }     

               //@TODO Separate action to log a private note needed
                
                //Add KSD ticket defaults       
                $new_ticket['post_type']      = 'ksd_ticket';
                $new_ticket['comment_status'] = 'closed ';
                
                //Log the ticket
                $new_ticket_id = wp_insert_post( $new_ticket );
                $notify_user = get_userdata( 1 );//User to notify. Defaults to admin if ticket doesn't have an assignee
                
                //Add meta fields
                $meta_array                             = array();
                $meta_array['_ksd_tkt_info_channel']    = $tkt_channel;
                if( isset( $new_ticket_raw['ksd_tkt_cc'] ) && $new_ticket_raw['ksd_tkt_cc'] != __( 'CC','kanzu-support-desk' ) ){
                    $meta_array['_ksd_tkt_info_cc']     = sanitize_text_field( $new_ticket_raw[ 'ksd_tkt_cc' ] );
                }
                                
                //These other fields are only available if a ticket is logged from the admin side so we need to 
                //first check if they are set
                if ( isset( $new_ticket_raw[ 'ksd_tkt_severity' ] ) ) {
                    $meta_array['_ksd_tkt_info_severity']   =  $new_ticket_raw[ 'ksd_tkt_severity' ] ;   
                }
                if ( isset( $new_ticket_raw[ 'ksd_tkt_assigned_to' ] ) && !empty( $new_ticket_raw[ 'ksd_tkt_assigned_to' ] ) ) {
                    $meta_array['_ksd_tkt_info_assigned_to']            =  $new_ticket_raw[ 'ksd_tkt_assigned_to' ] ;
                    $notify_user    = get_userdata(  $meta_array['_ksd_tkt_info_assigned_to'] );
                } 
                //If the ticket wasn't assigned by the user, check whether auto-assignment is set so we auto-assign it
                if ( empty( $new_ticket_raw[ 'ksd_tkt_assigned_to' ] ) &&  !empty( $settings['auto_assign_user'] ) ) {
                    $meta_array['_ksd_tkt_info_assigned_to']            = $settings['auto_assign_user'];                    
                }
                
                //Save ticket meta info
                $this->save_ticket_meta_info( $new_ticket_id, $new_ticket['post_title'], $meta_array );

                $new_ticket_status = (  $new_ticket_id > 0  ? $output_messages_by_channel[ $tkt_channel ] : __("Error", 'kanzu-support-desk') );

                //@TODO Save the attachments
                //if( isset( $new_ticket_raw['ksd_attachments'] ) ){
                //    $this->add_ticket_attachments( $new_ticket_id, $new_ticket_raw['ksd_attachments'] );
                //}
                
                //If the ticket was logged by using the import feature, end the party here
                if( isset( $new_ticket_raw['ksd_tkt_imported'] ) ){
                   do_action( 'ksd_new_ticket_imported', array( $new_ticket_raw['ksd_tkt_imported_id'], $new_ticket_id ) );
                   return;
                }
                
               //Notify the customer that their ticket has been logged. CC is only used for tickets logged by admin-form 
                if ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "support-tab" ){                    
                    $cc = null;
                    if( isset( $new_ticket_raw['ksd_tkt_cc'] ) && $new_ticket_raw['ksd_tkt_cc'] !== __( 'CC','kanzu-support-desk' ) ) { 
                        $cc = $new_ticket_raw['ksd_tkt_cc'];
                    }                   
                    $this->send_email( $cust_email , $cc );    
                }
                
                //For add-ons to do something after new ticket is added. We share the ID and the final status
                if ( isset( $new_ticket_raw['ksd_addon_tkt_id'] ) ) {                    
                    do_action( 'ksd_new_ticket_logged', $new_ticket_raw['ksd_addon_tkt_id'], $new_ticket_id );
                }
                //@TODO If $tkt_channel  ==  "admin-form", notify the customer
                //@TODO If agent logs new ticket by addon, notify the customer
                if ( $tkt_channel  !==  "admin-form" && $tkt_channel  !==  "sample-ticket" ){//Notify the agent  
                    $ksd_attachments = ( isset ( $new_ticket_raw['ksd_attachments'] ) ? $this->convert_attachments_for_mail( $new_ticket_raw['ksd_attachments'] ) : array() );
                    $this->do_notify_new_ticket( $notify_user->user_email, $new_ticket_id, $cust_email, $new_ticket['post_title'], $new_ticket['post_content'], $ksd_attachments );
                }
                //If this was initiated by the email add-on, end the party here
                if ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "email"){
                     $this->send_email( $cust_email );//Send an auto-reply to the customer                     
                     return;
                }
                
                if( $from_addon ) {
                    return; //For addon mode to ensure graceful exit from function. 
                }
                
                echo json_encode( $new_ticket_status );
                die();// IMPORTANT: don't leave this out
                
                }catch( Exception $e){
                    $response = array(
                        'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                    );
                    echo json_encode($response);	
                    die();// IMPORTANT: don't leave this out
                }  
        }
        
        /**
         * update a ticket's activity
         * @since 2.0.0
         */
        private function update_ticket_activity( $changed_item, $ticket_title, $ticket_id, $old_value, $new_value ){
           $this->do_admin_includes();
           try {
                $new_ticket_activity = array();
                $new_ticket_activity['post_title']     = $ticket_title;
                $new_ticket_activity['post_parent']    = $ticket_id;                
                //Add KSD ticket activity defaults       
                $new_ticket_activity['post_type']      = 'ksd_ticket_activity';
                $new_ticket_activity['post_status']    = 'private';
                $new_ticket_activity['comment_status'] = 'closed ';
                //Note that the person who did this assignment is captured in the post_author field which is autopopulated by current user's ID
                
                switch ( $changed_item ) {
                    case '_ksd_tkt_info_severity':
                            $old_value = ( '' == $old_value ? 'low' : $old_value ); 
                            $activity_content = sprintf( __( 'changed severity from %1$s to %2$s','kanzu-support-desk' ), $old_value, $new_value ); 
                        break;
                    case '_ksd_tkt_info_assigned_to': 
                        $old_value_name = ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$old_value}").'">'.get_userdata( $old_value )->display_name.'</a>' );
                        $new_value_name = ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$new_value}").'">'.get_userdata( $new_value )->display_name.'</a>' );
                        $activity_content = sprintf( __( 're-assigned ticket from %1$s to %2$s','kanzu-support-desk' ), $old_value_name, $new_value_name );                         
                        break;
                    case '_ksd_tkt_info_customer': 
                        $old_value_name =  ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$old_value}").'">'.get_userdata( $old_value )->display_name.'</a>' );
                        $new_value_name =  ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$new_value}").'">'.get_userdata( $new_value )->display_name.'</a>' );
                        $activity_content = sprintf( __( ' created ticket for %1$s','kanzu-support-desk' ), $new_value_name );                         
                        break;
                    default:
                        return false;//Any unsupported meta key, end the party here
                }                
                
                $new_ticket_activity['post_content'] = $activity_content;
                    
                //Save the assignment                       
                $new_ticket_activity_id = wp_insert_post( $new_ticket_activity );

                if ( $new_ticket_activity_id > 0) {
                    return true;
                } else {
                    return false;
                }                    
            } catch (Exception $e) {
                return false;
            }           
        }
        
        /**
         * Add attachment(s) to a ticket
         * Call this after $this->do_admin_includes() is called
         * @param int $ticket_id The ticket or reply's ID
         * @param Array $attachments_array Array containing the attachments
         *        The array is of the form:
                        Array
                        (
                            [url] => Array
                                (
                                    [0] => http://url/filename.txt
                                    [1] => http://url/filename.jpg
                                )

                            [size] => Array
                                (
                                    [0] => 724 B
                                    [1] => 146 kB
                                )

                            [filename] => Array
                                (
                                    [0] => filename.txt
                                    [1] => filename.jpg
                                )
         * @param Boolean $is_reply Whether this is a reply or a ticket
         */
        private function add_ticket_attachments( $ticket_id, $attachments_array, $is_reply=false ) {
            for ( $i = 0; $i < count( $attachments_array['url'] ); $i++ ) {
                $AC = new KSD_Attachments_Controller();//We don't sanitize these values because they aren't supplied by the user. The system generates them
                $AC->add_attachment( $ticket_id, $attachments_array['url'][$i], $attachments_array['size'][$i], $attachments_array['filename'][$i], $is_reply );
            }
        }
        
        /**
         * Modify the ticket's attachments array for sending in mail.
         * The mail attachments array only contains filenames
         * @param Array $tickets_attachments_array The ticket attachment's array
         * @return Array $mail_attachments_array The attachments array to add to mail
         * @since 1.7.0
         */
        private function convert_attachments_for_mail( $tickets_attachments_array ){
            $mail_attachments_array = array();
            $upload_dir = wp_upload_dir();
            $attachments_dir = $upload_dir['basedir'] . '/ksd/attachments/';
            foreach ( $tickets_attachments_array['filename'] as $single_attached_file ){
                $mail_attachments_array[] = $attachments_dir.$single_attached_file;
            }         
            return $mail_attachments_array;
        }

        /**
         * Replace a ticket's logged_by field with the nicename of the user who logged it
         * Replace the tkt_time_logged with a date better-suited for viewing
         * NB: Because we use {@link KSD_Users_Controller}, call this function after {@link do_admin_includes} has been called.   
         * @param Object $ticket The ticket to modify
         * @param boolean $single_ticket_view Whether we are in single ticket view or not
         */
        private function format_ticket_for_viewing( $ticket, $single_ticket_view = false ){
            //If the ticket was logged by an agent from the admin end, then the username is available in wp_users. Otherwise, we retrive the name
            //from the KSD customers table
           // $tmp_tkt_assigned_by = ( 'admin-form' === $ticket->tkt_channel ? $users->get_user($ticket->tkt_assigned_by)->display_name : $CC->get_customer($ticket->tkt_assigned_by)->cust_firstname );
            $tkt_user_data      =  get_userdata( $ticket->tkt_cust_id );
            $tmp_tkt_cust_id    =  $tkt_user_data->display_name;
            if( $single_ticket_view ){
                $tmp_tkt_cust_id.=  ' <'.$tkt_user_data->user_email.'>';
                $ticket->tkt_message = $this->format_message_content_for_viewing( $ticket->tkt_message );
            }
            //Replace the tkt_assigned_by name with a prettier one
            $ticket->tkt_cust_id = str_replace($ticket->tkt_cust_id,$tmp_tkt_cust_id,$ticket->tkt_cust_id);
            //Replace the date 
            $ticket->tkt_time_logged = date('M d',strtotime($ticket->tkt_time_logged));
            
            return $ticket;
        }
        
		
		/**
		 * Generate the ticket volumes displayed in the graph in the dashboard
		 */
		public function get_dashboard_ticket_volume(){
                    try{
			 if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
				 die ( __('Busted!','kanzu-support-desk') );
                         }
			$this->do_admin_includes();
			$tickets = new KSD_Tickets_Controller();		
			$tickets_raw = $tickets->get_dashboard_graph_statistics();
                        //If there are no tickets, the road ends here
                        if ( count( $tickets_raw ) < 1 ) {
                            $response = array(
                                'error'=> array( 
                                        'message' => __( "No logged tickets. Graphing isn't possible", "kanzu-support-desk") , 
                                        'code'=> -1 )
                            );
                            echo json_encode($response);	
                            die();// IMPORTANT: don't leave this out
                        }
                        
                        $y_axis_label = __( 'Day', 'kanzu-support-desk');
                        $x_axis_label = __( 'Ticket Volume', 'kanzu-support-desk');
                        
			$output_array = array();
                        $output_array[] = array( $y_axis_label,$x_axis_label );
                        
			foreach ( $tickets_raw as $ticket ) {
				$output_array[] = array ( date_format(date_create($ticket->date_logged),'d-m-Y') ,(float)$ticket->ticket_volume);//@since 1.1.2 Added casting since JSON_NUMERIC_CHECK was kicked out 			
			}        
                        echo json_encode( $output_array );//@since 1.1.2 Removed JSON_NUMERIC_CHECK which is only supported PHP >=5.3
			die();//Important
                        
                    }catch( Exception $e){
                        $response = array(
                            'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                        );
                        echo json_encode($response);	
                        die();// IMPORTANT: don't leave this out
                    }  
		}
                /**
                 * Get the statistics that show on the dashboard, above the graph
                 */
                public function get_dashboard_summary_stats(){
                    if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
				 die ( __('Busted!','kanzu-support-desk') );
                    }
                    $this->do_admin_includes();
                    try{
                        $tickets = new KSD_Tickets_Controller();	
                        $summary_stats = $tickets->get_dashboard_statistics_summary();
                        //Compute the average. We do this here rather than using AVG in the DB query to take the load off the Db
                        $total_response_time = 0;
                        foreach ( $summary_stats["response_times"] as $response_time ) {
                            $total_response_time+=$response_time->time_difference;
                        }
                        //Prevent division by zero
                        if ( count($summary_stats["response_times"]) > 0 ){
                            $summary_stats["average_response_time"] = date('H:i:s', $total_response_time/count($summary_stats["response_times"]) ) ;
                        }else{
                            $summary_stats["average_response_time"] = '00:00:00';
                        }
                        echo json_encode ( $summary_stats );                    
                    }catch( Exception $e){
                        $response = array(
                            'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                        );
                        echo json_encode( $response );                      
                    }  
                     die();// IMPORTANT: don't leave this out
                }
         
         /**
          * Update all settings
          */
         public function update_settings(){
            if ( ! wp_verify_nonce( $_POST['update-settings-nonce'], 'ksd-update-settings' ) ){
                die ( __('Busted!','kanzu-support-desk') );
            }                
            try{
                $updated_settings = Kanzu_Support_Desk::get_settings();//Get current settings
                //Iterate through the new settings and save them. We skip all multiple checkboxes; those are handled later. As of 1.5.0, there's only one set of multiple checkboxes, ticket_management_roles
                foreach ( $updated_settings as $option_name => $current_value ) {
                    if( $option_name == 'ticket_management_roles' ){
                        continue;//Don't handle multiple checkboxes in here @since 1.5.0
                    }
                    if( $option_name == 'ticket_mail_message' ){//Support HTML in ticket message @since 1.7.0   
                        $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) ? wp_kses_post ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
                        continue;
                    }                    
                    $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) ? sanitize_text_field ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
                }
                //For a checkbox, if it is unchecked then it won't be set in $_POST
                $checkbox_names = array("show_support_tab","tour_mode","enable_new_tkt_notifxns","enable_recaptcha","enable_notify_on_new_ticket","enable_anonymous_tracking");
                //Iterate through the checkboxes and set the value to "no" for all that aren't set
                foreach ( $checkbox_names as $checkbox_name ){
                     $updated_settings[$checkbox_name] = ( !isset ( $_POST[$checkbox_name] ) ? "no" : $updated_settings[$checkbox_name] );
                }      
                //Now handle the multiple checkboxes. As of 1.5.0, only have ticket_management_roles. If it isn't set, use administrator
                $updated_settings['ticket_management_roles'] = !isset( $_POST['ticket_management_roles'] ) ? "administrator" : $this->convert_multiple_checkbox_to_setting( $_POST['ticket_management_roles'] );
            
                //Apply the settings filter to get settings from add-ons
                $updated_settings = apply_filters( 'ksd_settings', $updated_settings, $_POST );
                
                $status = update_option( KSD_OPTIONS_KEY, $updated_settings );
                
                if( true === $status){
                    do_action( 'ksd_settings_saved' );
                   echo json_encode(  __( 'Settings Updated', 'kanzu-support-desk'));
                }else{
                    throw new Exception(__( 'Update failed. Please retry.', 'kanzu-support-desk'), -1);
                }
                die();
            }catch( Exception $e){
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
         }
         
         /**
          * Reset settings to default
          */
         public function reset_settings(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );
             }
             try{
                $base_settings = KSD_Install::get_default_options();
                //Add the settings from add-ons
                $base_settings = apply_filters( 'ksd_settings', $base_settings );
                $status = update_option( KSD_OPTIONS_KEY, $base_settings );
                if( $status){
                    echo json_encode( __( 'Settings Reset', 'kanzu-support-desk') );
                }else{
                    throw new Exception( __( 'Reset failed. Please retry', 'kanzu-support-desk'), -1);
                }                    
                die();
            }catch( Exception $e){
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
         }
        
         /**
          * Retrieve and display the list of add-ons
          * @since 1.1.0
          */
         public function load_ksd_addons(){                    
            ob_start();  
            if ( false === ( $cache = get_transient( 'ksd_add_ons_feed' ) ) ) {
		$feed = wp_remote_get( 'https://kanzucode.com/?feed=ksdaddons', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {                   
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'ksd_add_ons_feed', $cache, 3600 );
			}
		} else {
			$cache = '<div class="add-on-error add-ons"><p>' . __( 'Sorry, an error occurred while retrieving the add-ons list. A re-attempt will be made later. Thank you.', 'kanzu-support-desk' ) . '</div>';
		}
            }
        echo $cache;
        echo ob_get_clean();    
        }
         
         /**
          * Update a ticket's private note
          */
         public function update_private_note() {
            //  if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ){//@TODO Update this
            //	 die ( __('Busted!','kanzu-support-desk') );
            //   }
            $this->do_admin_includes();
            try {
                $new_private_note = array();
                $new_private_note['post_title']     = wp_strip_all_tags($_POST['ksd_reply_title']);
                $new_private_note['post_parent']    = sanitize_text_field($_POST['tkt_id']);
                //Add KSD private_note defaults
                $new_private_note['post_type']      = 'ksd_private_note';
                $new_private_note['post_status']    = 'private';
                $new_private_note['comment_status'] = 'closed ';

                $new_private_note['post_content'] = wp_kses_post(stripslashes($_POST['tkt_private_note']));
                if ( strlen( $new_private_note['post_content'] ) < 2 ) {//If the private note sent it too short
                    throw new Exception(__("Error | Private Note too short", 'kanzu-support-desk'), -1);
                }
                //Save the private_note                         
                $new_private_note_id = wp_insert_post( $new_private_note );

                if ( $new_private_note_id > 0) {
                    //Add 'post_author' to the response
                    $new_private_note['post_author'] = get_userdata ( get_current_user_id() )->display_name;
                    $response = $new_private_note;
                } else {
                    throw new Exception(__('Failed', 'kanzu-support-desk'), -1);
                }                    
            } catch (Exception $e) {
                $response = array(
                    'error' => array('message' => $e->getMessage(), 'code' => $e->getCode())
                );
            }
            echo json_encode( $response );
            die(); // IMPORTANT: don't leave this out
        }
        
        /**
         * Update a ticket's information
         * @since 2.0.0
         */
        public function update_ticket_info(){
           if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
            	 die ( __('Busted!','kanzu-support-desk') );
            }
            $this->do_admin_includes();
            try {
                $tkt_id = wp_strip_all_tags( $_POST['tkt_id'] );
                $ticket_title = wp_strip_all_tags( $_POST['ksd_reply_title'] );
                $tkt_info = array();
                parse_str(  wp_strip_all_tags ( $_POST['ksd_tkt_info'] ), $tkt_info ) ;
                
                //Update ticket status
                $post = get_post( $tkt_id );
                $post->post_status = $tkt_info['_ksd_tkt_info_status'];
                wp_update_post( $post );
                
                //Update the meta information
                foreach ( $tkt_info as $tkt_info_meta_key => $tkt_info_new_value ){    
                        if( get_post_meta( $tkt_id, $tkt_info_meta_key, true ) == $tkt_info_new_value )
                                continue;
                        $this->update_ticket_activity( $tkt_info_meta_key, $ticket_title, $tkt_id, get_post_meta( $tkt_id, $tkt_info_meta_key, true ), $tkt_info_new_value );
                        update_post_meta( $tkt_id, $tkt_info_meta_key, $tkt_info_new_value ); 
                    }

                $response = __( 'Ticket information updated', 'kanzu-support-desk' );
                    
            } catch ( Exception $e ) {
                $response = array(
                    'error' => array('message' => $e->getMessage(), 'code' => $e->getCode())
                );
            }
            echo json_encode( $response );
            die(); // IMPORTANT: don't leave this out            
        }

        /**
          * Give the user an introductory tour to KSD
          * @return Array $pointers Returns an array of pointers or false
          * @since 1.1.0
          */
         private function load_intro_tour(){
            // Don't run on WP < 3.3. The 'pointers' used to give the
            //intro tour were only introduced in WP 3.3
            if ( get_bloginfo( 'version' ) < '3.3' ){
                return false;                
            }
            //Check if tour mode is on
            $KSD_settings = Kanzu_Support_Desk::get_settings();
            if ( "no" === $KSD_settings['tour_mode'] ){
                return false;
             }
            //Generate the tour messages
            $pointers = $this->generate_tour_content();
            
            // No pointers? Then we stop.
            if ( ! $pointers || ! is_array( $pointers ) ){
                return false;
            }
            wp_enqueue_style( 'wp-pointer' );
            wp_enqueue_script( 'wp-pointer' );
            
            return $pointers;
         }
         
         /**
          * The tour content for the different screens
          * @since 1.1.0
          */
         private function generate_tour_content(){
             //The content is entered into the array based on which tab it'll show on
             //Content for tab 1 is entered first and for tab n is entered at $p[n]
             $p[] = array(
                "target" => "#ksd_dashboard_chart",
                "tab"  => 0, //Which tab (Dashboard, tickets, new ticket, etc) to show the pointer on
                "options" => array(
                    "content" => sprintf( "<span><h3> %s </h3> <p> %s </p><p> %s </p></span>",
                    __( 'Kanzu Support Desk Dashboard', 'kanzu-support-desk'),
                    __( "Welcome to Kanzu Support Desk! Thanks for choosing us. Let's see where everything is. First off...", "kanzu-support-desk"),
                    __( 'Your dashboard displays your performance statistics', 'kanzu-support-desk')
                    ),
                    "button2"  => __( 'Next', 'kanzu-support-desk'),
                    "function" => 'window.location="' . admin_url( 'admin.php?page=wpseo_titles' ) . '";',
                    "position" => array( 'edge' => 'right', 'align' => 'top' )
                    )                
            );
            $p[] = array(
                'target' => '.more_nav',
                'tab'  => 0,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p></span>',
                    __( 'Notifications' ,'kanzu-support-desk'),
                    __( 'Notifications to keep you up-to-date with all things KSD! Get the latest news and tips right here.','kanzu-support-desk')                    
                    ),
                    'position' => array( 'edge' => 'right', 'align' => 'right', 'nudgehorizontal' => -2 )
                )
            );
            $p[] = array(
                'target' => '.ticket-list',
                'tab'  => 1,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p><p> %s </p><p> %s </p></span>',
                    __( 'The tickets' ,'kanzu-support-desk'),
                    __( 'All your tickets are displayed here. Filter tickets using the filters at the top left','kanzu-support-desk'),
                    __( 'Search, refresh and paginate the view using the buttons at the top right.' ,'kanzu-support-desk'),
                    __( 'View ticket details by clicking on a single ticket' ,'kanzu-support-desk')                    
                            ),
                    'position' => array( 'edge' => 'left', 'align' => 'top', 'nudgehorizontal' => 50, 'nudgevertical' => 20 )               
                )                
            );
            $p[] = array(
                'target' => '#tickets',
                'tab'  => 1,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3><ul class="tour"><li class="new">N</li><li class="open">O</li><li class="pending">P</li><li class="resolved">R</li></ul></p><p>%s </p><p><img width="157" height="166" src="%s" /></p><p> %s </p></span>',
                    __( 'Ticket Status & Severity' ,'kanzu-support-desk'),                    
                    __( 'These labels show New, Open, Pending & Resolved tickets respectively' ,'kanzu-support-desk'),
                     KSD_PLUGIN_URL.'/assets/images/ksd-severity-indicators.png',
                    __( 'The Red and Orange borders show tickets with Urgent & High severity respectively' ,'kanzu-support-desk')
                            ),
                    'position' => array( 'edge' => 'left', 'align' => 'top' )                
                )
            );
            $p[] = array(
                'target' => '#ksd-new-ticket',
                'tab'  => 2,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p><p> %s </p></span>',
                    __( 'New Ticket' ,'kanzu-support-desk'),
                    __( 'You or your agent(s) can log new tickets here. If "Send Email" is checked, an email is sent to your customer','kanzu-support-desk'),
                    __( 'New tickets can also be logged by your customers from a form at the front-end of your site.' ,'kanzu-support-desk')
                    ),
                     'position' => array( 'edge' => 'right', 'align' => 'top','nudgehorizontal' => 1 )             
                )
            );
            $p[] = array(
                'target' => '.ksd-reset',
                'tab'  => 3,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p></span>',
                    __( 'Settings' ,'kanzu-support-desk'),
                    __( 'Modify your settings','kanzu-support-desk')
                    ),
                     'position' => array( 'edge' => 'bottom', 'align' => 'bottom' )        
                )
            );
            $p[] = array(
                'target' => '.add-ons',
                'tab'  => 4,  
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p></span>',
                    __( 'Add-ons' ,'kanzu-support-desk'),
                    __( 'Activate an add-on to allow your customers to log tickets using other channels such as email','kanzu-support-desk')
                    ),
                    'position' => array( 'edge' => 'bottom', 'align' => 'left' )     
                )                 
            );
            $p[] = array(
                'target' => '#help',
                'tab'  => 5,
                'options' => array(
                    'content' => sprintf( '<span><h3> %s </h3> <p> %s </p><p> %s </p></span>',
                    __( 'Help' ,'kanzu-support-desk'),
                    __( 'Resource center to help you make the most of your Kanzu Support Desk experience','kanzu-support-desk'),                   
                    __( "That's it! Dive right in. To take this tour again, click 'Enable Tour Mode' in your settings tab, update your settings then refresh your page", "kanzu-support-desk")
                    ),
                    'position' => array( 'edge' => 'left', 'align' => 'top','nudgehorizontal' => 1 )
                )
            );
            return $p;
         }
         
         /**
          * Mark a ticket as read or unread
          * @param int $ticket_ID The ticket ID
          * @param boolean $mark_as_read Whether to mark the ticket as read or not
          */
         private function do_change_read_status( $ticket_ID, $mark_as_read = true ) {
            $ticket_read_status = ( $mark_as_read ? 1 : 0 );
            $updated_ticket                  = new stdClass();
            $updated_ticket->tkt_id          = $ticket_ID;
            $updated_ticket->new_tkt_is_read = $ticket_read_status;
            $TC = new KSD_Tickets_Controller();	                
            return $TC->update_ticket( $updated_ticket );
        }
        
        
        /**
         * Change ticket's read status
         * @throws Exception
         */
        public function change_read_status(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                    die ( __('Busted!','kanzu-support-desk') );
            }
            
            try{
                $this->do_admin_includes();	    
                if( $this->do_change_read_status( $_POST['tkt_id'], $_POST['tkt_is_read'] ) ){
                    echo json_encode( __( 'Ticket updated', 'kanzu-support-desk'));
                }else {
                    throw new Exception( __( 'Update Failed. Please retry', 'kanzu-support-desk') , -1);
                }
		die();// IMPORTANT: don't leave this out
            }catch( Exception $e){ 
                $response = array( 
                    'error'=> array( 'message' => $e->getMessage() , 'code'=> $e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
	}


        /**
          * Create a new customer in wp_users
          * @param Object $customer The customer object
          */
         private function create_new_customer( $customer ){
                    $username = sanitize_user( preg_replace('/@(.)+/','',$customer->user_email ) );//Derive a username from the emailID
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
                        'user_email'    => $customer->user_email,
                        'display_name'  => empty( $customer->last_name ) ? $customer->first_name : $customer->first_name.' '.$customer->last_name,
                        'first_name'    => $customer->first_name,
                        'role'          => 'ksd_customer'
                    );
                    if( !empty( $customer->last_name )){//Add the username if it was provided
                        $userdata['last_name']  =   $customer->last_name;
                    }
                    $user_id = wp_insert_user( $userdata ) ;
                    if( !is_wp_error($user_id) ) {
                        return $user_id;
                    }
                    return false;
            }
         
         /**
          * Disable tour mode
          * @since 1.1.0
          */
         public function disable_tour_mode(){
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            $ksd_settings['tour_mode'] = "no";
            Kanzu_Support_Desk::update_settings( $ksd_settings );
            echo json_encode( 1 );
            die();            
         }
         
         /**
          * Enable usage statistics
          * @since 1.6.7
          */
         public function enable_usage_stats(){
            $ksd_settings = Kanzu_Support_Desk::get_settings();
            $ksd_settings['enable_anonymous_tracking'] = "yes";
            Kanzu_Support_Desk::update_settings($ksd_settings);
            echo json_encode( __('Successfully enabled. Thank you!','kanzu-support-desk') );
            die();
        }
        /**
         * AJAX callback to send notification of a new ticket
         */
        public function notify_new_ticket(){
            // if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
           //       die ( __('Busted!','kanzu-support-desk') );                         
           // } //@TODO Update this NONCE check                   
              $this->do_notify_new_ticket();                    
              echo json_encode( __('Notification sent.','kanzu-support-desk') );
              die();//IMPORTANT. Shouldn't be left out         
        }
         
         /**
          * Notify the primary administrator that a new ticket has been logged  
          * The wp_mail call in send_mail takes a while (about 5s in our tests)
          * so for tickets logged in the admin side, we call this using AJAX     
          * @param string $notify_email Email to notify
          * @param int $tkt_id
          * @param string $customer_email The email of the customer for whom the new ticket has been created
          * @param string $ticket_subject The new ticket's subject   
          * @param Array $attachments Filenames to attach to the notification
          * @since 1.5.5
          */
         private function do_notify_new_ticket( $notify_email, $tkt_id, $customer_email = null, $ticket_subject = null, $ticket_message = null, $attachments = array() ){                    
            $ksd_settings = Kanzu_Support_Desk::get_settings(); 
            //If new ticket notifications have been set, inform the primary administrator that a new ticket has been logged          
            if ( "yes" == $ksd_settings['enable_notify_on_new_ticket'] ){                   
                // The blogname option is escaped with esc_html on the way into the database in sanitize_option
                // we want to reverse this for the plain text arena of emails.
                $blog_name = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
                $notify_new_tkt_message  = sprintf(__('New customer support ticket on your site %s:','kanzu-support-desk'), $blog_name) . "\r\n\r\n";
                if( !is_null( $customer_email ) ){
                    $notify_new_tkt_message .= sprintf(__('Customer E-mail: %s','kanzu-support-desk'), $customer_email ) . "\r\n\r\n";   
                }
                if( !is_null( $ticket_subject ) ){
                    $notify_new_tkt_message .= sprintf(__('Ticket Subject: %s','kanzu-support-desk'), $ticket_subject ) . "\r\n\r\n";   
                }
                if( !is_null( $ticket_message ) ){
                    $notify_new_tkt_message .= sprintf(__('Ticket Message: %s','kanzu-support-desk'), $ticket_message ) . "\r\n\r\n";   
                }
                $notify_new_tkt_message .= Kanzu_Support_Desk::output_ksd_signature( $tkt_id );
                $notify_new_tkt_subject = sprintf( __('[%s] New Support Ticket'), $blog_name );
                
                //Use two filters, ksd_new_ticket_notifxn_message and ksd_new_ticket_notifxn_subject, to make changes to the
                //the notification message and subject by add-ons
                $this->send_email( $notify_email, apply_filters( 'ksd_new_ticket_notifxn_message', $notify_new_tkt_message, $ticket_message , $ksd_settings ), apply_filters( 'ksd_new_ticket_notifxn_subject', $notify_new_tkt_subject, $ticket_subject , $ksd_settings ), null, $attachments );                  
                } 
         
         }
         
         /**
          * Return the HTML for a feedback form
          * @param string $position The position of the form
          * @param string $send_button_text Submit button text
          */
         public static function output_feeback_form( $position, $send_button_text='Send' ){
            $form = '<form action="#" class="ksd-feedback-'.$position.'" method="POST">';        
            $form.= '<p><textarea name="ksd_user_feedback" rows="5" cols="100"></textarea></p>';
            $form.= '<input name="action" type="hidden" value="ksd_send_feedback" />';
            $form.= '<input name="feedback_type" type="hidden" value="'.$position.'" />';
            $form.= wp_nonce_field( 'ksd-send-feedback', 'feedback-nonce' ); 
            $form.= '<p><input type="submit" class="button-primary" name="ksd-feedback-submit" value="'.$send_button_text.'"/></p>';
            $form.= '</form>';
            return $form;
         }         

         
         /**
          * Send the KSD team feedback
          * @since 1.1.0
          */
         public function send_feedback(){
             if ( isset( $_POST['feedback_type'] ) ) {//If it a request to be added to our waiting list, add them
                switch( $_POST['feedback_type'] ):
                    case 'waiting_list':
                        $current_user = wp_get_current_user();
                        $addon_message = sanitize_text_field( $_POST['ksd_user_feedback'] ) . ',' . $current_user->user_email;
                        $response = ( $this->send_email("feedback@kanzucode.com", $addon_message, "KSD Add-on Waiting List") ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
                        break;
                    default:
                        $feedback_message = sanitize_text_field( $_POST['ksd_user_feedback'] ) . ',' . $_POST['feedback_type'];
                        $response = ( $this->send_email("feedback@kanzucode.com", $feedback_message, "KSD Feedback") ? __('Sent successfully. Thank you!', 'kanzu-support-desk') : __('Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
                endswitch;
 
                echo json_encode($response);
                die(); // IMPORTANT: don't leave this out
            }
            
            if ( ! wp_verify_nonce( $_POST['feedback-nonce'], 'ksd-send-feedback' ) ){
                die ( __('Busted!','kanzu-support-desk') );
               }
             if (strlen( $_POST['ksd_user_feedback'] )<= 2 ){
                $response = __( "Error | The feedback field's empty. Please type something then send", "kanzu-support-desk"); 
             }  
             else{
                $response =  ( $this->send_email( "feedback@kanzucode.com", sanitize_text_field( $_POST['ksd_user_feedback'] ),"KSD Feedback" ) ? __( 'Sent successfully. Thank you!', 'kanzu-support-desk' ) : __( 'Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com', 'kanzu-support-desk') );
             }
             echo json_encode( $response );	
             die();// IMPORTANT: don't leave this out
         }
         
         /**
          * Send mail. 
          * @param string $to Recipient email address
          * @param string $message The message to send. Can be "new_ticket"
          * @param string $subject The message subject
          * @param string $cc  A comma-separated list of email addresses to cc   
          * @param Array $attachments Array of attachment filenames
          */
         public function send_email( $to, $message="new_ticket", $subject=null, $cc=null,  $attachments= array() ){
             $settings = Kanzu_Support_Desk::get_settings();             
             switch ( $message ):
                 case 'new_ticket'://For new ticket auto-replies
                     $subject   = $settings['ticket_mail_subject'];
                     $message   = $settings['ticket_mail_message'];                     
             endswitch;
                     $headers[] = 'From: '.$settings['ticket_mail_from_name'].' <'.$settings['ticket_mail_from_email'].'>';
                     $headers[] = 'Content-Type: text/html; charset=UTF-8'; //@since 1.6.4 Support HTML emails
                     if( !is_null( $cc ) ){
                         $headers[] = "Cc: $cc";
                     }
             return wp_mail( $to, $subject, $this->format_message_content_for_viewing( $message ), $headers, $attachments ); 
         }
         
         /**
          * Retrieve Kanzu Support Desk notifications. These are currently
          * retrieved from the KSD blog feed, http://blog.kanzucode.com/feed/
          * @since 1.3.2
          */
         public function get_notifications(){
            ob_start();  
            if ( false === ( $cache = get_transient( 'ksd_notifications_feed' ) ) ) {
		$feed = wp_remote_get( 'http://kanzucode.com/work/blog/feed/', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {                   
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'ksd_notifications_feed', $cache, 86400 );//Check everyday
			}
		} else {
                    $cache["error"] =  __( 'Sorry, an error occurred while retrieving the latest notifications. A re-attempt will be made later. Thank you.', 'kanzu-support-desk');
		}
            }
            echo json_encode( $cache );
            echo ob_get_clean();    
            die();
         }
         
         /**
          * Convert the multiple checkbox input, which is an array in $_POST, into a setting,
          * which is a string of the values separated by |. We save them this way since we use
          * them in an SQL REGEXP which uses them as is 
          * e.g. SELECT field1,field2 from table where REGEXP 'value1|value2|value3'
          * @param Array $multiple_checbox_array An array of the checked checkboxes in a set of multiple checkboxes
          * @return string A |-separated list of the checked values
          * @since 1.5.0
          */
         private function convert_multiple_checkbox_to_setting( $multiple_checbox_array ){
             $setting_string = "administrator";//By default, the administrator has access
             foreach ( $multiple_checbox_array as $checkbox ){
                 $setting_string.="|".$checkbox;                
             }       
             return $setting_string;
         }
         
         /**
         * Append plugin to active plugin list
         * @since    1.1.1
         * 
         */
        public static function append_to_activelist ( $active_addons ){
            $active_addons['ksd-mail'] =  'ksd-mail/ksd-mail.php'; 
            return $active_addons;
        }
        
        
        /**
         * Add KSD tickets import to the wordpress tools toolbox
         * @since   1.5.2
         */
        public  function add_importer_to_toolbox () { 
            echo '
                <div class="tool-box">
                    <h3 class="title"> ' . __('KSD Importer') . '</h3>
                     <p>
                     Import tickets into Kanzu Support Desk. Use the  <a href="?import=ksdimporter">KSD Importer </a>
                     </p>
                </div>
            ';
        }
       
        /**
         * Hand this over to the function in the Import class
         * @param int $imported_ticket_id
         * @param int $logged_ticket_id
         */
        public function new_ticket_imported( $imported_ticket_id, $logged_ticket_id ){
            $importer = new KSD_Importer () ;   
            $importer->new_ticket_imported( $imported_ticket_id, $logged_ticket_id );
        }
        
        /**
         * Initialize the KSD importer; it enables users to import
         * tickets into KSD
         * ksd_importer_init
         * @since   1.5.4
         */
        public function ksd_importer_init () {
                
            $id             = 'ksdimporter';
            $name           = __( 'KSD Importer', 'kanzu-support-desk' );
            $description    = __( 'Import support tickets into the Kanzu Support Desk plugin.', 'kanzu-support-desk' );
            
            include_once( KSD_PLUGIN_DIR.  "includes/libraries/class-ksd-importer.php" );  
            $importer = new KSD_Importer ( ) ;
            $callback    = array( $importer, 'dispatch' );
            register_importer ( $id, $name, $description, $callback ) ; 
        }
        
        /**
         * Handle an AJAX request to change the license's status. We use this to activate
         * and deactivate licenses
         */
        public function modify_license_status(){
        if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );                         
          }
          
          $response = $this->do_license_modifications( $_POST['license_action'],$_POST['plugin_name'],$_POST['plugin_author_uri'],$_POST['plugin_options_key'],$_POST['license_key'],$_POST['license_status_key'],sanitize_text_field( $_POST['license'] ) );
          echo json_encode( $response );          
          die();//Important. Don't leave this out
        }
        
        /**
         * For KSD plugins, make a remote call to Kanzu Code to activate/Deactivate/check license status
         * @param string $action The action to perform on the license. Can be 'activate_license','deactivate_license' or 'check_license'
         * @param string $plugin_name The plugin name
         * @param string $plugin_author_uri Plugin author's URI
         * @param string $plugin_options_key The plugin options key used to store its options in the KSD options array
         * @param string $license_key The key used to store the license
         * @param string $license_status_key The key used to store the license status
         * @param string $license The license to check
         * @return string $response Returns a response message 
         */
        public function do_license_modifications( $action, $plugin_name, $plugin_author_uri, $plugin_options_key, $license_key, $license_status_key, $license = NULL ) {
                $response_message = __( 'Sorry, an error occurred. Please retry or reload the page','kanzu-support-desk' );
           
                 /*Retrieve the license from the database*/
                //First get overall settings
                $base_settings = get_option( KSD_OPTIONS_KEY );
                //Check that the key exists
                $plugin_settings = ( isset ( $base_settings[ $plugin_options_key ] ) ? $base_settings[ $plugin_options_key ] : array() );
                
                if( is_null( $license ) ){
                    $license    =   trim( $plugin_settings[ $license_key ] );    	
                }   

		// data to send in our API request
		$api_params = array( 
			'edd_action'     => $action, 
			'license'                       => $license, 
			'item_name'                     => urlencode( $plugin_name ), // the name of our product in EDD
			'url'                           => home_url()
		);
               $response = wp_remote_post( $plugin_author_uri, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ){                    
			return $response_message;
                }
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
                
                switch( $action ){
                    case 'activate_license':
                    case 'check_license':
                        if( $license_data->license == 'valid' ) {
                            $plugin_settings[ $license_status_key ] = 'valid';
                            $response_message = __('License successfully validated. Welcome to a super-charged Kanzu Support Desk!','kanzu-support-desk' );
                           }
                        else{//Invalid license
                            $plugin_settings[ $license_status_key ] = 'invalid';
                            $response_message = __( 'Invalid License. Please renew your license','kanzu-support-desk' );             
                        }
                        break;
                    case 'deactivate_license':
                        if( $license_data->license == 'deactivated' ) {
                            $plugin_settings[ $license_status_key ] = 'invalid';
                            $response_message = __( 'Your license has been deactivated successfully. Thank you.','kanzu-support-desk' );                            
                        }
                        break;
                }
                //Retrieve the license for saving
                $plugin_settings[ $license_key ] = $license;
                
                //Update the Db
                $base_settings[ $plugin_options_key ] = $plugin_settings;
                update_option( KSD_OPTIONS_KEY, $base_settings );         
                
                return $response_message;	 
        } 
        
        /**
         * Generates a debug file for download 
         *
         * @since       1.7.0
         * @return      void
         */
        public function generate_debug_file() {
            nocache_headers();

            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="ksd-debug.txt"');
            require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-debug.php' );
            
            $ksd_debug =  KSD_Debug::get_instance();
            
            echo wp_strip_all_tags( $ksd_debug->retrieve_debug_info() );
            die();
        }

        /**
         * Add cc button
         * @since 1.6.8
         */
        private function add_tinymce_cc_button(){
            add_filter("mce_external_plugins", array ( $this, "add_tinymce_cc_plugin" ) );
            add_filter('mce_buttons', array ( $this, 'register_tinymce_cc_button' ), 10, 2 );
        }
        
        /**
         * Register the CC tinymce button
         * @param array $plugin_array
         * @return string
         */
        public function add_tinymce_cc_plugin( $plugin_array ) {
            $plugin_array['KSDCC'] = KSD_PLUGIN_URL. '/assets/js/ksd-wp-editor-cc.js';                    
            return $plugin_array;
        }
        
		/**
         * Register the CC button
         * @param type $buttons
         * @return type
         */
        public function register_tinymce_cc_button( $buttons,  $editor_id ) {
            if ( strpos ( $editor_id , 'ksd_' ) !== false ){//Add the CC button only if it is a KSD editor (not a post, page, etc editor)
                array_push( $buttons, 'ksd_cc_button' );  
            }
            return $buttons;
        }
       
        /**
         * Modify the tickets grid and add ticket-specific headers
         * @param array $defaults The grid headers
         * @return array
         * @since 2.0.0
         */
        public function add_tickets_headers( $defaults ){
            $defaults['status']         = __( 'Status', 'kanzu-support-desk' );
            $defaults['assigned_to']    = __( 'Assigned To', 'kanzu-support-desk' );
            $defaults['severity']       = __( 'Severity', 'kanzu-support-desk' );
            $defaults['customer']       = __( 'Customer', 'kanzu-support-desk' );
            $defaults['replies']        = __( 'Replies', 'kanzu-support-desk' );
            return $defaults;
        }
        
        /**
         * Define which ticket columns to add sorting to
         * @return string
         * @since 2.0.0
         */
        public function ticket_table_sortable_columns( $columns ){
            $columns['status']      = 'status';
            $columns['assigned_to'] = 'assigned_to';
            $columns['severity']    = 'severity';
            $columns['customer']    = 'customer';
            return $columns;
        }
        
        /**
         * Remove some default columns. In particular, we remove the tags column
         * @since 2.0.0
         */
        public function ticket_table_remove_columns( $columns ){           
            unset($columns['tags']); //Remove tags
            return $columns;
        }
        
        /**
         * Order the ticket table columns by a particular field
         * @since 2.0.0
         */
        public function ticket_table_columns_orderby( $vars ){
            if ( isset( $vars['orderby'] ) ) {
                switch( $vars['orderby'] ){
                    case 'severity':
                        $vars = array_merge( $vars, array(
                            'meta_key' => '_ksd_tkt_info_severity',
                            'orderby' => 'meta_value'
                            ) );
                        break; 
                    case 'assigned_to':
                        $vars = array_merge( $vars, array(
                            'meta_key' => '_ksd_tkt_info_assigned_to',
                            'orderby' => 'meta_value_num'
                            ) );
                        break; 
                    case 'status':
                        $vars = array_merge( $vars, array(
                            'orderby' => 'post_status'
                            ) );
                        break; 
                    case 'customer':
                        $vars = array_merge( $vars, array(
                            'orderby' => 'post_author'
                            ) );
                        break; 
                }   
            }
            return $vars;    
        }
        
        /**
         * Add filters to the WP ticket grid
         * @since 2.0.0
         */
        public function ticket_table_filter_headers(){
            global $wpdb, $current_screen;
            if ( $current_screen->post_type == 'ksd_ticket' ) {
                $ksd_statuses = array ( 
                    'new'       => __( 'New', 'kanzu-support-desk' ),
                    'open'      => __( 'Open', 'kanzu-support-desk' ), 
                    'pending'   => __( 'Pending', 'kanzu-support-desk' ), 
                    'resolved'  => __( 'Resolved', 'kanzu-support-desk' ) 
                    );
                $filter  = '';
                $filter .= '<select name="ksd_statuses_filter" id="filter-by-status">';
        	$filter .= '<option value="0">' . __( 'All statuses', 'kanzu-support-desk' ) . '</option>';
                $filter_status_by = ( isset ( $_GET['ksd_statuses_filter'] ) ? sanitize_key( $_GET['ksd_statuses_filter'] ) : 0 );    
                foreach( $ksd_statuses as $value => $name ) {
                    $filter .= '<option ' .selected( $filter_status_by , $value , false ) . ' value="'.$value.'">' . $name . '</option>';       
                }
                $filter .= '</select>';  
                $ksd_severities = $this->get_severity_list();
                $filter .= '<select name="ksd_severities_filter" id="filter-by-severity">';
        	$filter .= '<option value="0">' . __( 'All severities', 'kanzu-support-desk' ) . '</option>';
                $filter_severity_by = ( isset ( $_GET['ksd_severities_filter'] ) ? sanitize_key( $_GET['ksd_severities_filter'] ) : 0 );    
                foreach( $ksd_severities as $value => $name ) {
                    $filter .= '<option ' .selected( $filter_severity_by , $value, false ) . ' value="'.$value.'">' . $name . '</option>';       
                }
                $filter .= '</select>';
                echo $filter;
             }            
        }
        
        /**
         * Apply filters to the ticket grid
         * Called when a view is selected in the ticket grid and when the user filters a view
         * @since 2.0.0
         */
        public function ticket_table_apply_filters( $query ){
            if( is_admin() && $query->query['post_type'] == 'ksd_ticket' ) {
                
                 $qv = &$query->query_vars;
                 $qv['meta_query'] = array();
                 
                if( ! empty( $_GET['ksd_severities_filter'] ) ) {
                    $qv['meta_query'][] = array(
                      'key' => '_ksd_tkt_info_severity',
                      'value' => $_GET['ksd_severities_filter'],
                      'compare' => '=',
                      'type' => 'CHAR'
                    );
                }
                if( ! empty( $_GET['ksd_statuses_filter'] ) ) {
                    $qv['post_status'] =  sanitize_key( $_GET['ksd_statuses_filter'] );
                }
                if( ! empty( $_GET['ksd_view'] ) ) {  
                   switch( sanitize_key( $_GET['ksd_view'] ) ){
                       case 'mine':      
                            $qv['meta_query'][] = array(
                                'key'           => '_ksd_tkt_info_assigned_to',
                                'value'         => get_current_user_id(),
                                'compare'       => '=',
                                'type'          => 'NUMERIC'
                              );
                            $qv['post_status'] = array( 'new', 'open', 'draft', 'pending' );//Don't show resolved tickets
                           break;
                       case 'unassigned':
                            $qv['meta_query'][] = array(
                                'key'           => '_ksd_tkt_info_assigned_to',
                                'value'         => 0,
                                'compare'       => '=',
                                'type'          => 'NUMERIC'
                              );
                           $qv['post_status'] = array( 'new', 'open', 'draft', 'pending' );//Don't show resolved tickets
                           break;
                       case 'recently_updated':
                           break;
                       case 'recently_resolved':
                           break;
                   }
               }
            }            
        }
        
        /**
         * Populate our custom ticket columns
         * @param string $column_name
         * @param int $post_id
         * @since 2.0.0
         */
        public function populate_ticket_columns( $column_name, $post_id ){
            if ( $column_name == 'severity' ) {
                $ticket_severity = get_post_meta( $post_id, '_ksd_tkt_info_severity', true );
                echo  '' == $ticket_severity ? 'low' : $ticket_severity ;
            }
            if ( $column_name == 'assigned_to' ) {
                $ticket_assignee = get_post_meta( $post_id, '_ksd_tkt_info_assigned_to', true );                
                echo  '' == $ticket_assignee || 0 == $ticket_assignee ? __( 'No one', 'kanzu-support-desk' ) : get_userdata( $ticket_assignee )->display_name;
            }   
            if ( $column_name == 'status' ) {
                global $post;
                echo   "<span class='{$post->post_status}'>{$post->post_status}</span>";
            }   
            if ( $column_name == 'customer' ) {
                global $post;
                echo   get_userdata( $post->post_author )->display_name;
            } 
            if ( $column_name == 'replies' ) {
                global $wpdb;
                $reply_count 
                        = $wpdb->get_var( " SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE "
                        . " post_type = 'ksd_reply' AND post_parent = '${post_id}' " 
                        );
                echo   $reply_count;
            } 
        }
        
        /**
         * Add custom views to the admin post grid
         * @param Array $views The default admin post grid views
         * @since 2.0.0
         */
        public function ticket_views( $views ){
            unset( $views['publish'] ); //Remove the publish view          
            $views['mine']              = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=mine'>".__( 'Mine', 'kanzu-support-desk' )."</a>";
            $views['unassigned']        = "<a href='edit.php?post_type=ksd_ticket&amp;ksd_view=unassigned'>".__( 'Unassigned', 'kanzu-support-desk' )."</a>";
            return $views;
        }
        
        /**
         * Function name is very descriptive; display ticket
         * states next to the title in the ticket grid
         * We use this to remove 'draft' and 'pending' ticket states
         * that are automatically added to tickets by WP
         * @global Object $post
         * @param type $states
         * @return type
         * @since 2.0.0
         */
        public function display_ticket_statuses_next_to_title( $states ) {
            global $post;
            if( $post->post_status == 'pending' || $post->post_status == 'draft' ){
                return array ( );
            }
            return $states;
        }       


        /**
         * Migrate tickets and replies to version 2.0+ 
         * @since 2.0.0
         * 
         */
        public function migrate_to_v2(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                  die ( __('Busted!','kanzu-support-desk') );                         
            }
            ini_set("max_execution_time",0);
            
            include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-tickets-controller.php");
            include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-replies-controller.php");
            include_once( KSD_PLUGIN_DIR.  "includes/models/class-ksd-assignments-model.php");
            
            //Tickets
            $tObj = new KSD_Tickets_Controller();
 
            $ksd_v2migration_status  = get_option( 'ksd_v2migration_status' );
            $prev_tkt_id  = (int)$ksd_v2migration_status;
            
            $tickets = $tObj->get_tickets(" tkt_id > %d", array( $prev_tkt_id ));

            foreach( $tickets as $t ){
                $post_arr = array(
                    'post_title'    => $t->tkt_subject,
                    'post_content'  => $t->tkt_message,
                    'post_status'   => strtolower( $t->tkt_status ),
                    'post_author'   => $t->tkt_updated_by,
                    'post_type'     => 'ksd_ticket',
                    'post_date'     => date( 'Y-m-d H:i:s', strtotime($t->tkt_time_logged) )
                );
                $post_id = wp_insert_post( $post_arr );

                //@TODO: Check if post was created before preceeding
                if( 0 == $post_id ){ 
                    $option    = 'ksd_v2migration_status';
                    $new_value =   $t->tkt_id - 1; //Last updated ticket
                    update_option( $option, $new_value );   
                    _e( 'Error occured. Migration did not completed.', 'kanzu-support-desk');
                    die();
                }
                
                //Add assignment,severity, and channel
                add_post_meta( $post_id, 
                        '_ksd_tkt_info_assigned_to',  
                        $t->tkt_assigned_to,
                        true); 
                
                add_post_meta( $post_id, 
                        '_ksd_tkt_info_severity',   
                        strtolower( $t->tkt_severity ), 
                        true); 
                
                add_post_meta( $post_id, 
                        '_ksd_tkt_info_channel',   
                        strtolower( $t->tkt_channel ),  //@TODO: Translate channels where names changed eg admin-form
                        true); 
                
                //Add private notes
                if( "" !== $t->tkt_private_note ){
                    $priv_arr = array(
                        'post_title'    => $t->tkt_subject,
                        'post_content'  => $t->tkt_private_note,
                        'post_status'   => 'private',
                        'post_author'   => $t->tkt_updated_by,
                        'post_type'     => 'ksd_private_note',
                        'post_date'     => date( 'Y-m-d H:i:s', strtotime( $t->tkt_time_logged ) ),
                        'comment_status'=> 'closed',
                        'post_parent'   => $post_id
                    );
                    
                    wp_insert_post( $priv_arr );
                }
                
                //Update assignments / ticket activity
                $assModel  = new KSD_Assignments_Model();
                $assignments = $assModel->get_all(" assign_tkt_id = '%d' ORDER BY assign_date_assigned ASC", array( $t->tkt_id ));
                $prev_assignment = 0;
                
                foreach( $assignments as $a ){
                    
                    $old_value = $prev_assignment;
                    $new_value = $a->assign_assigned_to;
                    $old_value_name = ( 0 == $old_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$old_value}").'">'.get_userdata( $old_value )->display_name.'</a>' );
                    $new_value_name = ( 0 == $new_value ? __( 'No One', 'kanzu-support-desk' ) : '<a href="'.admin_url( "user-edit.php?user_id={$new_value}").'">'.get_userdata( $new_value )->display_name.'</a>' );
                    $activity_content = sprintf( __( 're-assigned ticket from %1$s to %2$s','kanzu-support-desk' ), $old_value_name, $new_value_name );   
                    
                    $ass_arr = array(
                    'post_title'    => $t->tkt_subject,
                    'post_content'  => $activity_content ,
                    'post_status'   => 'private',
                    'post_author'   => $a->assign_assigned_by,
                    'post_type'     => 'ksd_ticket_activity',
                    'post_date'     => date( 'Y-m-d H:i:s', strtotime($a->assign_date_assigned) ),
                    'comment_status'=> 'closed',
                    'post_parent'   => $post_id
                    );
                    $prev_assignment = $a->assign_assigned_to;
                    
                    wp_insert_post( $ass_arr );
                }
                        
                //Add replies
                $repObj  = new KSD_Replies_Controller();
                $replies = $repObj->get_replies("rep_tkt_id = '%d'", array($t->tkt_id ));
                
                foreach( $replies as $r ){
                    $rep_arr = array(
                    'post_title'    => $t->tkt_subject,
                    'post_content'  => $r->rep_message,
                    'post_status'   => 'publish',
                    'post_author'   => $r->rep_created_by,
                    'post_type'     => 'ksd_reply',
                    'post_date'     => date( 'Y-m-d H:i:s', strtotime($r->rep_date_created) ),
                    'comment_status'=> 'closed',
                    'post_parent'   => $post_id
                    );
                    
                    $rep_id = wp_insert_post( $rep_arr );
                }
            }//eof:tickets
            
            
            
            //Update migration stage
            $option    = 'ksd_v2migration_status';
            $newvalue = 'ksd_v2migration_deletetables'; 
            update_option( $option, $newvalue );    
            $count = count($tickets);
            printf( _n( 'Migration successful. %d ticket has been migrated. Welcome to the next level of amazing customer support!.',
                        'Migration successful. %d tickets have been migrated. Welcome to the next level of amazing customer support!.',
                         $count, 'kanzu-support-desk' ), $count );
             
            die();
        }
        
        /**
         * Delete tables used in KSD prior to version 2.0
         * @since 2.0.0
         */
        public function deletetables_v2(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                  die ( __('Busted!','kanzu-support-desk') );                         
            }
            global $wpdb;
            ini_set("max_execution_time",0);
            
            foreach( 
                array(
                "{$wpdb->prefix}kanzusupport_replies",
                "{$wpdb->prefix}kanzusupport_assignments",
                "{$wpdb->prefix}kanzusupport_attachments",
                "{$wpdb->prefix}kanzusupport_tickets",
                ) as $table 
            ){
                $sql = "DROP TABLE IF EXISTS $table";
                $wpdb->query($sql);
            }

                        //Update migration stage
            $option    = 'ksd_v2migration_status';
            $new_value = 'ksd_v2migration_done'; 
            update_option( $option, $new_value );   
            
            _e('Tables successfully deleted.', 'kanzu-support-desk');
            die();
            
        }
}   
        
endif;

return new KSD_Admin();
