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
                    'tkt_subject'        => '%s', 		
                    'tkt_message'        => '%s', 
                    'tkt_message_excerpt'=> '%s',
                    'tkt_channel' 	 => '%s',
                    'tkt_status' 	 => '%s',
                    'tkt_assigned_by' 	 => '%s',  
                    'tkt_cust_id'        => '%s',
                    'tkt_assigned_to' 	 => '%s',  
                    'tkt_severity' 	 => '%s', 
                    'tkt_resolution' 	 => '%s', 
                    'tkt_time_logged' 	 => '%s', 
                    'tkt_time_updated' 	 => '%s', 
                    'tkt_private_note'   => '%s',
                    'tkt_tags' 		 => '%s',
                    'tkt_is_read'        => '%d',
                    'tkt_customer_rating'=> '%d',
                    'tkt_cc'             => '%s'
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
                global $wpdb;
		return parent::get_all( $filter,$value_parameters );
	}
 
	/*
	*Add Ticket 
	*@param object Ticket object
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
        
        /**
         * Update several tickets at a go
         * @param Array $ticket_IDs
         * @param Array $updates Filds and the corresponding values to assign to them 
         */
        public function bulk_update_tickets ( $ticket_IDs, $updates ){
        $where = " WHERE tkt_id IN (".implode(",", $ticket_IDs).")";

        $set = '';
        $numUpdates = count($updates);
        $ni = 0;
        foreach ( $updates as $field => $new_value ) {            
            if ( ++$ni === $numUpdates ) {
               $set.=$field."= '".$new_value."' ";
               continue;
            }
            $set.=$field."= '".$new_value."', ";
        }
        //NB: We can't use $wpdb->update since it uses AND, not OR to join the WHERE clause
        $query = 'UPDATE '.$this->_tablename.' SET '.$set.' '.$where.';';
            return parent::exec_query( $query );
        }       
        
        public function bulk_delete_tickets( $ticket_IDs ){
            $where = " WHERE tkt_id IN (".implode(",", $ticket_IDs).")";
            $query = 'DELETE FROM '.$this->_tablename.' '.$where.';';
            return parent::exec_query( $query );    
        }
        
        
        public function exec_query( $query ){
		return parent::exec_query( $query );
	}
        
        public function get_dashboard_graph_statistics(){
            global $wpdb;
            $query = " SELECT COUNT(ID) AS ticket_volume, DATE(post_date) AS  date_logged "
                    . " FROM {$wpdb->prefix}posts WHERE post_type = 'ksd_ticket'  GROUP BY date_logged ";
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
             $response_time_query=" 
                SELECT TIMESTAMPDIFF(SECOND, post_date, reply_date) AS time_difference 
                FROM (
                SELECT  t1.post_date AS post_date, 
                CASE WHEN t2.post_date is NULL THEN now() ELSE t2.post_date END AS reply_date 
                FROM {$wpdb->prefix}posts t1
                LEFT JOIN {$wpdb->prefix}posts t2 ON t1.ID = t2.post_parent
                WHERE 
                t1.post_type = 'ksd_ticket' AND t2.post_type = 'ksd_reply'
                ) T
            "
            ;
                        
            $summary_statistics["response_times"] = parent::exec_query( $response_time_query );
             
            $open_tickets_args = array(
                'post_type' 	=> 'ksd_ticket',
                'post_status' 	=> 'open'
             );
            
             $wp_open_tickets_query = new WP_Query( $open_tickets_args );
             $summary_statistics["open_tickets"] = $wp_open_tickets_query->found_posts;              
   
             $unassigned_tickets_args['post_status'] = array( 'new', 'open', 'draft', 'pending' );//Don't show resolved tickets
             $unassigned_tickets_args['post_type'] = 'ksd_ticket';
             $unassigned_tickets_args = array(
                'post_type'         => 'ksd_ticket',
                'post_status'       => array( 'open','pending','new', 'draft' ),
                'meta_key'          => '_ksd_tkt_info_assigned_to',
                'meta_value'        => 0,
                'meta_compare'      => '<='//This works yet = doesn't
                ); 
               
            $wp_unassigned_tickets_query = new WP_Query( $unassigned_tickets_args );

            $summary_statistics["unassigned_tickets"] =  $wp_unassigned_tickets_query->found_posts; 
             
             return $summary_statistics;
         }
         
         /**
          * Get ticket count by status
          * @since 1.7.0
          */
         public function get_ticket_count_by_status(){     
             global $wpdb;
             $query = "SELECT count(ID) AS `count`,`post_status` FROM `{$wpdb->prefix}posts` where `post_type` = 'ksd_ticket' group by `post_status`";
             return parent::exec_query( $query );
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
        
        /*
         * 
         */
        public function get_all_and_reply_cnt( $filter, $value_parameters=array() ){
            global $wpdb;
            $query = "
                SELECT T.*, 
                COALESCE(cnt,0) AS rep_count
                FROM {$wpdb->prefix}kanzusupport_tickets T
                LEFT JOIN 
                ( 
                SELECT count(*) as cnt, rep_tkt_id 
                FROM {$wpdb->prefix}kanzusupport_replies
                GROUP BY rep_tkt_id
                ) R ON R.rep_tkt_id = T.tkt_id
                    ";
            $query = $query . ' WHERE ' . $filter;
            return $this->exec_prepare_query( $query, $value_parameters );
        }
         
        
       /* Returns the number of tickets in each ticket filter category
        * 
        * My unresolved, All,Unassigned,Recently updated,Recently resolved,Resolved
        * @param int user_id
        * As of v2.0.0, only returns 'mine' and 'unassigned' views
        * NB: Assign the returned column the same name as the view;the JS will put the right count against a view by the same name
        */ 
       public function get_filter_totals( $user_id, $recency ){
           global $wpdb;
           $query = "
                    SELECT * FROM ( 
                        SELECT COUNT({$wpdb->prefix}posts.ID) as mine FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( ( {$wpdb->prefix}postmeta.meta_key = '_ksd_tkt_info_assigned_to' AND CAST({$wpdb->prefix}postmeta.meta_value AS SIGNED) = '{$user_id}' ) ) AND {$wpdb->prefix}posts.post_type = 'ksd_ticket' AND (({$wpdb->prefix}posts.post_status = 'draft' OR {$wpdb->prefix}posts.post_status = 'pending' OR {$wpdb->prefix}posts.post_status = 'open' OR {$wpdb->prefix}posts.post_status = 'new') ) 
                    ) T1,
                    (	
                    SELECT COUNT({$wpdb->prefix}posts.ID) as unassigned  FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( ( {$wpdb->prefix}postmeta.meta_key = '_ksd_tkt_info_assigned_to' AND CAST({$wpdb->prefix}postmeta.meta_value AS SIGNED) = '0' ) ) AND {$wpdb->prefix}posts.post_type = 'ksd_ticket' AND (({$wpdb->prefix}posts.post_status = 'draft' OR {$wpdb->prefix}posts.post_status = 'pending' OR {$wpdb->prefix}posts.post_status = 'open' OR {$wpdb->prefix}posts.post_status = 'new') ) 
                    ) T2
                    ";
           
           return $this->exec_query( $query);           
       }
         
         
 }