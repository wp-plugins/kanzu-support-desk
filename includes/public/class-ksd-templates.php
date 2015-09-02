<?php
/**
 * All work related to Kanzu Support Desk templates
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Templates' ) ) : 
    

class KSD_Templates {
   public function __construct() {
       add_action( 'ksd_after_ticket_content', array( $this, 'append_ticket_replies') );
   }
     /**
      * Adds KSD theme support to any active WordPress theme
      *
      * @since 2.0.0
      * Taken from BBPress
      *
      * @param string $slug
      * @param string $name Optional. Default null
      * @uses locate_template()
      * @uses load_template()
      * @uses get_template_part()
      */
     public function get_template_part( $slug, $name = null ) {

             // Execute code for this part
             do_action( 'get_template_part_' . $slug, $slug, $name );

             // Setup possible parts
             $templates = array();
             if ( isset( $name ) )
                     $templates[] = $slug . '-' . $name . '.php';
             $templates[] = $slug . '.php';

             // Allow template parst to be filtered
             $templates = apply_filters( 'ksd_get_template_part', $templates, $slug, $name );

             // Return the part that is found
             return $this->locate_template( $templates, true, false );
     }

     /**
      * Retrieve the name of the highest priority template file that exists.
      *
      * Searches in the child theme before parent theme so that themes which
      * inherit from a parent theme can just overload one file. If the template is
      * not found in either of those, it looks in the theme-compat folder last.
      *
      * Taken from BBPress
      * @since 2.0.0
      *
      * @param string|array $template_names Template file(s) to search for, in order.
      * @param bool $load If true the template file will be loaded if it is found.
      * @param bool $require_once Whether to require_once or require. Default true.
      *                            Has no effect if $load is false.
      * @return string The template filename if one is located.
      */
     private function locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach( $this->get_theme_template_paths() as $template_path ) {

			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
        }
        
        /**
        * Returns a list of paths to check for template locations
        * Taken from EDD
        * @since 2.0.0
        * @return mixed|void
        */
       private function get_theme_template_paths() {

               $template_dir = 'ksd_templates';

               $file_paths = array(
                       1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
                       10 => trailingslashit( get_template_directory() ) . $template_dir,
                       100 => $this->get_templates_dir()
               );

               $file_paths = apply_filters( 'ksd_template_paths', $file_paths );

               // sort the file paths based on priority
               ksort( $file_paths, SORT_NUMERIC );

               return array_map( 'trailingslashit', $file_paths );
       }

        /**
         * Returns the path to KSD's templates directory
         *
         * @since 2.0.0
         * @return string
         */
        private function get_templates_dir() {
                return KSD_PLUGIN_DIR . 'templates/'.$this->get_active_theme();
        }
        
        /**
         * Get the active KSD theme
         * Active theme can be changed by using the ksd_active_theme filter
         * By default, the active theme is, well, default. :-)
         * @since 2.0.0
         */
        private function get_active_theme(){
            return apply_filters( 'ksd_active_theme', 'default' );
        }
 
        
        /**
         * Append content to a single ticket. We use this to append replies
         * @param int $tkt_id The ticket's ID
         */
        public function append_ticket_replies( $tkt_id ){
            //Retrieve the replies
            require_once( KSD_PLUGIN_DIR .  'includes/admin/class-ksd-admin.php' );
            $this->get_template_part( 'single','replies' );
            $this->get_template_part( 'form','new-reply' );
        }

}

return new KSD_Templates();
endif;