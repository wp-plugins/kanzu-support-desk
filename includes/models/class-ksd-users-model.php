<?php
/**
 * Holds all installation & deactivation-related functionality.  
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-model.php");

 class KSD_Users_Model extends KSD_Model{
	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "users";	
		$this->_id = "user_id";
	}	
	/*
	*Get user object (WP_User)
	*
	*@param userid
	*/
	public function get_user( $id = null){
		if ( $id == null or $id == 0 ){
			return wp_get_current_user();
		}
		return get_user_by( 'id', $id );
	}
	
	/*
	*Get all from users (kanzu-users) from wp users table
         * 
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*@param $filter SQL filter 
	*/
	public  function get_all( $filter = "", $value_parameters=array() ){
		return parent::get_all( $filter,$value_parameters );
	}
 
	/*
	*Add users
	*
	*
	*/
	public function add_user( &$userObject ){
		$userdata = (array) $userObject;
		$user_id = wp_insert_user( $userdata ) ;
		
		return ( $user_id > 0 ) ? $user_id : -1 ;
	}
	
	/*
	*
	*@param $id User id
	*/
	public function delete_user( int $id, $reassign = null){
		 wp_delete_user( $id, $reassign ); 
	}
	
	
	/*
	*
	* @param userObject
	*/
	public function update_user( &$userObject ){
		$userdata = (array) $userObject;
		$user_id  = wp_update_user( $userdata );
		
		return ( $user_id > 0 ) ? $user_id : -1 ;
	}
        
        public function get_users_with_roles( $roles ){
            global $wpdb;
            $guery_get_users = "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value REGEXP '.$roles.'";
            return parent::exec_query( $guery_get_users ); 
        }

 }
 
 
 ?>
