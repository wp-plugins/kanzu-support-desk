<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2><?php _e( 'KSD Tickets Importer', 'kanzu-support-desk' ) ; ?></h2>
    <p><?php _e( 'Currently, only csv files are supported. The csv file  should have the fields below in the same order they are listed in.', 'kanzu-support-desk'); ?></p>
 <?php 
      $ksd_importer_fields = array();  
      $ksd_importer_fields['subject']['values'] = __( 'The ticket subject', 'kanzu-support-desk');                                              
      $ksd_importer_fields['subject']['default'] =   __( 'N/A', 'kanzu-support-desk');                                                
      $ksd_importer_fields['subject']['mandatory'] =  __( 'Yes', 'kanzu-support-desk');
      $ksd_importer_fields['message']['values'] = __( 'The ticket message', 'kanzu-support-desk');                                              
      $ksd_importer_fields['message']['default'] =   __( 'N/A', 'kanzu-support-desk');                                                
      $ksd_importer_fields['message']['mandatory'] =  __( 'Yes', 'kanzu-support-desk');
      $ksd_importer_fields['customer_name']['values'] = __( 'John Doe', 'kanzu-support-desk');                                              
      $ksd_importer_fields['customer_name']['default'] =   __( 'None', 'kanzu-support-desk');                                                
      $ksd_importer_fields['customer_name']['mandatory'] =  __( 'Yes', 'kanzu-support-desk');
      $ksd_importer_fields['customer_email']['values'] = __( 'customer@email.com', 'kanzu-support-desk');                                              
      $ksd_importer_fields['customer_email']['default'] =   __( 'None', 'kanzu-support-desk');                                                
      $ksd_importer_fields['customer_email']['mandatory'] =  __( 'Yes', 'kanzu-support-desk');  
      $ksd_importer_fields['channel']['values'] = "STAFF,FACEBOOK,TWITTER,SUPPORT_TAB,EMAIL,CONTACT_FORM";                                              
      $ksd_importer_fields['channel']['default'] =   "STAFF";                                                
      $ksd_importer_fields['channel']['mandatory'] =  __( 'No', 'kanzu-support-desk');
      $ksd_importer_fields['status']['values'] = "NEW,OPEN,ASSIGNED,PENDING,RESOLVED";                                              
      $ksd_importer_fields['status']['default'] =   "NEW";                                                
      $ksd_importer_fields['status']['mandatory'] =  __( 'No', 'kanzu-support-desk');
      $ksd_importer_fields['severity']['values'] = "URGENT,HIGH,MEDIUM,LOW";                                              
      $ksd_importer_fields['severity']['default'] =   "LOW";                                                
      $ksd_importer_fields['severity']['mandatory'] =  __( 'No', 'kanzu-support-desk');        
      $ksd_importer_fields['time_logged']['values'] = "DD-MM-YYYY HH:MI:SS";                                              
      $ksd_importer_fields['time_logged']['default'] =   __( 'Current time', 'kanzu-support-desk');                                                
      $ksd_importer_fields['time_logged']['mandatory'] =  __( 'No', 'kanzu-support-desk');
      $ksd_importer_fields['private_note']['values'] = __( 'Ticket private note', 'kanzu-support-desk');                                              
      $ksd_importer_fields['private_note']['default'] =   __( 'None', 'kanzu-support-desk');                                                
      $ksd_importer_fields['private_note']['mandatory'] =  __( 'No', 'kanzu-support-desk');  
 ?>
    <div class="ksd-importer-fields">
        <div class="ksd-field-row ksd-importer-header">
            <div><?php _e( 'Field position', 'kanzu-support-desk');?></div>
            <div><?php _e( 'Field', 'kanzu-support-desk');?></div>
            <div><?php _e( 'Possible values', 'kanzu-support-desk');?></div>
            <div><?php _e( 'Default Value', 'kanzu-support-desk');?></div>
            <div><?php _e( 'Mandatory', 'kanzu-support-desk');?></div>
	</div>
    <?php $ksd_position=1;foreach ( $ksd_importer_fields as $field => $values ): ?>   
        <div class="ksd-field-row">
            <div><?php echo $ksd_position; ?></div>
            <div><?php echo $field; ?></div>
            <div><?php echo $values['values']; ?></div>
            <div><?php echo $values['default']; ?></div>
            <div><?php echo $values['mandatory']; ?></div>
	</div>
        <?php $ksd_position++; ?>
    <?php  endforeach; ?>    
    </div>
    <form action="?import=ksdimporter" method="post"enctype="multipart/form-data" class="ksd-importer-form">
        <label for="ksdimport"><?php _e( 'Select file to import', 'kanzu-support-desk');?></label>
        <input type="file" size="30" name="ksdimport" />
         <?php wp_nonce_field( 'ksd-ticket-importer', 'ksd-ticket-import-nonce' ); ?>
        <input class="button-small button button-primary ksd-button" type="submit" name="ksd-import-submit" value="Import Tickets" />
    </form>
    <p>
     <?php _e( 'Sample file input:', 'kanzu-support-desk');?>
        <pre>
          <?php _e( 'Ticket subject, This is the ticket message, John Doe, customer@email.com, STAFF, NEW, HIGH,21-02-2015 09:00:00,Please update the client on progress every 1hr.', 'kanzu-support-desk');?>            
          <?php  _e( 'Ticket subject, Ticket message, Jonathan Doe, customer@email.com', 'kanzu-support-desk'); ?>            
          <?php  _e( 'Ticket subject, Ticket message, Jonathan Doe, customer@email.com,,,MEDIUM', 'kanzu-support-desk');?>            
          <?php  _e( 'Ticket subject, Ticket message, Jane Doe, the.customer@email.com, EMAIL, OPEN, URGENT,21-02-2015 09:00:00,Checked his profile and it is fine', 'kanzu-support-desk');?>
        </pre>
    </p>
    <p><?php _e( 'NB: Non-mandatory fields can be left blank like in lines 2 and 3 in the sample above', 'kanzu-support-desk');?></p>
</div>