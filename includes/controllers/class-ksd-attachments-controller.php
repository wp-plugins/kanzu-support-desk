<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file	  Assignments.php
 */

 
include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-controller.php");

class KSD_Attachments_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Attachments";
		parent::__construct();
	}
	
	/*
	* Attach item to a ticket
	*
	* @param $ticket_id Ticket ID
	* @param $url File URL
	* @param $size File size
	* @param $filename Filename
	*/
	public function add_attachment( $ticket_id, $url,  $size, $filename, $is_reply = false ){
		$aO                             = $this->_model->get_obj();
		$aO->attach_url                 = $url;
		$aO->attach_size                = $size;
                $aO->attach_filename            = $filename;
                if( $is_reply ){//If it is a reply, the provided ID is a reply ID
                    $aO->attach_rep_id  = $ticket_id;
                }
                else{
                    $aO->attach_tkt_id  = $ticket_id;
                }		
		return $this->_model->add_attachment( $aO );
	}
        
	/*
	* Returns all attachments that through query
	*
        * @param String $query The Query to run on the table(s). Uses placeholders %s and %d
        * @param Array $value_parameters The values to replace the placeholders in $query
	* @return Array Array of objects
         */
        public function get_attachments( $query = null, $value_parameters=array() ){
            return $this->_model->get_all( $query, $value_parameters );           
        }
        
       /*
	* Returns a reply's attachments  
	*
        * @param int $reply_id The reply's ID
         */
        public function get_reply_attachments( $reply_id ){           
            return $this->_model->get_reply_attachments( $reply_id ) ;        
        }


}