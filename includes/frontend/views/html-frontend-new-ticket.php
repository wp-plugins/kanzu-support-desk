<div id="ksd-new-ticket-frontend-wrap" class="<?php echo $form_position_class; ?>">
    <div class="ksd-close-form-wrapper">
        <img src="<?php echo KSD_PLUGIN_URL.'assets/images/icons/close.png'; ?>" class="ksd_close_button" width="32" height="32" Alt="<?php __('Close','kanzu-support-desk'); ?>" />
    </div>
    <form id="ksd-new-ticket" method="POST" class="ksd-new-ticket-frontend <?php echo $form_position_class; ?>-form">
        <ul>
        <li class="ksd-name">               
              <input type="text" value="<?php _e('Name','kanzu-support-desk'); ?>" size="30" name="ksd_cust_fullname" label="Customer Name" class="ksd-customer-name" minlength="2" required/>
        </li>
        <li class="ksd-email">
              <input type="email" value="<?php _e('Email','kanzu-support-desk'); ?>" size="30" name="ksd_cust_email" label="Customer Email" class="ksd-customer-email" required/>
        </li>        
        <li class="ksd-subject">       
          <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
        </li>
          <li class="ksd-message">     
              <textarea value="<?php _e('Message','kanzu-support-desk'); ?>" rows="7" class="ksd-message" name="ksd_tkt_message" required></textarea>
          </li>  
          <li class="ksd-frontend-submit">
            <img src="<?php echo KSD_PLUGIN_URL.'assets/images/loading_button.gif'; ?>" class="hidden ksd_loading_button" width="45" height="35" />
            <input type="submit" value="<?php _e( "Send Message","kanzu-support-desk" ); ?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
          </li>
        </ul>
        <input name="action" type="hidden" value="ksd_log_new_ticket" />
        <input name="ksd_tkt_channel" type="hidden" value="support_tab" />
        <?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
    </form>
    <div class="<?php echo $form_position_class; ?>-form-response hidden"></div>
</div>