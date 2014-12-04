<?php
/**
 * The tickets model
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 
include_once( KSD_PLUGIN_DIR .  'includes/libraries/class-ksd-model.php' );

 class KSD_Tickets_Model extends KSD_Model{

	public function __construct(){
		global $wpdb;
		$this->_tablename = $wpdb->prefix . "kanzusupport_tickets";	
		$this->_id = "tkt_id";
			
		$this->_formats = array(
                    'tkt_id' 		 => '%d', 
                    'tkt_subject' 		 => '%s', 		
                    'tkt_message'            => '%s', 
                    'tkt_message_excerpt'	 => '%s',
                    'tkt_channel' 		 => '%s',
                    'tkt_status' 		 => '%s',
                    'tkt_assigned_by' 	 => '%s',  
                    'tkt_cust_id'            => '%s',
                    'tkt_assigned_to' 	 => '%s',  
                    'tkt_severity' 		 => '%s', 
                    'tkt_resolution' 	 => '%s', 
                    'tkt_time_logged' 	 => '%s', 
                    'tkt_time_updated' 	 => '%s', 
                    'tkt_private_note'  	 => '%s',
                    'tkt_tags' 		 => '%s',
                    'tkt_customer_rating'    => '%d'
                );
	}
	
	/*
	*Get Tickets object
	*
	*@param Ticket ID
	*/
	public function get_ticket( $id ){
		return parent::get_row($id);
	}
	
	/*
	*Get all from Tickets table
	*
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*/
	public  function get_all( $filter = "", $value_parameters=array() ){
		return parent::get_all( $filter,$value_parameters );
	}
 
	/*
	*Add Ticket to 
	*
	*
	*/
	public function add_ticket( &$ticket ){
		return parent::add_row( $ticket );
	}
	
	/*
	*Add user to 
	*
	*@param Ticket object.
	*/
	public function delete_ticket(  &$ticket ){
		return parent::delete_row( $ticket );
	}
	

	/*
	* Save/update 
	*@param ticket object
	* *new_* for new value
	*/
	public function update_ticket( &$ticket ){
		return parent::update_row( $ticket );
	}
        
        
        public function exec_query( $query ){
		return parent::exec_query( $query );
	}
        
        public function get_dashboard_graph_statistics(){
            $query = 'SELECT COUNT(tkt_id) AS "ticket_volume",DATE(tkt_time_logged) AS "date_logged" FROM '.$this->_tablename.' GROUP BY date_logged;';
            return parent::exec_query( $query );
        }
        
        /**
         * Retrieve the summary statistics that show on the dashboard
         */
        public function get_dashboard_statistics_summary(){
            global $wpdb;
            $summary_statistics = array();
                         
            //Note that the alias's in all the queries below are important. 
            //They are used by the JS that iterates through the AJAX response
            //and displays the output in the view. 
            //Change the query but keep the alias; or moodify the output JS too
             $response_time_query="SELECT TIMESTAMPDIFF(
                        SECOND , TICKETS.tkt_time_logged, REPLIES.rep_date_created ) AS time_difference
                        FROM {$wpdb->prefix}kanzusupport_tickets AS TICKETS
                        JOIN `{$wpdb->prefix}kanzusupport_replies` AS REPLIES ON TICKETS.tkt_id = REPLIES.rep_tkt_id
                        WHERE TICKETS.tkt_status = 'OPEN'
                        GROUP BY rep_tkt_id";
             $summary_statistics["response_times"] = parent::exec_query( $response_time_query );
             
             $open_tickets_query = 'SELECT COUNT(tkt_id) AS open_tickets FROM '.$this->_tablename.' WHERE tkt_status != "RESOLVED" ';
             $summary_statistics["open_tickets"] = parent::exec_query( $open_tickets_query );

             $unassigned_tickets_query = 'SELECT COUNT(tkt_id) AS unassigned_tickets FROM '.$this->_tablename.' WHERE tkt_assigned_to IS NULL ';
             $summary_statistics["unassigned_tickets"]  = parent::exec_query( $unassigned_tickets_query );
              
             return $summary_statistics;
         }
 
         
         
       /* Before imposing a LIMIT clause to a query to get the tickets needed in the tickets view,
        * we run that query against the Db and count the number of rows returned. This is essential for
        * pagination of the returned tickets
        * Return number of rows in query
        * @param String $filter The Query to run on the table. Uses placeholders %s and %d
        * @param Array  $value_parameters The values to replace the placeholders in $filter
        * @return int The number of tickets
        */
         public function get_pre_limit_count( $filter, $value_parameters ){
            $new_filter = "SELECT * FROM {$this->_tablename} WHERE {$filter}";            
            $query = "SELECT COUNT(*) AS count FROM ( $new_filter ) t";
            if ( count ($value_parameters) > 0 ){//If we need to prepare the query {since some input in it is from the user
                $obj =  parent::exec_prepare_query( $query,$value_parameters );   
            }else{//$query doesn't contain any user input
                $obj =  parent::exec_query( $query );   
            }           
            return $obj[0]->count;
         }
 }