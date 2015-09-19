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

class KSD_Assignments_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Assignments";
		parent::__construct();
	}
	
	/*
	* Assign ticket
	*
	* @param $ticket_id Ticket ID
	* @param $assign_to ID of agent to assign ticket to
	* @param $assign_by ID of admin who assigned ticket
	* @param $notes 	Notes on ticket assignment
	*/
	public function assign_ticket( $ticket_id, $assign_to,  $assign_by, $notes="" ){
		$aO                                 = $this->_model->get_obj();
		$aO->assign_assigned_to             = $assign_to;
		$aO->assign_assigned_by             = $assign_by;
		$aO->assign_tkt_id = $ticket_id;
		return $this->_model->add_assignment( $aO );
	}
	
	
	/*
	* Unassign ticket
	*
	* @param $ticket_id Ticket ID of ticket to unassign 
	*
	*/
	public function unassign_ticket( $ticket_id ){
		$aO                = $this->_model->get_obj();
		$aO->assign_tkt_id = $ticket_id;
		$this->_model->delete_assignment( $aO );
	}

}