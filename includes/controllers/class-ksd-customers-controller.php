<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file      class-ksd-customers-controller.php
 */

include_once( KSD_PLUGIN_DIR. "includes/libraries/class-ksd-controller.php");

class KSD_Customers_Controller extends KSD_Controller 
{	
	public function __construct(){
		$this->_model_name = "Customers";
		parent::__construct();
	}
        
       /*
	* Add a new customer to the Db
	*
	*@param $reply reply object to log
	*/
	public function add_customer( &$customer ){
		return $this->_model->add_customer( $customer );
	}
	
	/*
	*Returns customer object with specified id.
	*
	*@param  $customer_id	ticket id
	*@return customer Object
	*/
	public function get_customer( $customer_id ){
		return $this->_model->get_customer( $customer_id);
	}
	
        /**
         * Get customer by their email address
         * @param string $email_address The customer's email address
         */
        public function get_customer_by_email( $email_address ){            
            return $this->_model->get_customer_by_email( $email_address );
        }
        
        /**
         * Get customer email address by Ticket ID
         */
        public function get_customer_by_ticketID( $tkt_id ){
            return $this->_model->get_customer_by_ticketID( $tkt_id );
        }
        
	/*
	*Returns all customers that through query
	*@param string $filter Everything after the WHERE clause. Uses placeholders %s and %d
        *@param Array $value_parameters The values to replace the placeholders
	*@return Array Array of objects
	*/
	public function get_customers( $filter,$value_parameters ){
		return $this->_model->get_all( $filter,$value_parameters );
	}
	
	/*
	* Disable customer account
	*
	* @param int $customer_id 
	*/
	public function disable_account( $customer_id ){
		$cO = new stdClass();
		$cO->cust_id = $customer_id;
		$cO->new_account_status = "DISABLED";
		$this->_model->update_customer( $cO );
	}
	
	/*
	* Enable customer account
	*/
	public function enable_account( $customer_id ){
		$cO = new stdClass();
		$cO->cust_id = $customer_id;
		$cO->new_account_status = "ENABLED";
		$this->_model->update_customer( $cO );
	}
	
	public function delete_customer( $customer_id ){
		
		//Delete from customer table
		//Delete from wp usertable
		//Delete tickets
		//delete replies
	}
}
?>