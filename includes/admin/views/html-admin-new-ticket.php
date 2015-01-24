<div id="ksd-new-ticket">
    <form class="ksd-new-ticket-admin" id="new-ticket" method="POST">
        <div>
            <input type="text" value="<?php _e('Customer Name','kanzu-support-desk'); ?>" size="30" name="ksd_cust_fullname" label="Customer Name" class="ksd-customer-name" minlength="2" required/>
            <input type="email" value="<?php _e('Customer Email','kanzu-support-desk'); ?>" size="30" name="ksd_cust_email" label="Customer Email" class="ksd-customer-email" required/>
            <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
        </div>
        <div class="ksd-message">
            <?php wp_editor(  '' , 'ksd_tkt_message', array( "media_buttons" => false, "textarea_rows" => 5 ) ); ?> 
        </div>
        <div class="ksd-severity-and-assign">
            <div class="ksd-severity">
                <label for="ksd_tkt_severity"><?php _e('Severity','kanzu-support-desk'); ?></label>
                <select name="ksd_tkt_severity">
                    <option><?php _e('LOW','kanzu-support-desk'); ?></option>
                    <option><?php _e('MEDIUM','kanzu-support-desk'); ?></option>
                    <option><?php _e('HIGH','kanzu-support-desk'); ?></option>
                    <option><?php _e('URGENT','kanzu-support-desk'); ?></option>
                </select>
            </div>
            <div class="ksd-assign-to">
                <label for="ksd_tkt_assigned_to"><?php _e('Assign To','kanzu-support-desk'); ?></label>
                <select name="ksd_tkt_assigned_to">
                    <option>-</option>
                <?php $agents = get_users();
                    foreach ( $agents as $agent ) {
                        echo '<option value='.$agent->ID.'>' . esc_html( $agent->display_name ) . '</option>';
                    }
                ?>
                </select>
            </div>
        </div>    
        <div class="ksd-send-email">
            <label for="ksd_send_email"><?php _e('Send Email','kanzu-support-desk'); ?></label>
            <input name="ksd_send_email"  type="checkbox" value="yes" checked/>
        </div>
        <input name="ksd_tkt_assigned_by" type="hidden" value="<?php echo get_current_user_id(); ?>" />
        <input name="action" type="hidden" value="ksd_log_new_ticket" />
        <input name="ksd_tkt_channel" type="hidden" value="staff" />
         <?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
        <input type="submit" value="<?php _e( "Send","kanzu-support-desk" ); ?>" name="ksd-submit-admin-new-ticket" class="ksd-submit"/>
    </form>
</div>