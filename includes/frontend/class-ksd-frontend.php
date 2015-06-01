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

if ( ! class_exists( 'KSD_FrontEnd' ) ) : 
    
class KSD_FrontEnd {
    
    public function __construct() {
        
        //Enqueue styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        //Add form for new ticket to the footer
        add_action( 'wp_footer', array( $this , 'generate_new_ticket_form' ));
        //Handle AJAX
        add_action( 'wp_ajax_nopriv_ksd_log_new_ticket', array( $this, 'log_new_ticket' ));
        //Add a shortcode for the front-end form
        add_shortcode( 'ksd_support_form', array( $this,'form_short_code' ) );
    }
    
    /**
     * Generate the ticket form that's displayed in the front-end
     * NB: We only show the form if you enabled the 'show_support_tab' option
     */
    public function generate_new_ticket_form(){
        $settings = Kanzu_Support_Desk::get_settings();
        if( "yes" == $settings['show_support_tab'] ) {?>
            <button id="ksd-new-ticket-frontend"><?php _e( 'Support', 'kanzu-support-desk'); ?></button><?php
            $form_position_class = "ksd-form-hidden-tab";//Used as a class to style the form
            include( KSD_PLUGIN_DIR .  'includes/frontend/views/html-frontend-new-ticket.php' );
        }
    }
   
    /**
     * Display a form wherever shortcode [ksd-form] is used
     */
   public function form_short_code(){
       $settings = Kanzu_Support_Desk::get_settings();
       $form_position_class = "ksd-form-short-code";//Used as a class to style the form
       include( KSD_PLUGIN_DIR .  'includes/frontend/views/html-frontend-new-ticket.php' );
   }     
    
    	/**
	 * Register and enqueue front-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_frontend_styles() {	
		wp_enqueue_style( KSD_SLUG .'-frontend-css', KSD_PLUGIN_URL . 'assets/css/ksd-frontend.css' , array() , KSD_VERSION );
        }
        
        /**
         * Enqueue scripts used solely at the front-end
         * @since 1.0.0
         */
        public function enqueue_frontend_scripts() {	
            wp_enqueue_script( KSD_SLUG . '-frontend-js', KSD_PLUGIN_URL .  'assets/js/ksd-frontend.js' , array( 'jquery', 'jquery-ui-core' ), KSD_VERSION );
            $msd_grecaptcha_error = sprintf( __( 'Please check the <em>%s</em> checkbox and wait for it to complete loading', 'kanzu-support-desk'), "I'm not a robot" );
            wp_localize_script( KSD_SLUG . '-frontend-js', 'ksd_frontend' , array( 'ajax_url' => admin_url( 'admin-ajax.php'), 'msg_gcaptcha_error' => $msd_grecaptcha_error ) );    
            //Check whether enable_recaptcha is checked. @TODO Don't retrieve settings again. Use same set of settings
            $settings = Kanzu_Support_Desk::get_settings();
            if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ){
               wp_enqueue_script( KSD_SLUG . '-frontend-grecaptcha', '//www.google.com/recaptcha/api.js', array(), KSD_VERSION );  
            }
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
            $ksd_admin->log_new_ticket();
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
		$google_recaptcha_response = wp_remote_get( add_query_arg( $recaptcha_args, 'https://www.google.com/recaptcha/api/siteverify' ), array( 'sslverify' => false ) );
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
}
endif;

return new KSD_FrontEnd();

