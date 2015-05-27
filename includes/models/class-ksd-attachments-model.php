<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
 
include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-model.php");

 class KSD_Attachments_Model extends KSD_Model{

	
	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_attachments";	
		$this->_id = "attach_id";
			
		$this->_formats = array(
		'attach_tkt_id'             => '%d', 
                'attach_rep_id'             => '%d', 
		'attach_url'                => '%s',
		'attach_size'               => '%s', 
		'attach_filename'           => '%s'
	);
	}
	
	/*
	*Get attachment object
	*
	*@param userid
	*/
	public function get_attachment( $id ){
		return parent::get_row($id);
	}
        
                
       /*
	* Get a reply's attachments  
	*
        * @param int $reply_id The reply's ID
	* @return Array Array of objects
         */
        public function get_reply_attachments( $reply_id ){
            global $wpdb;
            $query = " attach_rep_id = %d";
            $value_parameters[] = $reply_id;
            $attachments    =  parent::get_all( $query, $value_parameters ); 
            $wpdb->flush(); //This is important otherwise all replies will have the same attachments displayed
            return $attachments ;        
        }
	
 
	/*
	*Add new attachment
	*@param Object Attachment
	*
	*/
	public function add_attachment( &$obj ){
		return parent::add_row( $obj );
	}
	
	/*
	* Delete Attachment
	*
	*@param Object Attachment
	*/
	public function delete_attachment(  &$obj ){
		return parent::delete_row( $obj );
	}
	

	/*
	* Save/update attachment
	*@param Object Attachment
	* *new_* for new value
	*/
	public function update_attachment( &$obj ){
		return parent::update_row( $obj );
	}
 }
