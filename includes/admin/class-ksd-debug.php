<?php

/**
 * Generate KSD Debug information
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('KSD_Debug')) :

    class KSD_Debug {
    	
        /**
	 * Instance of this class.
	 *
	 * @since    1.7.0
	 *
	 * @var      object
	 */
	protected static $instance = null;   

        	/**
	 * Return an instance of this class.
	 *
	 * @since     1.7.0
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
         * Get debug info
         *
         * @since       1.7.0
         * @access      private
         * @global      object $wpdb Used to query the database using the WordPress Database API
         * @return      string $return A string containing the info to output
         * This code is largely from EDD 2.3.9 with several adjustments specific to KSD's implementation
         */
        public function retrieve_debug_info(){
	
            global $wpdb;

            if( !class_exists( 'Browser' ) ){
                    require_once KSD_PLUGIN_DIR.  'includes/libraries/browser.php';
            }

            $browser = new Browser();

            // Get theme info
            if( get_bloginfo( 'version' ) < '3.4' ) {
                    $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
                    $theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
            } else {
                    $theme_data = wp_get_theme();
                    $theme      = $theme_data->Name . ' ' . $theme_data->Version;
            }

            // Try to identify the hosting provider
            $host = $this->get_host(); 

            $return  = '### Begin System Info ###' . "\n\n";

            // Start with the basics...
            $return .= '-- Site Info' . "\n\n";
            $return .= 'Site URL:                 ' . site_url() . "\n";
            $return .= 'Home URL:                 ' . home_url() . "\n";
            $return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

            // Can we determine the site's host?
            if( $host ) {
                    $return .= "\n" . '-- Hosting Provider' . "\n\n";
                    $return .= 'Host:                     ' . $host . "\n";
            }

            // The local users' browser information, handled by the Browser class
            $return .= "\n" . '-- User Browser' . "\n\n";
            $return .= $browser;


            // WordPress configuration
            $return .= "\n" . '-- WordPress Configuration' . "\n\n";
            $return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
            $return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
            $return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
            $return .= 'Active Theme:             ' . $theme . "\n";
            $return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

            // Only show page specs if frontpage is set to 'page'
            if( get_option( 'show_on_front' ) == 'page' ) {
                    $front_page_id = get_option( 'page_on_front' );
                    $blog_page_id = get_option( 'page_for_posts' );

                    $return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
                    $return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
            }

            // Make sure wp_remote_post() is working
            $request['cmd'] = '_notify-validate';

            $params = array(
                    'sslverify'     => false,
                    'timeout'       => 60,
                    'user-agent'    => 'KSD/' . KSD_VERSION,
                    'body'          => $request
            );

            $response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

            if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
                    $WP_REMOTE_POST = 'wp_remote_post() works';
            } else {
                    $WP_REMOTE_POST = 'wp_remote_post() does not work';
            }

            $return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
            $return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
            $return .= 'Admin AJAX:               ' . ( $this->test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
            $return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
            $return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
            $return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

            //KSD configuration
            $settings = Kanzu_Support_Desk::get_settings();//Current settings
            $return .= "\n" . '-- KSD Configuration' . "\n\n";
            $return .= 'Version:                    ' . KSD_VERSION . "\n";
            $return .= 'DB version:                 ' . $settings['kanzu_support_db_version'] . "\n";
            $return .= 'Enable auto-reply:          ' . $settings['enable_new_tkt_notifxns'] . "\n";
            $return .= 'Notify on new ticket:       ' . $settings['enable_notify_on_new_ticket'] . "\n";
            $return .= 'Recency Definition:         ' . $settings['recency_definition'] . "\n";
            $return .= 'Show support tab:           ' . $settings['show_support_tab'] . "\n";
            $return .= 'Support Button Text:        ' . $settings['support_button_text'] . "\n";
            $return .= 'Tour Mode:                  ' . $settings['tour_mode'] . "\n";
            $return .= 'Enable ReCAPTCHA:           ' . $settings['enable_recaptcha'] . "\n";
            $return .= 'Enable usage tracking:      ' . $settings['enable_anonymous_tracking'] . "\n";
            $return .= 'Auto-assign tickets to:     ' . $settings['auto_assign_user'] . "\n";
            $return .= 'Ticket Management Roles:    ' . $settings['ticket_management_roles'] . "\n";

            //KSD tickets & agents
            $return .= "\n" . '-- KSD Tickets & Users' . "\n\n";
            $return .= 'Ticket information:      ' . $this->get_ticket_info() . "\n"; 
            $return .= 'User information:       ' . $this->get_user_information() . "\n";

            //Must-use plugins
            if ( ! function_exists( 'get_mu_plugins' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $muplugins = get_mu_plugins();
            if( count( $muplugins > 0 ) ) {
                $return .= "\n" . '-- Must-Use Plugins' . "\n\n";

                foreach( $muplugins as $plugin => $plugin_data ) {
                    $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
                }
            }

            // WordPress active plugins
            $return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

            $plugins = get_plugins();
            $active_plugins = get_option( 'active_plugins', array() );

            foreach( $plugins as $plugin_path => $plugin ) {
                    if( !in_array( $plugin_path, $active_plugins ) )
                            continue;

                    $return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
            }

            // WordPress inactive plugins
            $return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

            foreach( $plugins as $plugin_path => $plugin ) {
                    if( in_array( $plugin_path, $active_plugins ) )
                            continue;

                    $return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
            }

            if( is_multisite() ) {
                    // WordPress Multisite active plugins
                    $return .= "\n" . '-- Network Active Plugins' . "\n\n";

                    $plugins = wp_get_active_network_plugins();
                    $active_plugins = get_site_option( 'active_sitewide_plugins', array() );

                    foreach( $plugins as $plugin_path ) {
                            $plugin_base = plugin_basename( $plugin_path );

                            if( !array_key_exists( $plugin_base, $active_plugins ) )
                                    continue;

                            $plugin  = get_plugin_data( $plugin_path );
                            $return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
                    }
            }

            // Server configuration (really just versioning)
            $return .= "\n" . '-- Webserver Configuration' . "\n\n";
            $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
            $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
            $return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

            // PHP configs... now we're getting to the important stuff
            $return .= "\n" . '-- PHP Configuration' . "\n\n";
            $return .= 'Safe Mode:                ' . ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' . "\n" );
            $return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
            $return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
            $return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
            $return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
            $return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
            $return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
            $return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

            // PHP extensions and such
            $return .= "\n" . '-- PHP Extensions' . "\n\n";
            $return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
            $return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
            $return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
            $return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

            // Session stuff
            if ( class_exists( 'EDD' ) ){
                $return .= "\n" . '-- Session Configuration' . "\n\n";
                $return .= 'EDD Use Sessions:         ' . ( defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS ? 'Enforced' : ( EDD()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ) ) . "\n";
                $return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

                // The rest of this is only relevant is session is enabled
                if( isset( $_SESSION ) ) {
                        $return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
                        $return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
                        $return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
                        $return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
                        $return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
                }
            }
            $return  = apply_filters( 'ksd_debug_info', $return );

            $return .= "\n" . '### End System Info ###';

            return $return;
        }

        /**
         * Get user host
         *
         * Returns the webhost this site is using if possible
         *
         * @since 1.7.0
         * @return mixed string $host if detected, false otherwise
         * This code is retrieved as is from EDD v2.3.9
         */
        private function get_host() {
            $host = false;

            if (defined('WPE_APIKEY')) {
                $host = 'WP Engine';
            } elseif (defined('PAGELYBIN')) {
                $host = 'Pagely';
            } elseif (DB_HOST == 'localhost:/tmp/mysql5.sock') {
                $host = 'ICDSoft';
            } elseif (DB_HOST == 'mysqlv5') {
                $host = 'NetworkSolutions';
            } elseif (strpos(DB_HOST, 'ipagemysql.com') !== false) {
                $host = 'iPage';
            } elseif (strpos(DB_HOST, 'ipowermysql.com') !== false) {
                $host = 'IPower';
            } elseif (strpos(DB_HOST, '.gridserver.com') !== false) {
                $host = 'MediaTemple Grid';
            } elseif (strpos(DB_HOST, '.pair.com') !== false) {
                $host = 'pair Networks';
            } elseif (strpos(DB_HOST, '.stabletransit.com') !== false) {
                $host = 'Rackspace Cloud';
            } elseif (strpos(DB_HOST, '.sysfix.eu') !== false) {
                $host = 'SysFix.eu Power Hosting';
            } elseif (strpos($_SERVER['SERVER_NAME'], 'Flywheel') !== false) {
                $host = 'Flywheel';
            } else {
                // Adding a general fallback for data gathering
                $host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
            }

            return $host;
        }
        
        /**
         * Check if AJAX works as expected
         *
         * @since 1.7.0
         * @return bool True if AJAX works, false otherwise
         * Code adapted and modified from EDD v2.3.9
         */
        private function test_ajax_works() {

            // Check if the Airplane Mode plugin is installed
            if ( class_exists('Airplane_Mode_Core') ) {

                $airplane = Airplane_Mode_Core::getInstance();

                if ( method_exists($airplane, 'enabled') ) {

                    if ($airplane->enabled()) {
                        return true;
                    }
                } else {

                    if ( $airplane->check_status() == 'on' ) {
                        return true;
                    }
                }
            }

            add_filter( 'block_local_requests', '__return_false' );

            if ( get_transient('_ksd_ajax_works') ) {
                return true;
            }

            $params = array(
                'sslverify' => false,
                'timeout'   => 30,
                'body'      => array(
                'action'    => 'ksd_test_ajax'
                )
            );

            $ajax = wp_remote_post( admin_url( 'admin-ajax.php' ), $params );
            $works = true;

            if ( is_wp_error($ajax) ) {

                $works = false;
            } else {

                if ( empty( $ajax['response'] ) ) {
                    $works = false;
                }

                if ( empty( $ajax['response']['code']) || 200 !== (int) $ajax['response']['code'] ) {
                    $works = false;
                }

                if ( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
                    $works = false;
                }

                if ( !isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
                    $works = false;
                }
            }

            if ( $works ) {
                set_transient('_ksd_ajax_works', '1', DAY_IN_SECONDS);
            }

            return $works;
        }
        
        /**
         * Get ticket-related information
         * @since 1.7.0
         */
        private function get_ticket_info(){
            require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );            
            $ksd_admin =  KSD_Admin::get_instance();
            $ksd_admin->do_admin_includes();
            $tickets = new KSD_Tickets_Controller();	
            $ticket_stats = $tickets->get_ticket_count_by_status();
            $total_tickets = 0;
            $ticket_status_info = "";
            foreach  ( $ticket_stats as $stat ){
                $total_tickets+=$stat->count;
                $ticket_status_info.= "{$stat->count} are {$stat->post_status}, ";//Not internationalized because this info is for sending to Kanzu Code
            }
            return "{$total_tickets} total tickets, {$ticket_status_info}";
        }
        
        private function get_user_information(){
            $result = count_users();
            $user_info = "{$result['total_users']} total users";
            
            foreach ( $result['avail_roles'] as $role => $count ):
                $user_info.= ", {$count} are {$role}s";
            endforeach;
            
            return $user_info;
        }

    }

    endif;

return new KSD_Debug();
