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
    }
    
    /**
     * Generate the ticket form that's displayed in the front-end
     * NB: We only show the form if you enabled the 'show_support_tab' option
     */
    public function generate_new_ticket_form(){
        $settings = Kanzu_Support_Desk::get_settings();
        if( "yes" == $settings['show_support_tab'] ) {
            include_once( KSD_PLUGIN_DIR .  'includes/frontend/views/html-frontend-new-ticket.php' );
        }
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
            wp_localize_script( KSD_SLUG . '-frontend-js', 'ksd_frontend' , array( 'ajax_url' => admin_url( 'admin-ajax.php') ) );            
        }
        
        /**
         * Log a new ticket. We use the backend logic
         */
        public function log_new_ticket(){
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->log_new_ticket();
        }
}
endif;

return new KSD_FrontEnd();

