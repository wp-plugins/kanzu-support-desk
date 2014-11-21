<?php
/**
 * Retrieves new mail and logs a ticket 
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'KSD_Deamon' ) ) :

class KSD_Deamon {
    
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;   

        
    private $transient         = null;
    
    public function __construct(){
        $this->transient = 'ksd_deamon_transient';
        
        if( php_sapi_name() !== 'cli' ) {
            die( __( "Must be run from commandline." ) ) ;
        }
        set_error_handler( array( $this, 'error_handler' ), E_ERROR & ~E_DEPRECATED );
        set_exception_handler( array( $this, 'exception_handler' ) );
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
    
    public function run (){
        
        $transient = $this->transient;
        $value     = $this->transient;
        $expiration= 60 * 60 * 1 ; //1 hr
        
        if (  false === get_transient( $transient ) ){
            set_transient( $transient, $value, $expiration );

            do_action( 'ksd_run_deamon' );

            delete_transient( $transient );            
        }else{
            _e( 'Script still running.' );
        } 
    }
    
    
    public function error_handler( $errno, $errstr, $errfile, $errline, $errcontext ) {
       throw new Exception( $errstr );
    }

    public function exception_handler( $e ) {
        echo $e;
        delete_transient('ksd_deamon_transient');
    }
            

}

endif;
$ksd_deamon = KSD_Deamon::get_instance();

try{
    $ksd_deamon->run();
}  catch (Exception $e){
    delete_transient( 'ksd_deamon_transient' );
}
