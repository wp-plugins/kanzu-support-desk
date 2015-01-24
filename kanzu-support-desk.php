<?php
/**
 * Plugin Name:       Kanzu Support Desk
 * Plugin URI:        http://kanzucode.com/kanzu-support-desk
 * Description:       All-in-one support desk (ticketing) solution for your WordPress site
 * Version:           1.2.0
 * Author:            Kanzu Code
 * Author URI:        http://kanzucode.com
 * Text Domain:       kanzu-support-desk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Kanzu_Support_Desk' ) ) :


final class Kanzu_Support_Desk {

	/**
	 * @var string
	 */
	public $version = '1.2.0';
	
	
	/**
	 * @var string
	 * Note that it should match the Text Domain file header in this file
	 */
	public $ksd_slug = 'kanzu-support-desk';
        
        /**
         * The options name in the WP Db. We store all
         * KSD options using a single options key
         */
        private $ksd_options_name = "kanzu_support_desk";
	
	/**
	 * @var Kanzu_Support_Desk The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;
	
	/**
	 * Main KanzuSupport Instance
	 *
	 * Ensures only one instance of KanzuSupport is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return KanzuSupport - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzu-support-desk' ), $this->version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'kanzu-support-desk' ), $this->version );
	}

	public function __construct(){
	//Define constants
	$this->define_constants();

	//Include required files
	$this->includes();
	
        //Set-up actions and filters
	$this->setup_actions();
        
	/*
	 * Register hooks that are fired when the plugin is activated  
	 * When the plugin is deleted, the uninstall.php file is loaded.
	 */
	register_activation_hook( __FILE__, array( 'KSD_Install', 'activate' ) );
        
        //Register a de-activation hook
        register_deactivation_hook( __FILE__, array( 'KSD_Install', 'deactivate' ) );
	
        }
	
	/**
	 * Define Kanzu Support Constants
	 */
	private function define_constants() {

             if ( ! defined( 'KSD_VERSION' ) ) {                
		define( 'KSD_VERSION', $this->version );
             }
            if ( ! defined( 'KSD_SLUG' ) ) {                
                define( 'KSD_SLUG', $this->ksd_slug );           
            }                
            if ( ! defined( 'KSD_PLUGIN_DIR' ) ) {
            define( 'KSD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }                
            if ( ! defined( 'KSD_PLUGIN_URL' ) ) {
                define( 'KSD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
             } 
            if ( ! defined( 'KSD_PLUGIN_FILE' ) ) {
                define( 'KSD_PLUGIN_FILE',  __FILE__ );
            }             
            if ( ! defined( 'KSD_OPTIONS_KEY' ) ) {
                define( 'KSD_OPTIONS_KEY',  $this->ksd_options_name );
            } 

	}
	
	/**
	 * Include all the files we need
	 */
	private function includes() {
		//Do installation-related work
		include_once( 'includes/class-ksd-install.php' );
		
		//Dashboard and Administrative Functionality 
		if ( is_admin() ) {
			require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );
		}
                //The front-end
                require_once( KSD_PLUGIN_DIR .  'includes/frontend/class-ksd-frontend.php' );
		}
		
	

	/**
	 * Load the plugin text domain for translation.
	 * .mo files should be placed in /languages/ and should be named {KSD_SLUG}-{locale}.mo
         *  e.g. For Danish, whose locale is Danish is 'da_DK',
         * the MO and PO files should be named kanzu-support-desk-da_DK.mo and kanzu-support-desk-da_DK.po
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
	
		$locale = apply_filters( 'plugin_locale', get_locale(), KSD_SLUG );

		load_textdomain( KSD_SLUG, trailingslashit( WP_LANG_DIR ) . KSD_SLUG . '/' . KSD_SLUG . '-' . $locale . '.mo' );
		load_plugin_textdomain( KSD_SLUG, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
	
 

	/**
	 * Enqueue scripts used in both the front and back end
	 *
	 * @since    1.0.0
	 */
	public function enqueue_general_scripts() {		
            //For form validation
            wp_enqueue_script( KSD_SLUG . '-validate', KSD_PLUGIN_URL . 'assets/js/jquery.validate.min.js' , array("jquery"), "1.13.0" ); 
        }
        
         /**
          * Get all settings. Settings are stored as an array
          * with key KSD_OPTIONS_KEY
          */
         public static function get_settings(){
             return get_option( KSD_OPTIONS_KEY );
         }
         
         /**
          * Update settings. 
          * @TODO Change this to use a filter
          */
         public static function update_settings( $updated_settings ){             
             return update_option( KSD_OPTIONS_KEY, $updated_settings );
         }

	
	/**
	 * Setup Kanzu Support's actions
	 * @since    1.0.0
	 */
	private function setup_actions(){	

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

                //Load scripts used in both the front and back ends
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_general_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_general_scripts' ) ); 
                
                //Share the plugin's settings with add-ons
                add_filter( 'ksd_get_settings', array( $this, 'get_settings') );
                
               //Handle logging of new tickets & replies initiated by add-ons.   
                add_action( 'ksd_log_new_ticket', array( $this, 'do_log_new_ticket' ) );
	}
        
        /**
         * Log new tickets & replies initiated by add-ons
         * We hand this over to the admin-end logic which has
         * all the functions needed to do this smoothly
         * @param Object $new_ticket The new ticket or reply object
         */
        public function do_log_new_ticket( $new_ticket ){
            require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->do_log_new_ticket( $new_ticket );
        }

	/**
	* Added to write custom debug messages to the debug log (wp-content/debug.log). You
	* need to turn debug on for this to work
	*/
	public static function kanzu_support_log_me($message) {
            if (WP_DEBUG === true) {
                if (is_array($message) || is_object($message)) {
                    error_log(print_r($message, true));
                } else {
                    error_log($message);
                }
                            }
        } 
 }

    /**
     * The main function responsible for returning the one true Kanzu_Support_Desk Instance
     * to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     * Example: <?php $ksd = Kanzu_Support_Desk(); ?>
     *
     * @return The one true Kanzu_Support_Desk Instance
     */
    function kanzu_support_desk() {
            return Kanzu_Support_Desk::instance();
    }

 
kanzu_support_desk();


endif; // class_exists check
