<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 *
 * Channels.php
 */
 
include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-model.php");


 class KSD_Replies_Model extends KSD_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_replies";	
		$this->_id = "rep_id";
			
		$this->_formats = array(
		'rep_id' 			 	=> '%d', 
		'rep_tkt_id'	 		=> '%d',
		'rep_type'	 			=> '%d' , 
		'rep_is_cc' 			=> '%s',
		'rep_is_bcc' 	 		=> '%s',
		'rep_date_created' 		=> '%s',
		'rep_created_by' 	 	=> '%s',
		'rep_date_modified' 	=> '%s',
                'rep_message'    => '%s'
		);
	}
	
	/*
	*Get Replies object
	*
	*@param userid
	*/
	public function get_reply( $id ){
		return parent::get_row($id);
	}
	
	/*
	*Get all from the replies table
	*
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*/
	public  function get_all( $filter = "",$value_parameters=array() ){
		return parent::get_all($filter,$value_parameters);
	}
 
	/*
	*
	*/
	public function add_reply( &$obj ){
		return parent::add_row( $obj );
	}
	
	/*
	*
	*@param Replies object.
	*/
	public function delete_reply(  &$obj ){
		return parent::delete_row( $obj );
	}

 }
 
 
 ?>