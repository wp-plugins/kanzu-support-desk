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
                add_action( 'wp_ajax_ksd_get_ticket_replies', array( $this, 'get_ticket_replies' ));   
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' )); 
                add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ));  
                add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' )); 
                add_action( 'wp_ajax_ksd_reset_settings', array( $this, 'reset_settings' )); 
                add_action( 'wp_ajax_ksd_update_private_note', array( $this, 'update_private_note' ));  
                add_action( 'wp_ajax_ksd_send_feedback', array( $this, 'send_feedback' ));  
                add_action( 'wp_ajax_ksd_disable_tour_mode', array( $this, 'disable_tour_mode' ));              
                add_action( 'wp_ajax_ksd_get_notifications', array( $this, 'get_notifications' ));  
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
                $ksd_admin_tab = ( isset( $_GET['page'] ) ? $_GET['page'] : "" );//This determines which tab to show as active
               
                
                //Get intro tour messages if we are in tour mode @since 1.1.0
                $tour_pointer_messages['ksd_intro_tour'] =  $this->load_intro_tour();
                
                //This array allows us to internalize (translate) the words/phrases/labels displayed in the JS 
                $admin_labels_array = array();
                $admin_labels_array['dashboard_chart_title']        = __('Incoming Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_open_tickets']       = __('Total Open Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_unassigned_tickets'] = __('Unassigned Tickets','kanzu-support-desk');
                $admin_labels_array['dashboard_avg_response_time']  = __('Avg. Response Time','kanzu-support-desk');
                $admin_labels_array['tkt_trash']                    = __('Trash','kanzu-support-desk');
                $admin_labels_array['tkt_assign_to']                = __('Assign To','kanzu-support-desk');
                $admin_labels_array['tkt_change_status']            = __('Change Status','kanzu-support-desk');
                $admin_labels_array['tkt_subject']                  = __('Subject','kanzu-support-desk');
                $admin_labels_array['tkt_cust_fullname']            = __('Customer Name','kanzu-support-desk');
                $admin_labels_array['tkt_cust_email']               = __('Customer Email','kanzu-support-desk');
                $admin_labels_array['tkt_reply']                    = __('Reply','kanzu-support-desk');
                $admin_labels_array['tkt_forward']                  = __('Forward','kanzu-support-desk');
                $admin_labels_array['tkt_update_note']              = __('Update Note','kanzu-support-desk');
                $admin_labels_array['msg_still_loading']            = __('Loading Replies...','kanzu-support-desk');
                $admin_labels_array['msg_loading']                  = __('Loading...','kanzu-support-desk');
                $admin_labels_array['msg_sending']                  = __('Sending...','kanzu-support-desk');
                $admin_labels_array['msg_error']                    = __('Sorry, an unexpected error occurred. Kindly retry. Thank you.','kanzu-support-desk');
                $admin_labels_array['pointer_next']                 = __('Next','kanzu-support-desk');
                $admin_labels_array['lbl_toggle_trimmed_content']   = __('Toggle Trimmed Content','kanzu-support-desk');
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
                                            'ksd_version'               =>  KSD_VERSION
                                        )
                                    );

	}
                            
        /**
         * Get a list of agents
         * @param string $roles The WP Roles with access to KSD
         * @return An unordered list of agents
         */
        public static function get_agent_list( $roles ){
            include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-users-controller.php");//@since 1.5.0 filter the list to return users in certain roles
            $UC = new KSD_Users_Controller();
            $tmp_user_IDs = $UC->get_users_with_roles( $roles );
            foreach ( $tmp_user_IDs as $userID ){
                $user_IDs[] = $userID->user_id;
            }            
            $agents_list = "<ul class='ksd_agent_list hidden'>";//The available list of agents
                foreach (  get_users( array( 'include' => $user_IDs ) ) as $agent ) {
                    $agents_list .= "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>";
                }
             $agents_list .= "</ul>";
             return $agents_list;
        }

	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=ksd-settings' ) . '">' . __( 'Settings', KSD_SLUG ) . '</a>'
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
		$menu_title = __('Kanzu Support Desk','kanzu-support-desk');
		$capability = 'manage_options';
		$menu_slug = KSD_SLUG;
		$function = 'output_admin_menu_dashboard';
		
                /*
		* Add the settings page to the Settings menu.
                */
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,$function),'dashicons-groups',40);
		
		//Add the ticket pages. 
		$ticket_types = array();
		$ticket_types[ 'ksd-dashboard' ]  =   __( 'Dashboard','kanzu-support-desk' );
		$ticket_types[ 'ksd-tickets' ]    =   __( 'Tickets','kanzu-support-desk' );
                $ticket_types[ 'ksd-new-ticket' ] =   __( 'New Ticket','kanzu-support-desk' );
		$ticket_types[ 'ksd-settings' ]   =   __( 'Settings','kanzu-support-desk' );
		$ticket_types[ 'ksd-addons' ]     =   __( 'Add-ons','kanzu-support-desk' );
		$ticket_types[ 'ksd-help' ]       =   __( 'Help','kanzu-support-desk' );               
		
		foreach ( $ticket_types as $submenu_slug => $submenu_title ) {
                    
                    add_submenu_page($menu_slug, $page_title, $submenu_title, $capability, $submenu_slug, array($this,$function));                        		
      
                }
                
	}
                        
	
	/**
	 * Display the main Kanzu Support Desk admin dashboard
	 */
	public function output_admin_menu_dashboard(){
		$this->do_admin_includes();               
                include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-wrapper.php');                
	}
        
 
	/**
	 * Include the files we use in the admin dashboard
	 */
        public function do_admin_includes() {		
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-tickets-controller.php");
		include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-users-controller.php");
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-assignments-controller.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-replies-controller.php");  
                include_once( KSD_PLUGIN_DIR.  "includes/controllers/class-ksd-customers-controller.php");  
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
	 * Filter tickets in the 'tickets' view 
	 */
	public function filter_tickets() {		 
	  if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );                         
          }
          
                try{
                    $this->do_admin_includes();
                    $value_parameters   =   array();
                    switch( $_POST['view'] ):
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
                    $filter .= " ORDER BY tkt_time_logged DESC ";

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
                        $response = __( "Nothing to see here. Great work!","kanzu-support-desk" );
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
        public function get_single_ticket(){

            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
               die ( __('Busted!','kanzu-support-desk') );                         
            }
            $this->do_admin_includes();	
            try{
               $tickets = new KSD_Tickets_Controller();	
               $ticket = $tickets->get_ticket($_POST['tkt_id']);
               $this->format_ticket_for_viewing( $ticket );
               echo json_encode($ticket);
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
         * Retrieve a ticket's replies
         */
        public function get_ticket_replies(){
            if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                die ( __('Busted!','kanzu-support-desk') );                         
            }
            $this->do_admin_includes();
            try{
                $RC = new KSD_Replies_Controller();
                $query = " rep_tkt_id = %d";
                $value_parameters[] = $_POST['tkt_id'];
                $replies = $RC->get_replies( $query,$value_parameters );
                //Replace the rep_created_by ID with the name of the reply creator
                foreach ( $replies as $reply ){
                    $reply->rep_created_by = get_userdata( $reply->rep_created_by )->display_name;
                }
                echo json_encode( $replies );
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
	 * Delete a ticket
	 */
	public function delete_ticket(){
            try{
                if ( ! wp_verify_nonce( $_POST['ksd_admin_nonce'], 'ksd-admin-nonce' ) ){
                             die ( __('Busted!','kanzu-support-desk') );
                }
                    $this->do_admin_includes();	
                    $tickets = new KSD_Tickets_Controller();		
                    
                    if( $tickets->delete_ticket( $_POST['tkt_id']) ){
                        echo json_encode(__("Deleted","kanzu-support-desk"));
                    }else{
                        throw new Exception( __("Failed","kanzu-support-desk") , -1);
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
                $updated_ticket = new stdClass();
		$updated_ticket->tkt_id = $_POST['tkt_id'];
		$updated_ticket->new_tkt_status = $_POST['tkt_status'];
                
		$tickets = new KSD_Tickets_Controller();	
                
                if( $tickets->update_ticket( $updated_ticket ) ){
                    echo json_encode( __("Updated","kanzu-support-desk"));
                }else {
                    throw new Exception( __("Failed","kanzu-support-desk") , -1);
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
                    echo json_encode( __("Updated","kanzu-support-desk"));
                }else {
                    throw new Exception( __("Failed","kanzu-support-desk") , -1);
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
               $updated_ticket = new stdClass();
               $updated_ticket->tkt_id = $_POST['tkt_id'];
               $updated_ticket->new_tkt_assigned_to = $_POST['tkt_assign_assigned_to'];
               $updated_ticket->new_tkt_assigned_by = $_POST['ksd_current_user_id'];
               $assign_ticket = new KSD_Tickets_Controller();                       
               
               if( $assign_ticket->update_ticket( $updated_ticket ) ){
                   //Add the event to the assignments table
                   $this->do_ticket_assignment ( $updated_ticket->tkt_id,$updated_ticket->new_tkt_assigned_to, $updated_ticket->new_tkt_assigned_by );
                   echo json_encode( __("Re-assigned","kanzu-support-desk"));
               }else{
                   throw new Exception( __("Failed","kanzu-support-desk") , -1);
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
            
            if ( ! $add_on_mode ){//Check for NONCE if not in add-on mode    
               if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ){
			 die ( __('Busted!','kanzu-support-desk') );
               }
            }
                $this->do_admin_includes();
                
                try{    
                    //If this was called by an add-on, populate the $_POST array
                    if ( $add_on_mode ){
                        $_POST = $ticket_reply_array;
                    }
                    $new_reply = new stdClass(); 
                    $new_reply->rep_tkt_id    	 = sanitize_text_field( $_POST['tkt_id'] );    
                    $new_reply->rep_created_by   = sanitize_text_field( $_POST['ksd_rep_created_by'] );
                    $new_reply->rep_message 	 = wp_kses_post( stripslashes( $_POST['ksd_ticket_reply'] )  );
                    if ( strlen( $new_reply->rep_message ) < 2 && ! $add_on_mode ){//If the response sent it too short
                       throw new Exception( __("Error | Reply too short", 'kanzu-support-desk'), -1 );
                    }
                   //Add the reply to the replies table
                   $RC = new KSD_Replies_Controller(); 
                   $response = $RC->add_reply( $new_reply );
                   
                   if( $add_on_mode ){
                       do_action( 'ksd_new_ticket_logged', $_POST['addon_tkt_id'], $response );
                       return;//End the party if this came from an add-on. All an add-on needs if for the reply to be logged
                   }
                   
                    //Get the customer's email address and send them this reply.
                    $TC = new KSD_Tickets_Controller();
                    $ticket_details   = $TC->get_ticket( $new_reply->rep_tkt_id );     
                    $user = get_userdata( $ticket_details->tkt_cust_id );
                    $this->send_email( $user->user_email, $new_reply->rep_message, 'Re: '.$ticket_details->tkt_subject );//NOTE: Prefix the reply subject with Re:    
                   
                   if ( $response > 0 ){
                      echo json_encode(  esc_html( $new_reply->rep_message )  );
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
         * Log new tickets or replies initiated by add-ons
         * @param Object $new_ticket New ticket object. Can also be a reply object
         * @since 1.0.1
         */
        public function do_log_new_ticket( $new_ticket ){            
            $this->do_admin_includes();
            $TC = new KSD_Tickets_Controller();
            //First check if the ticket initiator exists in our users table. 
            $customer_details = get_user_by ( 'email', $new_ticket->cust_email );
            if ( $customer_details ){//If the customer's already in the Db, get their customer ID and check whether this is a new ticket or a response
                $new_ticket->tkt_cust_id = $customer_details->ID;
               //Check whether it is a new ticket or a reply. We match against subject and ticket initiator
                $value_parameters   = array();
                $filter             = " tkt_subject = %s AND tkt_status != %d AND tkt_cust_id = %d ";
                $value_parameters[] = sanitize_text_field( str_ireplace( "Re:", "", $new_ticket->tkt_subject ) ) ;  //Remove the Re: prefix from the subject of replies. @TODO Stands the
                                                                                                                    //very, very remote risk of removing other Re:'s in the subject if they exist
                                                                                                                    //Note that we use str_ireplace because it is less expensive than preg_replace
                $value_parameters[] = 'RESOLVED' ;
                $value_parameters[] = $new_ticket->tkt_cust_id ;
                $the_ticket = $TC->get_tickets( $filter, $value_parameters );
                if ( count( $the_ticket ) > 0  ){//this is a reply
                    //Get a $_POST array to send to the reply_ticket function
                    $_POST['tkt_id']                = $the_ticket[0]->tkt_id;//The ticket ID
                    $_POST['ksd_ticket_reply']      = $new_ticket->tkt_message;//Get the reply
                    $_POST['ksd_rep_created_by']    = $new_ticket->tkt_cust_id;//The customer's ID
                    $_POST['addon_tkt_id']          = $new_ticket->addon_tkt_id;//The add-on's ID for this ticket
                    $this->reply_ticket( $_POST ); 
                    return; //die removed because it was triggering a fatal error in add-ons
               }               
            }
            //This is a new ticket
            $_POST = $this->convert_ticket_object_to_post( $new_ticket );
            $this->log_new_ticket( $_POST );                        
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
         * Log new tickets.  The different channels (admin side, front-end) all 
         * call this method to log the ticket. Other plugins call $this->do_new_ticket_logging  through
         * an action
         * @param Array $new_ticket A new ticket array. This is present when ticket logging was initiated 
         *              by an add-on
         */
        public function log_new_ticket( $new_ticket_array=null ){
                //In add-on mode, this function was called by an add-on                            
                $add_on_mode = ( is_array( $new_ticket_array ) ? true : false );

                if( ! $add_on_mode ){//Check for NONCE if not in add-on mode
                    if ( ! wp_verify_nonce( $_POST['new-ticket-nonce'], 'ksd-new-ticket' ) ){
                             die ( __('Busted!','kanzu-support-desk') );
                    }
                }
                
		$this->do_admin_includes();
            
                try{
            	$tkt_channel    = "STAFF"; //This is the default channel
                $tkt_status     = "OPEN";//The default status
                //If this was called by an add-on, populate the $_POST array
                if ( $add_on_mode ){
                    $_POST = $new_ticket_array;
                }
            
                //Check what channel the request came from
                switch ( sanitize_text_field( $_POST['ksd_tkt_channel']) ){
                    case 'support_tab':
                        $tkt_channel   =       "SUPPORT_TAB";
                        break;
                     case 'EMAIL':
                         $tkt_channel   =       "EMAIL";
                         break;
                    default:
                        $tkt_channel    =      "STAFF";
                }                       
                           
                $ksd_excerpt_length = 30;//The excerpt length to use for the message

                //We sanitize each input before storing it in the database
                $new_ticket = new stdClass(); 
                $new_ticket->tkt_subject    	    = sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_subject' ] ) );
                $sanitized_message                  = wp_kses_post( stripslashes( $_POST[ 'ksd_tkt_message' ] ) );
                $new_ticket->tkt_message_excerpt    = wp_trim_words( $sanitized_message, $ksd_excerpt_length );
                $new_ticket->tkt_message            = $sanitized_message;
                $new_ticket->tkt_channel            = $tkt_channel;
                $new_ticket->tkt_status             = $tkt_status;             
                
                //Server side validation for the inputs. Only holds if we aren't in add-on mode
                if ( ( ! $add_on_mode && strlen( $new_ticket->tkt_subject ) < 2 || strlen( $new_ticket->tkt_message ) < 2 ) ) {
                     throw new Exception( __('Error | Your subject and message should be at least 2 characters','kanzu-support-desk'), -1 );
                }
                
                //These other fields are only available if a ticket is logged from the admin side so we need to 
                //first check if they are set
                if ( isset( $_POST[ 'ksd_tkt_severity' ] ) ) {
                    $new_ticket->tkt_severity           =  $_POST[ 'ksd_tkt_severity' ] ;   
                }
                if ( isset( $_POST[ 'ksd_tkt_assigned_to' ] ) && !empty( $_POST[ 'ksd_tkt_assigned_to' ] ) ) {
                    $new_ticket->tkt_assigned_to    =  $_POST[ 'ksd_tkt_assigned_to' ] ;
                }   
                               
                //Get the settings. We need them for tickets logged from the support tab
                $settings = Kanzu_Support_Desk::get_settings();
                
                 //If the ticket wasn't assigned by the user, check whether auto-assignment is set so we auto-assign it
                if ( empty( $_POST[ 'ksd_tkt_assigned_to' ] ) &&  !empty( $settings['auto_assign_user'] ) ) {
                    $new_ticket->tkt_assigned_to    = $settings['auto_assign_user'];                    
                }

                //Return a different message based on the channel the request came on
                $output_messages_by_channel = array();
                $output_messages_by_channel[ 'STAFF' ] = __("Ticket Logged", "kanzu-support-desk");
                $output_messages_by_channel[ 'SUPPORT_TAB' ] = $output_messages_by_channel[ 'EMAIL' ] = $settings['tab_message_on_submit'];
      
                //Get the provided email address and use it to check whether the customer's already in the Db
                $cust_email           = sanitize_email( $_POST[ 'ksd_cust_email' ] );
                //Check that it is a valid email address. Don't do this check in add-on mode
                if ( ! $add_on_mode && !is_email( $cust_email )){
                     throw new Exception( __('Error | Invalid email address specified','kanzu-support-desk') , -1);
                }                

                $customer_details = get_user_by ( 'email', $cust_email );
                if ( $customer_details ){//If the customer's already in the Db, proceed. Get their customer ID
                        $new_ticket->tkt_cust_id = $customer_details->ID;
                }
                else{//The customer isn't in the Db. We add them
                    $new_customer = new stdClass();
                    $new_customer->user_email           = $cust_email;
                    //Check whether one or more than one customer name was provided
                    if( false === strpos( trim( sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ) ), ' ') ){//Only one customer name was provided
                       $new_customer->first_name   =   sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] );
                    }
                    else{
                       preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ), $new_customer_fullname );
                        $new_customer->first_name   = $new_customer_fullname[1];
                        $new_customer->last_name   = $new_customer_fullname[2];//We store everything besides the first name in the last name field
                    }
                    //Add the customer to the user table and get the customer ID
                    $new_ticket->tkt_cust_id    =  $this->create_new_customer( $new_customer );
                }   
                
                //Set 'logged by' to the ID of whoever logged it ( admin side tickets ) or to the customer's ID ( for tickets from the front-end )
               $new_ticket->tkt_assigned_by   = ( !empty( $_POST[ 'ksd_tkt_assigned_by' ] ) ? sanitize_text_field( $_POST[ 'ksd_tkt_assigned_by' ] ) : $new_ticket->tkt_cust_id );
                
               //Log date and time if given
               if (isset( $_POST[ 'ksd_tkt_time_logged' ] ) ){
                   $new_ticket->tkt_time_logged = sanitize_text_field( $_POST[ 'ksd_tkt_time_logged' ] );
               }
               
               
                $TC = new KSD_Tickets_Controller();
                $new_ticket_id = $TC->log_ticket( $new_ticket );
                $new_ticket_status = (  $new_ticket_id > 0  ? $output_messages_by_channel[ $tkt_channel ] : __("Error", 'kanzu-support-desk') );
                
                if ( ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "SUPPORT_TAB") || ( $tkt_channel  ==  "STAFF" && isset($_POST['ksd_send_email'])) ){
                    $this->send_email( $cust_email );
                }
                //Add this event to the assignments table
                if ( isset( $new_ticket->tkt_assigned_to ) ) {
                    $this->do_ticket_assignment ( $new_ticket_id,$new_ticket->tkt_assigned_to,$new_ticket->tkt_assigned_by ); 
                }   

                //For add-ons to do something after new ticket is added. We share the ID and the final status
                if ( isset( $_POST['ksd_addon_tkt_id'] ) ) {                    
                    do_action( 'ksd_new_ticket_logged', $_POST['ksd_addon_tkt_id'], $new_ticket_status );
                }
                //If this was initiated by the email add-on, end the party here
                if ( ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "EMAIL") ){
                     $this->send_email( $cust_email );
                     return;
                }
                
                if( $add_on_mode ) {
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
         * Assign the ticket 
         */
        private function do_ticket_assignment( $ticket_id,$assign_to,$assign_by ){
            $this->do_admin_includes();
           $assignment = new KSD_Assignments_Controller();
           $assignment->assign_ticket( $ticket_id, $assign_to, $assign_by );  
        }
        
        /**
         * Replace a ticket's logged_by field with the nicename of the user who logged it
         * Replace the tkt_time_logged with a date better-suited for viewing
         * NB: Because we use {@link KSD_Users_Controller}, call this function after {@link do_admin_includes} has been called.   
         * @param Object $ticket The ticket to modify
         */
        private function format_ticket_for_viewing( $ticket ){
            //If the ticket was logged by staff from the admin end, then the username is available in wp_users. Otherwise, we retrive the name
            //from the KSD customers table
           // $tmp_tkt_assigned_by = ( 'STAFF' === $ticket->tkt_channel ? $users->get_user($ticket->tkt_assigned_by)->display_name : $CC->get_customer($ticket->tkt_assigned_by)->cust_firstname );
            $tmp_tkt_cust_id = get_userdata( $ticket->tkt_cust_id )->display_name;
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
                                        'message' => __("No logged tickets. Graphing isn't possible","kanzu-support-desk") , 
                                        'code'=> -1 )
                            );
                            echo json_encode($response);	
                            die();// IMPORTANT: don't leave this out
                        }
                        
                        $y_axis_label = __("Day", "kanzu-support-desk" );
                        $x_axis_label = __("Ticket Volume", "kanzu-support-desk" );
                        
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
                        //Compute the average
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
                    $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) ? sanitize_text_field ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
                }
                //For a checkbox, if it is unchecked then it won't be set in $_POST
                $checkbox_names = array("show_support_tab","tour_mode","enable_new_tkt_notifxns","enable_recaptcha");
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
                   echo json_encode(  __("Settings Updated"));
                }else{
                    throw new Exception(__("Update failed. Please retry. "  ), -1);
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
                    echo json_encode( __("Settings Reset") );
                }else{
                    throw new Exception( __("Reset failed. Please retry"), -1);
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
         public function update_private_note(){
               if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ){
			 die ( __('Busted!','kanzu-support-desk') );
               }
		$this->do_admin_includes();
                try{
                    $updated_ticket = new stdClass();
                    $updated_ticket->tkt_id = $_POST['tkt_id'];
                    $updated_ticket->new_tkt_private_note = sanitize_text_field ( stripslashes ( $_POST['tkt_private_note']) );
                    $tickets = new KSD_Tickets_Controller();		
                    //$status = ( $tickets->update_ticket( $updated_ticket  ) ? __("Noted","kanzu-support-desk") : __("Failed","kanzu-support-desk") );
                    if ( $tickets->update_ticket( $updated_ticket  ) ){
                        echo json_encode( __("Noted","kanzu-support-desk") );
                    }else {
                        throw new Exception(__("Failed","kanzu-support-desk"), -1);
                    }
                    //echo json_encode( $status );
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
                    __( "Kanzu Support Desk Dashboard" ,"kanzu-support-desk"),
                    __( "Welcome to Kanzu Support Desk! Thanks for choosing us. Let's see where everything is. First off...","kanzu-support-desk"),
                    __( "Your dashboard displays your performance statistics","kanzu-support-desk")
                    ),
                    "button2"  => __( "Next", "kanzu-support-desk" ),
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
                'target' => '.enable_new_tkt_notifxns',
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
                    __( "That's it! Dive right in. To take this tour again, click 'Enable Tour Mode' in your settings tab, update your settings then refresh your page","kanzu-support-desk")
                    ),
                    'position' => array( 'edge' => 'left', 'align' => 'top','nudgehorizontal' => 1 )
                )
            );
            return $p;
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
          * Send the KSD team feedback
          * @since 1.1.0
          */
         public function send_feedback(){
            if ( ! wp_verify_nonce( $_POST['feedback-nonce'], 'ksd-send-feedback' ) ){
			 die ( __('Busted!','kanzu-support-desk') );
               }
             if (strlen( $_POST['ksd_user_feedback'] )<= 2 ){
                $response = __( "Error | The feedback field's empty. Please type something then send","kanzu-support-desk" ); 
             }  
             else{
                $response =  ( $this->send_email( "feedback@kanzucode.com", sanitize_text_field( $_POST['ksd_user_feedback'] ),"KSD Feedback" ) ? __( "Sent successfully. Thank you!", "kanzu-support-desk" ) : __( "Error | Message not sent. Please try sending mail directly to feedback@kanzucode.com","kanzu-support-desk" ) );
             }
             echo json_encode( $response );	
             die();// IMPORTANT: don't leave this out
         }
         
         /**
          * Send mail. 
          * @param string $to Recipient email address
          * @param string $message The message to send. Can be "new_ticket"
          * @param string $subject The message subject
          */
         public function send_email( $to, $message="new_ticket", $subject=null ){
             $settings = Kanzu_Support_Desk::get_settings();             
             switch ( $message ):
                 case 'new_ticket'://For new tickets
                     $subject   = $settings['ticket_mail_subject'];
                     $message   = $settings['ticket_mail_message'];                     
             endswitch;
                     $headers = 'From: '.$settings['ticket_mail_from_name'].' <'.$settings['ticket_mail_from_email'].'>' . "\r\n";
             return wp_mail( $to, $subject, $message, $headers ); 
         }
         
         /**
          * Retrieve Kanzu Support Desk notifications. These are currently
          * retrieved from the KSD blog feed, http://blog.kanzucode.com/feed/
          * @since 1.3.2
          */
         public function get_notifications(){
            ob_start();  
            if ( false === ( $cache = get_transient( 'ksd_notifications_feed' ) ) ) {
		$feed = wp_remote_get( 'http://blog.kanzucode.com/feed/', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $feed ) ) {                   
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'ksd_notifications_feed', $cache, 86400 );//Check everyday
			}
		} else {
                    $cache["error"] =  __( "Sorry, an error occurred while retrieving the latest notifications. A re-attempt will be made later. Thank you.","kanzu-support-desk" );
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
  
}
endif;

return new KSD_Admin();

