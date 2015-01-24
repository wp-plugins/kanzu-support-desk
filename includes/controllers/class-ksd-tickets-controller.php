<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file      class-ksd-tickets-contoller.php
 */

include_once( KSD_PLUGIN_DIR .  'includes/libraries/class-ksd-controller.php' );

class KSD_Tickets_Controller extends KSD_Controller {	
	public function __construct(){
		$this->_model_name = "Tickets";
		parent::__construct();
	}
	
	/*
	*Logs new ticket
	*
	*@param $ticket ticket object to log
	*/
	public function log_ticket(&$ticket){
		return $this->_model->add_ticket( $ticket);
	}
	
	/*
	*Close ticket
	*
	*@param int $ticket_id ticket id of ticket to close
	*
	*/
	public function close_ticket($ticket_id ){
		$tO = new stdClass();
		$tO->tkt_id = $ticket_id;
		$tO->new_tkt_status = "CLOSE";
		$id = $this->_model->update_ticket( $tO );
	}
	
 
        
      	/*
	* Update a ticket
	*
	*@param Object $ticket the Updated ticket
	*
	*/
	public function update_ticket( $ticket ){
		return $this->_model->update_ticket( $ticket );
	}

	/*
	*Returns ticket object with specified id.
	*
	*@param  int $ticket_id	ticket id
	*@return ticket Object
	*/
	public function get_ticket($ticket_id){
		return $this->_model->get_ticket( $ticket_id);
	}
        
	
	/*
	* Returns all tickets that through query
	*
        * @param String $query The Query to run on the table(s). Uses placeholders %s and %d
        * @param Array $value_parameters The values to replace the placeholders in $query
	* @return Array Array of objects
	*/
	public function get_tickets( $query = null, $value_parameters=array() ){                   
               return $this->_model->get_all( $query, $value_parameters );               		
	}
	
	/**
	 * Delete the ticket with the specified ID
	 * @param int $ticket_id Ticket ID
	 */
	 public function delete_ticket( $ticket_id ){
		$where = array ('tkt_id'=>$ticket_id);
		return $this->_model->delete_ticket( $where );
	}
	
	/**
	 * Get the ticket volumes for display on the dashboard
	 */
	public function get_dashboard_graph_statistics(){	
		return $this->_model->get_dashboard_graph_statistics();
	}
        
        
        public function get_dashboard_statistics_summary(){
            return $this->_model->get_dashboard_statistics_summary();
        }
        /**
         * Run a custom query
         * @param type $query The query to run
         */
        public function exec_query($query){
            return $this->_model->exec_query( $query);
        }
        
       /**
        * Before imposing a LIMIT clause to a query to get the tickets needed in the tickets view,
        * we run that query against the Db and count the number of rows. This is essential for
        * pagination of the returned tickets
        * @param String $filter The Query to run on the table. Uses placeholders %s and %d
        * @param Array  $value_parameters The values to replace the placeholders in $filter
        * @return type
        */
        public function get_pre_limit_count( $filter, $value_parameters ){
           return  $this->_model->get_pre_limit_count( $filter,$value_parameters );
        }


}
?>
