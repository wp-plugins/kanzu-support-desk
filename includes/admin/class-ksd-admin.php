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

		// Add an action link pointing to the settings page.
		add_filter( 'plugin_action_links_' . plugin_basename(KSD_PLUGIN_FILE), array( $this, 'add_action_links' ) );		

		//Handle AJAX calls
		add_action( 'wp_ajax_ksd_filter_tickets', array( $this, 'filter_tickets' ));
                add_action( 'wp_ajax_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
		add_action( 'wp_ajax_ksd_delete_ticket', array( $this, 'delete_ticket' ));
		add_action( 'wp_ajax_ksd_change_status', array( $this, 'change_status' ));
                add_action( 'wp_ajax_ksd_assign_to', array( $this, 'assign_to' ));
                add_action( 'wp_ajax_ksd_reply_ticket', array( $this, 'reply_ticket' ));
                add_action( 'wp_ajax_ksd_get_single_ticket', array( $this, 'get_single_ticket' ));   
                add_action( 'wp_ajax_ksd_get_ticket_replies', array( $this, 'get_ticket_replies' ));   
		add_action( 'wp_ajax_ksd_dashboard_ticket_volume', array( $this, 'get_dashboard_ticket_volume' )); 
                add_action( 'wp_ajax_ksd_get_dashboard_summary_stats', array( $this, 'get_dashboard_summary_stats' ));  
                add_action( 'wp_ajax_ksd_update_settings', array( $this, 'update_settings' )); 
                add_action( 'wp_ajax_ksd_reset_settings', array( $this, 'reset_settings' )); 
                add_action( 'wp_ajax_ksd_update_private_note', array( $this, 'update_private_note' ));              

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
                wp_enqueue_script( KSD_SLUG . '-admin-gcharts', '//google.com/jsapi', array(), KSD_VERSION ); 
                wp_enqueue_script( KSD_SLUG . '-admin-js', KSD_PLUGIN_URL.'/assets/js/ksd-admin.js', array( 'jquery','jquery-ui-core','jquery-ui-tabs','json2','jquery-ui-dialog','jquery-ui-tooltip','jquery-ui-accordion' ), KSD_VERSION ); 
		
                //Variables to send to the admin JS script
                $ksd_admin_tab = ( isset( $_GET['page'] ) ? $_GET['page'] : "" );//This determines which tab to show as active
                
                $agents_list = "<ul class='ksd_agent_list hidden'>";//The available list of agents
                foreach (  get_users() as $agent ) {
                    $agents_list .= "<li ID=".$agent->ID.">".esc_html( $agent->display_name )."</li>";
                }
                $agents_list .= "</ul>";
                
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
                $admin_labels_array['msg_still_loading']            = __('Still Loading...','kanzu-support-desk');
                $admin_labels_array['msg_loading']                  = __('Loading...','kanzu-support-desk');
                        
                
                //Localization allows us to send variables to the JS script
                wp_localize_script( KSD_SLUG . '-admin-js',
                                    'ksd_admin',
                                    array(  'admin_tab'             =>  $ksd_admin_tab,
                                            'ajax_url'              =>  admin_url( 'admin-ajax.php'),
                                            'ksd_admin_nonce'       =>  wp_create_nonce( 'ksd-admin-nonce' ),
                                            'ksd_tickets_url'       =>  admin_url( 'admin.php?page=ksd-tickets'),
                                            'ksd_agents_list'       =>  $agents_list,
                                            'ksd_current_user_id'   =>  get_current_user_id(),
                                            'ksd_labels'            =>  $admin_labels_array
                                        )
                                    );
		

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
                $tickets_raw = $tickets->get_tickets( $filter,$value_parameters ); 	
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
               $this->format_ticket_for_viewing($ticket);
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
                $replies = new KSD_Replies_Controller();
                $query = " rep_tkt_id = %d";
                $value_parameters = array ($_POST['tkt_id']);
                $response = $replies->get_replies( $query,$value_parameters );
                echo json_encode($response);
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
         */
        
        public function reply_ticket(){
               if ( ! wp_verify_nonce( $_POST['edit-ticket-nonce'], 'ksd-edit-ticket' ) ){
			 die ( __('Busted!','kanzu-support-desk') );
               }
                $this->do_admin_includes();
                
                try{    
                    $new_reply = new stdClass(); 
                    $new_reply->rep_tkt_id    	 =  $_POST['tkt_id'] ;
                    $new_reply->rep_message 	 = sanitize_text_field( stripslashes ( $_POST['ksd_ticket_reply'] ) );
                    if ( strlen( $new_reply->rep_message ) < 2 ){//If the response sent it too short
                       throw new Exception( __("Error | Reply too short", 'kanzu-support-desk'), -1 );
                    }
                    //Get the customer's email address and send them this reply
                    $CC = new KSD_Customers_Controller();
                    $customer_details   = $CC->get_customer_by_ticketID( $new_reply->rep_tkt_id );                   
                    $this->send_email( $customer_details[0]->cust_email, $new_reply->rep_message, $customer_details[0]->tkt_subject );

                   $RC = new KSD_Replies_Controller(); 
                   $response = $RC->add_reply( $new_reply );
                   
                   if ($response > 0 ){
                      echo json_encode($new_reply->rep_message );
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
         * Log new tickets.  The different channels (admin side, front-end) all 
         * call this method to log the ticket
         */
        public function log_new_ticket(){
                if ( ! wp_verify_nonce( $_POST['new-ticket-nonce'], 'ksd-new-ticket' ) ){
			 die ( __('Busted!','kanzu-support-desk') );
                }
		$this->do_admin_includes();
            
                try{
            	$tkt_channel    = "STAFF"; //This is the default channel
                $tkt_status     = "OPEN";//The default status
                
                //Check what channel the request came from
                switch ( sanitize_text_field( $_POST['ksd_tkt_channel']) ){
                    case 'support_tab':
                        $tkt_channel   =       "SUPPORT_TAB";
                        break;
                    default:
                        $tkt_channel    =      "STAFF";
                }                       
                           
                $ksd_excerpt_length = 30;//The excerpt length to use for the message
                
                //We sanitize each input before storing it in the database
                $new_ticket = new stdClass(); 
                $new_ticket->tkt_subject    	    = sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_subject' ] ) );
                $new_ticket->tkt_message_excerpt    = wp_trim_words( sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_message' ] )  ), $ksd_excerpt_length );
                $new_ticket->tkt_message            = sanitize_text_field( stripslashes( $_POST[ 'ksd_tkt_message' ] ));
                $new_ticket->tkt_channel            = $tkt_channel;
                $new_ticket->tkt_status             = $tkt_status;
                
                //Server side validation for the inputs
                if ( strlen( $new_ticket->tkt_subject ) < 2 || strlen( $new_ticket->tkt_message ) < 2 ) {
                     throw new Exception( __('Error | Your subject and message should be at least 2 characters','kanzu-support-desk'), -1 );
                }
                
                //These other fields are only available if a ticket is logged from the admin side so we need to 
                //first check if they are set
                if ( isset( $_POST[ 'ksd_tkt_severity' ] ) ) {
                    $new_ticket->tkt_severity           =  $_POST[ 'ksd_tkt_severity' ] ;   
                }
                if ( isset( $_POST[ 'ksd_tkt_assigned_to' ] ) ) {
                    $new_ticket->tkt_assigned_to    =  $_POST[ 'ksd_tkt_assigned_to' ] ;
                }
               
                //Get the settings. We need them for tickets logged from the support tab
                $settings = Kanzu_Support_Desk::get_settings();
                //Return a different message based on the channel the request came on
                $output_messages_by_channel = array();
                $output_messages_by_channel[ 'STAFF' ] = __("Ticket Logged", "kanzu-support-desk");
                $output_messages_by_channel[ 'SUPPORT_TAB' ] = $settings['tab_message_on_submit'];
      
                //Get the provided email address and use it to check whether the customer's already in the Db
                $cust_email           = sanitize_email( $_POST[ 'ksd_cust_email' ] );
                //Check that it is a valid email address
                if (!is_email( $cust_email )){
                     throw new Exception( __('Error | Invalid email address specified','kanzu-support-desk') , -1);
                }
                
                $CC = new KSD_Customers_Controller();
                $customer_details = $CC->get_customer_by_email ( $cust_email );
                if ( $customer_details ){//If the customer's already in the Db, proceed. Get their customer ID
                        $new_ticket->tkt_cust_id = $customer_details[0]->cust_id;
                }
                else{//The customer isn't in the Db. We add them
                    $new_customer = new stdClass();
                    $new_customer->cust_email           = $cust_email;
                    //Check whether one or more than one customer name was provided
                    if( false === strpos( trim( sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ) ), ' ') ){//Only one customer name was provided
                       $new_customer->cust_firstname   =   sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] );
                    }
                    else{
                       preg_match('/(\w+)\s+([\w\s]+)/', sanitize_text_field( $_POST[ 'ksd_cust_fullname' ] ), $new_customer_fullname );
                        $new_customer->cust_firstname   = $new_customer_fullname[1];
                        $new_customer->cust_lastname   = $new_customer_fullname[2];//We store everything besides the first name in the last name field
                    }
                    //Add the customer to the customers table and get the customer ID
                    $new_ticket->tkt_cust_id    =   $CC->add_customer( $new_customer );
                }   
                
                //Set 'logged by' to the ID of whoever logged it ( admin side tickets ) or to the customer's ID ( for tickets from the front-end )
               $new_ticket->tkt_assigned_by   = ( isset( $_POST[ 'ksd_tkt_assigned_by' ] ) ? sanitize_text_field( $_POST[ 'ksd_tkt_assigned_by' ] ) : $new_ticket->tkt_cust_id );
                
                $TC = new KSD_Tickets_Controller();
                $new_ticket_id = $TC->log_ticket( $new_ticket );
                $new_ticket_status = (  $new_ticket_id > 0  ? $output_messages_by_channel[ $tkt_channel ] : __("Error", 'kanzu-support-desk') );
                
                if ( ( "yes" == $settings['enable_new_tkt_notifxns'] &&  $tkt_channel  ==  "SUPPORT_TAB") || ( $tkt_channel  ==  "STAFF" && isset($_POST['ksd_send_email'])) ){
                    $this->send_email( $cust_email );
                }
                //Add this event to the assignments table
                $this->do_ticket_assignment ( $new_ticket_id,$new_ticket->tkt_assigned_to,$new_ticket->tkt_assigned_by );

                //for addons to do something aftr new ticket is added.
                do_action( 'ksd_new_ticket', $_POST );
                
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
            //Replace the username
            $users = new KSD_Users_Controller();
            $ticket->tkt_assigned_by = str_replace($ticket->tkt_assigned_by,$users->get_user($ticket->tkt_assigned_by)->user_nicename,$ticket->tkt_assigned_by);
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
				$output_array[] = array ($ticket->date_logged,$ticket->ticket_volume);			
			}
        
			
			echo json_encode($output_array, JSON_NUMERIC_CHECK);
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
                        echo json_encode ( $summary_stats , JSON_NUMERIC_CHECK);                    
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
                //Iterate through the new settings and save them. 
                foreach ( $updated_settings as $option_name => $default_value ) {
                    $updated_settings[$option_name] = ( isset ( $_POST[$option_name] ) ? sanitize_text_field ( stripslashes ( $_POST[$option_name] ) ) : $updated_settings[$option_name] );
                }
                //Apply the settings filter to get settings from add-ons
                $updated_settings = apply_filters( 'ksd_settings', $updated_settings, $_POST );
                
                $status = update_option( KSD_OPTIONS_KEY, $updated_settings );
                
                if( true === $status){
                    do_action('ksd_settings_saved');
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
 
}
endif;

return new KSD_Admin();

