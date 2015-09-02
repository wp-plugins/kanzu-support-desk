<?php
 /**
  * Imports tickets into KSD
  *
  * @package   Kanzu_Support_Desk
  * @author    Kanzu Code <feedback@kanzucode.com>
  * @license   GPL-2.0+
  * @link      http://kanzucode.com
  * @copyright 2014 Kanzu Code
  * @since     1.5.4
  */
  
  class KSD_Importer{
      
      /**
       * The responses to display after the importation
       * @var Array 
       */
      private $import_response;

      public function __construct(){
          //After a ticket is successfully imported, this action is called
          add_action( 'ksd_new_ticket_imported', array( $this, 'new_ticket_imported' ) );
      }
      
      /**
       * Handle the importation
       */
      public function dispatch ( ) {

         $this->handle_import();

         $this->showform();   
      }

 

         /**
          * Display the upload form.
          * @since 1.5.4
          */
         public  function showform ( ) {
            include_once( KSD_PLUGIN_DIR .  'includes/admin/views/html-admin-tickets-importer.php');      
         }



         /**
          * Parses cvs file and imports the tickets in the appropriate table.
          *
          * @since 1.5.4 
          */

         public function handle_import ( ) {

             if ( ! isset( $_POST['ksd-import-submit']) ) return;
             $file_name = $_FILES['ksdimport']['name'];
             if ( empty( $file_name ) ) {
                 $error_string = __( 'No file uploaded! Please upload a file below', 'kanzu-support-desk');
                 echo "<div class='error'>{$error_string}</div>"; 
                 return;
             } 
             $file_ext = strtolower( end( explode(".", $file_name)));
             if(  !preg_match( '/txt|csv/',$file_ext ) ){
                 $error_string = __( 'Invalid file type! Allowed file types are txt and csv', 'kanzu-support-desk');
                 echo "<div class='error'>{$error_string}</div>"; 
                return;  
             }
             
            if ( ! wp_verify_nonce( $_POST['ksd-ticket-import-nonce'], 'ksd-ticket-importer' ) ){
                die ( __('Busted!','kanzu-support-desk') );
            }

             $file = $_FILES['ksdimport']['tmp_name'];
             ini_set("auto_detect_line_endings", true);//PHP may not properly recognize the line endings when reading files either on or created by a Macintosh computer. This fixes that
             $file_handle = fopen( $file , "r");
             $line_number = 0;

             while ( ( $row = fgetcsv( $file_handle, 9999999, "," ) ) !== FALSE) {
                 $line_number++;
                 //Check for mandatory fields
                 if( ! isset( $row[0] ) ){
                     $this->import_response[$line_number] = __( 'Ticket subject not defined', 'kanzu-support-desk');
                     continue;
                 }
                 if( ! isset( $row[1] ) ){
                     $this->import_response[$line_number] = __( 'Ticket message not defined', 'kanzu-support-desk');
                     continue;
                 }
                 if( ! isset( $row[2] ) ){
                     $this->import_response[$line_number] = __( 'Customer name not defined', 'kanzu-support-desk');
                     continue;
                 }
                 if( ! isset( $row[3] ) ){
                     $this->import_response[$line_number] = __( 'Customer email address not defined', 'kanzu-support-desk');
                     continue;
                 }  
                 $new_ticket                    = new stdClass(); 
                 $new_ticket->tkt_subject       = $row[0];
                 $new_ticket->tkt_message       = $row[1];
                 $new_ticket->cust_fullname     = $row[2];
                 $new_ticket->cust_email        = $row[3]; 
                 if( !is_email( trim( $row[3] ) ) ){
                    $this->import_response[$line_number] = sprintf( __( 'Invalid Email address %s', 'kanzu-support-desk'), $row[3] );   
                    continue;
                 }
                 
                 if ( isset( $row[4] ) ){
                    $new_ticket->tkt_channel    = $row[4];
                 }
                 if( isset( $row[4] ) && !empty ( $row[4] ) && ! preg_match('/admin-form|FACEBOOK|TWITTER|SUPPORT_TAB|EMAIL|CONTACT_FORM/',$row[4]) ){
                     $this->import_response[$line_number] = sprintf( __( 'Invalid channel %s', 'kanzu-support-desk'), $row[4] );   
                     continue;
                 }
                 if ( isset( $row[5] ) ){
                    $new_ticket->tkt_status    = $row[5];
                 }
                 if( isset( $row[5] ) && !empty ( $row[5] ) && ! preg_match('/NEW|OPEN|ASSIGNED|PENDING|RESOLVED/',$row[5]) ){
                     $this->import_response[$line_number] = sprintf( __( 'Invalid status %s', 'kanzu-support-desk'), $row[5] );   
                     continue;
                 }
                 if ( isset( $row[6] ) ){
                    $new_ticket->tkt_severity    = $row[6];
                 }
                 if( isset( $row[6] ) && !empty ( $row[6] ) && ! preg_match('/URGENT|HIGH|MEDIUM|LOW/',$row[6]) ){
                     $this->import_response[$line_number] = sprintf( __( 'Invalid severity %s', 'kanzu-support-desk'), $row[6] );   
                     continue;
                 }
                 if ( isset( $row[7] ) ){
                    try {
                        $new_ticket->tkt_time_logged    = date_format( new DateTime($row[7]), 'Y-m-d h:i:s'); 
                    } catch (Exception $e) {
                        $this->import_response[$line_number] = sprintf( __( 'Invalid Time logged %s. ERROR {$e->getMessage()}', 'kanzu-support-desk'), $row[7] );  
                        continue;
                    }                     
                 }
                 if ( isset( $row[8] ) ){
                    $new_ticket->tkt_private_note    = $row[8];
                 }
                 global $current_user;
                 $new_ticket->tkt_assigned_by       = $current_user->ID;
                 $new_ticket->tkt_imported          = true;//IMPORTANT: Used by the plugin to distinguish this as a ticket created by importing
                 $new_ticket->tkt_imported_id       = $line_number;
                 //Log the ticket
                 do_action( 'ksd_log_new_ticket', $new_ticket );//After this logging, $this->new_ticket_imported() is called via actions
            }
            if ( $line_number == 0 ){
                $this->import_response[0]   =   __( 'The specified file is empty', 'kanzu-support-desk');
            }
            fclose( $file_handle );
            //All done. Let's display all the responses
            $this->display_import_response();
         }
         
         /**
          * Called when an imported ticket is logged. Receives the ticket ID used to identify
          * the ticket in the importer and then the new ticket ID assigned to it in the db
          * @param Array $imported_ticket_array Array with two items. The first item is the line number
          *                                     of the ticket in the uploaded file, the second is the ID assigned to
          *                                     the new ticket in the Db e.g. $imported_ticket_array = array( 1 , 56 )
          */
         public function new_ticket_imported( $imported_ticket_array ){
            if( $imported_ticket_array[1] > 0 ){ //Ticket logged successfully, populate  $this->import_response['success'] array   
                $this->import_response['success'][$imported_ticket_array[0]] = __( 'Ticket logged successfully', 'kanzu-support-desk');
            }
            else{
                $this->import_response[$imported_ticket_array[0]] = __( 'An unexpected error occurred. Ticket not logged', 'kanzu-support-desk');  
            }
         }
         
         /**
          * Display all responses after completing the import process
          * //@TODO Consider putting all the errors in one error response
          */
         private function display_import_response(){
            foreach( $this->import_response as $line_number => $response ){
                if ( is_array( $response ) ){
                    $total_count_successful = count($response);
                    $success_message  = sprintf( _n( 'One ticket logged successfully', '%d tickets logged successfully', $total_count_successful, 'kanzu-support-desk' ), $total_count_successful );
                    echo "<div class='updated'>{$success_message}</div>"; 
                }
                else{
                    $error_string = sprintf( __( '%1$s on line %2$d. Ticket not logged','kanzu-support-desk' ),$response, $line_number );
                    echo "<div class='error'>{$error_string}</div>"; 
                }
            } 
         }

  }