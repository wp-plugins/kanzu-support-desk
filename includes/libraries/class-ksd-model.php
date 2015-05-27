<?php
/**
 * All db interactions. All data to the Db is
 * escaped to guard against SQL injection
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */
 

 class KSD_Model{
	protected $_tablename = "";
	protected $_id = "";
	protected $_formats = array(
		''=>''
	);
	
	public function __construct(){
	}
	
        /**
         * Perform custom SQL queries that don't need to be escaped (prepared)
         * All the queries that use this method don't get any input from the user
         */
	public function exec_query( $query ){
		global $wpdb;
		return $wpdb->get_results( $query, OBJECT );
	}
        
        /**
         * Execute a query that needs to be escaped first. All data in SQL queries 
         * must be SQL-escaped before the SQL query is executed to prevent against 
         * SQL injection attacks. The prepare method performs this 
         * functionality for WordPress
         * @param string $unprepared_query   The query with placeholders %s and %d
         * @param Array  $value_parameters  Value parameters for use in the query
         */
        public function exec_prepare_query( $unprepared_query, $value_parameters ){
           global   $wpdb;           
           return $wpdb->get_results( 
                        $wpdb->prepare(
                                $unprepared_query,$value_parameters),
                    OBJECT );
        }
	
	/*
	* Get single row object 
	* We first prepare the query to prevent SQL injection
	*@param userid
	*/
	public function get_row( $id ){
		global $wpdb;
		$results = $wpdb->get_results(
                                $wpdb->prepare(
                                    'SELECT * FROM '. $this->_tablename .' WHERE '. $this->_id .' = %d', $id ),
                                OBJECT);
		
		return ( count($results) > 0 ) ? $results[0]: null;
	}
        
        /**
         * Get a single variable from the database
         * 
         * @param String $variable The variable you'd like to retrieve. e.g. count(tkt_id),sum(tkt_id), etc
         * @param String $where The WHERE clause. It should have placeholders %s and %d
         * @param Array $value_parameters The values to replaced the placeholders in the $where variable.
         */
        
        public function get_var( $variable, $where, $value_parameters ) {
            global $wpdb;
            //Make $variable the first element in the $value_parameters_new array
           $value_parameters_new    = array_unshift( $value_parameters, $variable );
            return $wpdb->get_var( 
                        $wpdb->prepare(
                                "SELECT %s FROM ". $this->_tablename." ".$where,$value_parameters_new )
                );
        }
	
	/*
	* Get all from rows from table.
	*
	* @param $filter SQL filter. Everything after the WHERE key word. Uses placeholders %s and %d for values
        * @param Array $value_parameters The values to replaced the placeholders in the $where variable.
	*/
	public  function get_all( $filter = "", $value_parameters=array() ){
		global $wpdb;
		$where = ( $filter == "" || $filter == null ) ? "" : " WHERE " . $filter ;
                $results = $wpdb->get_results( 
                                $wpdb->prepare(
                                        'SELECT * FROM '. $this->_tablename . ' '. $where , 
                                        $value_parameters ),                        
                                OBJECT );
		return $results;
	}
 
	/*
	* Add row to table. 
	* Insert escapes the data for us
	*/
	public function add_row( &$rowObject ){
		global $wpdb;
		
		$data = array();
		$format = array();
		foreach( $rowObject as $key => $value) {
			$data[$key] = $value;
			array_push($format,$this->_formats[$key]);
		}
		$wpdb->insert( $this->_tablename, $data, $format );
		return $wpdb->insert_id;
	}
	
	/*
	* Delete row/s 
	* Delete escapes the data
	* @param $rowObject 
	* @return Number of rows deleted or false 
	*/
	public function delete_row(  &$rowObject ){
		global $wpdb;
		$table = $this->_tablename;
		$where = array();
		$where_format = array();
		foreach( $rowObject as $key => $value) {
			$where[$key] = $value;
			array_push($where_format,$this->_formats[$key]);
		}	
		return $wpdb->delete( $table, $where, $where_format ); 		 
	}
	

	/*
	* Save/update row(s). Update escapes the data for us
	* @return The number of rows updated or false
	* *new_* for new value
	*/
	public function update_row( &$rowObject ){
		global $wpdb;
		$table = $this->_tablename;
		$data = array();
		$where = array();
		$format = array();
		$where_format = array();
		foreach( $rowObject as $key => $value) {
			$pfx = substr($key,0,4); #new_
			if( $pfx == "new_"){ //New value Record Update Pattern
				$newkey = substr($key,4);
				$data[ $newkey] = $value;
				array_push($format,$this->_formats[$newkey]);
			}else{
				$where[$key] = $value;
				array_push($where_format,$this->_formats[$key]);
			}
		}
		return $wpdb->update( $table, $data, $where, $format, $where_format); 		 
	}
	
	public function get_obj(){
		return (new stdClass());
	}
        
 }
