	<?php
/**
 * Front-end of Kanzu Support Desk
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Public' ) ) : 
    
class KSD_Public {
    
    public function __construct() {
        
        //Enqueue styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
        //Add form for new ticket to the footer
        add_action( 'wp_footer', array( $this , 'generate_new_ticket_form' ));
        //Handle AJAX
        add_action( 'wp_ajax_nopriv_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
        add_action( 'wp_ajax_nopriv_ksd_register_user', array( $this, 'register_user' ));        
        
        //Add a shortcode for the public form
        add_shortcode( 'ksd_support_form', array( $this,'form_short_code' ) );
        add_shortcode( 'ksd_my_tickets', array( $this,'display_my_tickets' ) );
        
        //Add custom post types
        add_action( 'init', array( $this, 'create_custom_post_types' ) );
        //Add custom ticket statuses
        add_action( 'init', array( $this, 'custom_ticket_statuses' ) );        
        
        //Style public view of tickets
        add_filter( 'the_content', array( $this, 'apply_templates' ));
        
        //Redirect customers on login                
        add_filter( 'login_redirect', array ( $this, 'do_login_redirect' ), 10, 3 );
    }
    
    /**
     * Generate the ticket form that's displayed in the front-end
     * NB: We only show the form if you enabled the 'show_support_tab' option
     */
    public function generate_new_ticket_form(){
        $settings = Kanzu_Support_Desk::get_settings();
        if( "yes" == $settings['show_support_tab'] ) {?>
                <button id="ksd-new-ticket-public"><?php echo $settings['support_button_text']; ?></button><?php
                if( is_user_logged_in() ){//For logged in users                    
                    include( KSD_PLUGIN_DIR .  'includes/public/views/html-public-new-ticket.php' ); //Note that this isn't an include_once since you can have multiple forms on the same page (In the content using a shortcode and as a hidden slide-in,slide-out element)
                }
                else{
                    $form_position_class = 'ksd-form-hidden-tab';
                    include( KSD_PLUGIN_DIR .  'includes/public/views/html-public-register.php' ); 
                }
        }
    }
   
    /**
     * Display a form wherever shortcode [ksd-form] is used
     */
   public function form_short_code(){
        //Include the templating and admin classes
        include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        if( !is_user_logged_in() ) { 
            $form_position_class = 'ksd-form-short-code';
            include( KSD_PLUGIN_DIR .  'includes/public/views/html-public-register.php' );   
        } else{
            $ksd_template = new KSD_Templates();
            $ksd_template->get_template_part( 'single','submit-ticket' );
        }
   }    
   
   /**
    * Display a customer's tickets
    * @since 2.0.0
    */
   public function display_my_tickets(){
        //Include the templating and admin classes
        include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
        if( !is_user_logged_in() ) { 
            $form_position_class = 'ksd-form-short-code';
            include( KSD_PLUGIN_DIR .  'includes/public/views/html-public-register.php' );   
        } else{
            $ksd_template = new KSD_Templates();
            $ksd_template->get_template_part( 'list','my-tickets' );
        }
   }
    
    	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_public_styles() {	
		wp_enqueue_style( KSD_SLUG .'-public-css', KSD_PLUGIN_URL . 'assets/css/ksd-public.css' , array() , KSD_VERSION );
        }
        
        /**
         * Enqueue scripts used solely at the front-end
         * @since 1.0.0
         */
        public function enqueue_public_scripts() {	
            wp_enqueue_script( KSD_SLUG . '-public-js', KSD_PLUGIN_URL .  'assets/js/ksd-public.js' , array( 'jquery', 'jquery-ui-core' ), KSD_VERSION );
            $ksd_public_labels =  array();
            $ksd_public_labels['msg_grecaptcha_error']  = sprintf( __( 'Please check the <em>%s</em> checkbox and wait for it to complete loading', 'kanzu-support-desk'), "I'm not a robot" );
            $ksd_public_labels['msg_error_refresh']     = __('Sorry, but it seems like something went wrong. Please try again or reload the page.','kanzu-support-desk');
            $ksd_public_labels['msg_reply_sent']        = __('Your reply has been sent successfully. We will get back to you shortly. Thank you.','kanzu-support-desk');
            $ksd_public_labels['lbl_name']              = __('Name','kanzu-support-desk');
            $ksd_public_labels['lbl_subject']           = __('Subject','kanzu-support-desk');
            $ksd_public_labels['lbl_email']             = __('Email','kanzu-support-desk');
            $ksd_public_labels['lbl_first_name']        = __('First Name','kanzu-support-desk');
            $ksd_public_labels['lbl_last_name']         = __('Last Name','kanzu-support-desk');
            $ksd_public_labels['lbl_username']          = __('Username','kanzu-support-desk');
            
            //@TODO Don't retrieve settings again. Use same set of settings
            $settings = Kanzu_Support_Desk::get_settings();
            
            wp_localize_script( KSD_SLUG . '-public-js', 'ksd_public' , 
                    array(  'ajax_url'                  => admin_url( 'admin-ajax.php'), 
                            'ksd_public_labels'         => $ksd_public_labels,
                            'ksd_submit_tickets_url'    => get_permalink( $settings['page_submit_ticket'] )
                    ) 
                    );    
            //Check whether enable_recaptcha is checked. 
            if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ){
               wp_enqueue_script( KSD_SLUG . '-public-grecaptcha', '//www.google.com/recaptcha/api.js', array(), KSD_VERSION );  
            }
        }
        
        /**
         * Apply templates to a user's tickets prior to display
         * Allow the tickets to be modified by actions before and after and for the ticket content itself
         * to be modified using a filter
         * @since 2.0.0
         */
        public function apply_templates( $content ){
            global $post;
            if ( $post && $post->post_type == 'ksd_ticket' && is_singular( 'ksd_ticket' ) && is_main_query() && !post_password_required() ) {
                if( !is_user_logged_in() ) { //@TODO Send the current URL as the redirect URL for the 'login' and 'Register' action
                    include_once( KSD_PLUGIN_DIR.  "includes/admin/class-ksd-admin.php");
                    $form_position_class = 'ksd-form-short-code';
                    include( KSD_PLUGIN_DIR .  'includes/public/views/html-public-register.php' ); 
                    return;
                } 
                        global $current_user;   
                        if( in_array( 'ksd_customer', $current_user->roles ) && $current_user->ID != $post->post_author ){//This is a customer
                           return __( "Sorry, you do not have sufficient priviledges to view another customer's tickets", "kanzu-support-desk" );
                        }

                        //Include the templating class
                        include_once( KSD_PLUGIN_DIR.  "includes/public/class-ksd-templates.php");
                        
                        //Do actions before the ticket
                        ob_start();
                        do_action( 'ksd_before_ticket_content', $post->ID );
                        $content = ob_get_clean() . $content;
                        
                        //Modify the ticket content
                        $content = apply_filters( 'ksd_ticket_content', $content );
                        
                        //Do actions after the ticket
                        ob_start();
                        do_action( 'ksd_after_ticket_content', $post->ID );
                        $content .= ob_get_clean();
                }
            return $content;
        }
        
        /***
         * Create 'ksd_ticket' Custom post type
         * @since 2.0.0
         */
        public function create_custom_post_types(){

            /*----Tickets -----*/
            $labels = array(
                'name'              => _x('Tickets', 'post type general name','kanzu-support-desk' ),
                'singular_name'     => _x('Ticket', 'post type singular name','kanzu-support-desk' ),
                'add_new'           => _x('Add New', 'singular item','kanzu-support-desk' ),
                'add_new_item'      => __('Add New Ticket','kanzu-support-desk' ),
                'edit_item'         => __('Reply Ticket','kanzu-support-desk' ),
                'new_item'          => __('New Ticket','kanzu-support-desk' ),
                'all_items'         => __('All Tickets','kanzu-support-desk' ),
                'view_item'         => __('View Ticket','kanzu-support-desk' ),
                'search_items'      => __('Search Tickets','kanzu-support-desk' ),
                'not_found'         => __('No Tickets found','kanzu-support-desk' ),
                'not_found_in_trash'=> __('No tickets found in the Trash','kanzu-support-desk' ),
                'parent_item_colon' => '',
                'menu_name'         => __('Tickets','kanzu-support-desk' )
            );
            $ticket_supports = array( 'title', 'custom-fields' );
            if ( !isset( $_GET['post'] ) ){
                $ticket_supports[] = 'editor';
            }
            
            $args = array(
                'labels'                => $labels,
                'description'           => __( 'All your customer service tickets','kanzu-support-desk' ),
                'public'                => true,
                'exclude_from_search'   => true, 
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'show_in_nav_menus'     => false, 
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'ksd_ticket', 'with_front' => false ),
                'menu_position'         => 25,
                'has_archive'           => true,
                //'capabilities'        => @TODO Define these
                'menu_icon'             => 'dashicons-groups',
                'supports'              => $ticket_supports,
                'taxonomies'            => array( 'post_tag' )
            );
            register_post_type( 'ksd_ticket', $args ); 
            //@Change type of 'tags' to 'products'. Tags are categories that aren't heirarchical
            //Add the 'Product' tag to the ticket post type
           // Add new taxonomy, NOT hierarchical (like tags)
            $product_labels = array(
                    'name'                       => _x( 'Products', 'taxonomy general name' , 'kanzu-support-desk' ),
                    'singular_name'              => _x( 'Product', 'taxonomy singular name' , 'kanzu-support-desk' ),
                    'search_items'               => __( 'Search Products' , 'kanzu-support-desk' ),
                    'popular_items'              => __( 'Popular Products' , 'kanzu-support-desk' ),
                    'all_items'                  => __( 'All Products' , 'kanzu-support-desk' ),
                    'parent_item'                => null,
                    'parent_item_colon'          => null,
                    'edit_item'                  => __( 'Edit Product' , 'kanzu-support-desk' ),
                    'update_item'                => __( 'Update Product' , 'kanzu-support-desk' ),
                    'add_new_item'               => __( 'Add New Product' , 'kanzu-support-desk' ),
                    'new_item_name'              => __( 'New Product Name' , 'kanzu-support-desk' ),
                    'separate_items_with_commas' => __( 'Separate products with commas' , 'kanzu-support-desk' ),
                    'add_or_remove_items'        => __( 'Add or remove products' , 'kanzu-support-desk' ),
                    'choose_from_most_used'      => __( 'Choose from the most used products' , 'kanzu-support-desk' ),
                    'not_found'                  => __( 'No products found.' , 'kanzu-support-desk' ),
                    'menu_name'                  => __( 'Products' , 'kanzu-support-desk' )
            );

            $product_args = array(
                    'hierarchical'          => false,
                    'labels'                => $product_labels,
                    'show_ui'               => true,
                    'show_admin_column'     => true,
                    'update_count_callback' => '_update_post_term_count',
                    'query_var'             => true,
                    'rewrite'               => array( 'slug' => 'product' ),
            );

            register_taxonomy( 'product', 'ksd_ticket', $product_args );
                            
            /*----Replies -----*/
            $reply_labels = array(
                'name'                  => _x('Replies', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x('Reply', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __('Add New', 'kanzu-support-desk'),
                'add_new_item'          => __('Add New Reply', 'kanzu-support-desk'),
                'edit_item'             => __('Edit Reply', 'kanzu-support-desk'),
                'new_item'              => __('New Reply', 'kanzu-support-desk'),
                'all_items'             => __('All Replies', 'kanzu-support-desk'),
                'view_item'             => __('View Reply', 'kanzu-support-desk'),
                'search_items'          => __('Search Replies', 'kanzu-support-desk'),
                'not_found'             => __('No Replies found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __('No Replies found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __('Replies', 'kanzu-support-desk')
            );

            $reply_args = array(
                'labels'                => $reply_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'supports'              => array( 'editor', 'custom-fields' ),
                'can_export'            => true
            );
            register_post_type( 'ksd_reply', $reply_args );
            
            /*----Private Notes -----*/
            $private_note_labels = array(
                'name'                  => _x('Private Notes', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x('Private Note', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __('Add New', 'kanzu-support-desk'),
                'add_new_item'          => __('Add New Private Note', 'kanzu-support-desk'),
                'edit_item'             => __('Edit Private Note', 'kanzu-support-desk'),
                'new_item'              => __('New Private Note', 'kanzu-support-desk'),
                'all_items'             => __('All Private Notes', 'kanzu-support-desk'),
                'view_item'             => __('View Private Note', 'kanzu-support-desk'),
                'search_items'          => __('Search Private Notes', 'kanzu-support-desk'),
                'not_found'             => __('No Private Notes found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __('No Private Notes found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __('Private Notes', 'kanzu-support-desk')
            );

            $private_note_args = array(
                'labels'                => $private_note_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'supports'              => array( 'editor', 'custom-fields' ),//@TODO Change this. None of these are needed
                'can_export'            => true
            );
            register_post_type( 'ksd_private_note', $private_note_args );
            
            /*----Ticket Activity -----*/
            //Holds changes to ticket info as events
            $ticket_activity_labels = array(
                'name'                  => _x('Ticket Activity', 'post type general name', 'kanzu-support-desk'),
                'singular_name'         => _x('Ticket Activity', 'post type singular name', 'kanzu-support-desk'),
                'add_new'               => __('Add New', 'kanzu-support-desk'),
                'add_new_item'          => __('Add New Ticket Activity', 'kanzu-support-desk'),
                'edit_item'             => __('Edit Ticket Activity', 'kanzu-support-desk'),
                'new_item'              => __('New Ticket Activity', 'kanzu-support-desk'),
                'all_items'             => __('All Ticket Activities', 'kanzu-support-desk'),
                'view_item'             => __('View Ticket Activity', 'kanzu-support-desk'),
                'search_items'          => __('Search Ticket Activities', 'kanzu-support-desk'),
                'not_found'             => __('No Ticket Activity found', 'kanzu-support-desk'),
                'not_found_in_trash'    => __('No Ticket Activity found in Trash', 'kanzu-support-desk'),
                'parent_item_colon'     => '',
                'menu_name'             => __('Ticket Activity', 'kanzu-support-desk')
            );

            $ticket_activity_args = array(
                'labels'                => $ticket_activity_labels,
                'public'                => false,                
                'query_var'             => false,
                'rewrite'               => false,
                'show_ui'               => false,
                'map_meta_cap'          => true,
                'supports'              => array( 'editor', 'custom-fields' ),//@TODO Change this. None of these are needed
                'can_export'            => true
            );
            register_post_type( 'ksd_ticket_activity', $ticket_activity_args );
            
            //@TODO Use custom fields for tkt_cc,tkt_is_read and rep_cc
        }
        
        /**
         * Add custom KSD ticket statuses
         * @since 2.0.0
         */
        public function custom_ticket_statuses(){
            register_post_status( 'open', array(
                'label'                     => _x( 'Open', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>' )
                ) );
            register_post_status( 'pending', array(
                'label'                     => _x( 'Pending', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>' )
                ) );
            register_post_status( 'resolved', array(
                'label'                     => _x( 'Resolved', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'exclude_from_search'       => true,
                'label_count'               => _n_noop( 'Resolved <span class="count">(%s)</span>', 'Resolved <span class="count">(%s)</span>' )
                ) );
            register_post_status( 'new', array(
                'label'                     => _x( 'Resolved', 'status of a ticket', 'kanzu-support-desk' ),
                'public'                    => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'exclude_from_search'       => true,
                'label_count'               => _n_noop( 'Resolved <span class="count">(%s)</span>', 'Resolved <span class="count">(%s)</span>' )
                ) );
        }
        
        /**
         * Log a new ticket. We use the backend logic
         */
        public function log_new_ticket(){
            //First check the CAPTCHA to prevent spam
             $settings = Kanzu_Support_Desk::get_settings();
            if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ){
                $recaptcha_response = $this->verify_recaptcha();
                if( $recaptcha_response['error'] ){
                    echo json_encode( $recaptcha_response['message'] );
                    die();//This is important for WordPress AJAX
                }
            }
            //Use the admin side logic to do the ticket logging
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->log_new_ticket( $_POST );
        }
        
        /**
         * Register a user
         * @since 2.0.0
         */
        public function register_user(){
            //Check the nonce
            if ( ! wp_verify_nonce( $_POST['register-nonce'], 'ksd-register' ) ){
                  die ( __('Busted!','kanzu-support-desk') );                         
            }
            //@TODO Currently accepts defaults ('Last Name''First Name') Disable this
            //Perform server-side validation
            $first_name = sanitize_text_field( $_POST['ksd_cust_firstname'] );
            $last_name  = sanitize_text_field( $_POST['ksd_cust_lastname'] );
            $username   = sanitize_text_field( $_POST['ksd_cust_username'] );
            $email      = sanitize_text_field( $_POST['ksd_cust_email'] );
            $password   = sanitize_text_field( $_POST['ksd_cust_password'] );
            
            //@TODO Check if WP registrations are enabled
            
            //Check that we have all required fields
            if ( empty ( $first_name ) || empty ( $username ) || empty ( $email ) || empty ( $password ) ) {
                $response = __( 'Sorry, a required field is missing. Please fill in all fields.', 'kanzu-support-desk' );
                echo ( json_encode( $response ));
                die();
            }  
            //Check that the fields are valid
            if ( ( strlen ( $first_name ) || strlen( $last_name ) || strlen( $username ) ) < 2  ) {
                $response = __( 'Sorry, the name provided should be at least 2 characters long.', 'kanzu-support-desk' );
                echo ( json_encode( $response ));
                die();
            }
            if ( !is_email( $email ) ) {
                $response = __( 'Sorry, the email you provided is not valid.', 'kanzu-support-desk' );
                echo ( json_encode( $response ));
                die();           
            }
            //Check if the username is new
            if ( username_exists( $username ) ){
                $response = __( 'Sorry, that username is already taken. Please choose another one', 'kanzu-support-desk' );
                echo ( json_encode( $response ));
                die();       
            }   
            //Yay! Register the user
            $userdata = array(
                        'user_login'    => $username,
                        'user_pass'     => $password,  
                        'user_email'    => $email,
                        'display_name'  => $first_name.' '.$last_name,
                        'first_name'    => $first_name,
                        'role'          => 'ksd_customer'
            );
            if( !empty( $last_name )){//Add the last name if it was provided
                $userdata['last_name']  =   $last_name;
            }
            try {
                $user_id = wp_insert_user( $userdata ) ;                
                if( ! is_wp_error( $user_id ) ) {//Successfully created the user
                    $login_url = sprintf ( '<a href="%1$s" title="%2$s">%3$s</a>', wp_login_url(), __( 'Login', 'kanzu-support-desk' ), __( 'Click here to login', 'kanzu-support-desk' ) ) ;
                    $response = sprintf ( __( 'Your account has been successfully created! Redirecting you shortly...or %s', 'kanzu-support-desk' ), $login_url );
                    
                    //Sign in the user
                    $creds                  = array();
                    $creds['user_login']    = $username;
                    $creds['user_password'] = $password;
                    $creds['remember']      = false;
                    wp_signon( $creds, false );//We don't check whether this happens                
        
                    echo ( json_encode( $response ));
                    die();   
                }
                else{//We had an error 
                    $error_message = __( 'Sorry, but something went wrong. Please retry or reload the page.', 'kanzu-support-desk');
                   if( isset( $user_id->errors['existing_user_email'] ) ){//The email's already in use. Ask the user to reset their password  
                       $lost_password_url = sprintf ( '<a href="%1$s" title="%2$s">%3$s</a>', wp_lostpassword_url(), __( 'Lost Password', 'kanzu-support-desk' ), __( 'Click here to reset your password', 'kanzu-support-desk' ) ) ;
                       $error_message = sprintf( __( 'Sorry, that email address is already used! %s', 'kanzu-support-desk' ), $lost_password_url );
                   }
                        throw new Exception( $error_message, -1);
                }            
             }catch( Exception $e){
                $response = array(
                    'error'=> array( 'message' => $e->getMessage() , 'code'=>$e->getCode())
                );
                echo json_encode($response);	
                die();// IMPORTANT: don't leave this out
            }  
            
        }
        
        /**
         * Check, using Google reCAPTCHA, whether the submitted ticket was sent
         * by a human
         */
        private function verify_recaptcha(){
                $response = array();   
                $response['error'] = true;//Pre-assume an error is going to occur
                if( empty( $_POST['g-recaptcha-response'] )){
                   $response['message'] = __( "ERROR - Sorry, the \"I'm not a robot\" field is required. Please refresh this page & check it.", "kanzu-support-desk");
                   return $response;
                }
                $settings = Kanzu_Support_Desk::get_settings();
                $recaptcha_args = array(
                    'secret'    =>  $settings['recaptcha_secret_key'],
                    'response'  =>  $_POST['g-recaptcha-response']
                );
		$google_recaptcha_response = wp_remote_get( esc_url_raw ( add_query_arg( $recaptcha_args, 'https://www.google.com/recaptcha/api/siteverify' ) ), array( 'sslverify' => false ) );
		 if ( is_wp_error( $google_recaptcha_response ) ) { 
                     $response['message'] = __( 'Sorry, an error occurred. Please retry', 'kanzu-support-desk');
                     return $response;
                 }
                $recaptcha_text = json_decode( wp_remote_retrieve_body( $google_recaptcha_response ) );
                if ( $recaptcha_text->success ){
                     $response['error'] = false;
                     return $response;
                }
                else{
                    switch( $recaptcha_text->{'error-codes'}[0] ){
                        case 'missing-input-secret':
                            $response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'invalid-input-secret':
                            $response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA secret key. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'missing-input-response':
                            $response['message'] = __( 'Sorry, an error occurred due to a missing reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        case 'invalid-input-response':
                            $response['message'] = __( 'Sorry, an error occurred due to an invalid or malformed reCAPTCHA input response. Please refresh the page and retry.', 'kanzu-support-desk');
                            break;
                        default: 
                            $response['message'] = $settings['recaptcha_error_message'];
                    }
                    return $response;
                }
        }
        
        /**
         * Redirect user after successful login.
         *
         * @param string $redirect_to URL to redirect to.
         * @param string $request URL the user is coming from.
         * @param object $user Logged user's data.
         * @return string
         * @since 2.0.0
         */
        public function do_login_redirect( $redirect_to, $request, $user ) {                
                global $user;//is there a user to check?
                if ( isset( $user->roles ) && is_array( $user->roles ) ) {
                        //check for admins
                        if ( in_array( 'ksd_customer', $user->roles ) ) {    
                                //@TODO Check $request and send customer to 'My Tickets' or to 'Create new ticket'
                                $current_settings = Kanzu_Support_Desk::get_settings();//Get current settings                                
                                return get_permalink( $current_settings['page_submit_ticket'] ); //redirect customers to the create tickets page
                        } 
                }  
            return $redirect_to;                        
        }        
        
        
}
endif;

return new KSD_Public();
