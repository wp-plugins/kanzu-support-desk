<form method="POST" id="update-settings" class="ksd-settings pending"> 
    <div class="ksd-settings-accordion">
        <?php $settings = Kanzu_Support_Desk::get_settings();?>
    <h3><?php _e("General","kanzu-support-desk"); ?> </h3>
        <div>
             <div class="setting">
                <label for="enable_new_tkt_notifxns"><?php _e( "Enable new ticket notifications","kanzu-support-desk" ); ?></label>
                <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("If this is enabled, an email is sent to the customer's email address for all new tickets logged from the front-end",'kanzu-support-desk')  ;?>"/>
                <input name="enable_new_tkt_notifxns"  type="checkbox" <?php checked( $settings['enable_new_tkt_notifxns'], "yes" ) ?> value="yes"  />
             </div>
             <div class="enable_new_tkt_notifxns">
                <div class="setting">
                   <label for="ticket_mail_from_name"><?php _e( "From (Name)","kanzu-support-desk" ); ?></label>
                   <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Defaults to the primary administrator's display name",'kanzu-support-desk')  ;?>"/>
                   <input type="text" value="<?php echo $settings['ticket_mail_from_name']; ?>" size="30" name="ticket_mail_from_name" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_from_email"><?php _e( "From (Email Address)","kanzu-support-desk" ); ?></label>
                   <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Defaults to the primary administrator's email address",'kanzu-support-desk')  ;?>"/>
                   <input type="text" value="<?php echo $settings['ticket_mail_from_email']; ?>" size="30" name="ticket_mail_from_email" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_subject"><?php _e( "Subject","kanzu-support-desk" ); ?></label>
                   <input type="text" value="<?php echo $settings['ticket_mail_subject']; ?>" size="60" name="ticket_mail_subject" />
               </div>
               <div class="setting">
                   <label for="ticket_mail_message"><?php _e( "Message","kanzu-support-desk" ); ?></label>
                   <textarea cols="60" rows="4" name="ticket_mail_message"><?php echo $settings['ticket_mail_message']; ?></textarea>
               </div>
             </div><!--.enable_new_tkt_notifxns-->
            <div class="setting">
                <label for="recency_definition"><?php _e( "Recency Definition ( In Hours )","kanzu-support-desk" ); ?></label>
                <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("In the ticket view, the 'Recently Updated' & 'Recently resolved' tabs, show tickets updated in last X hours",'kanzu-support-desk')  ;?>"/>
                <input type="text" value="<?php echo $settings['recency_definition']; ?>" size="15" name="recency_definition" />
            </div>
            <div class="setting">
                <label for="show_support_tab"><?php _e( "Show front-end support tab","kanzu-support-desk" ); ?></label>
                <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Displays a form on the front-end of your site through which your customers can log new tickets",'kanzu-support-desk')  ;?>"/>
                <input name="show_support_tab"  type="checkbox" <?php checked( $settings['show_support_tab'], "yes" ) ?> value="yes"  />
             </div>
             <div class="setting show_support_tab">
                   <label for="tab_message_on_submit"><?php _e( "Tab message on ticket submission","kanzu-support-desk" ); ?></label>
                   <textarea cols="60" rows="4" name="tab_message_on_submit"><?php echo $settings['tab_message_on_submit']; ?></textarea>
             </div>
             <div class="setting">
                <label for="tour_mode"><?php _e( "Enable tour mode","kanzu-support-desk" ); ?></label>                
                <img width="16" height="16" src="<?php echo KSD_PLUGIN_URL."/assets/images/help.png";?>" class="help_tip" title="<?php _e("Refresh your page after enabling this and tour mode will start automatically",'kanzu-support-desk')  ;?>"/>
                <input name="tour_mode"  type="checkbox" <?php checked( $settings['tour_mode'], "yes" ) ?> value="yes"  />
             </div>
        </div>   
             <?php 
             //Retrieve extra settings from add-ons. Pass current settings to them
             do_action( 'ksd_display_settings', $settings );  
  
             //Retrieve 'Licenses' tab if any licenses exist. This is true if one or more add-ons have been activated
             $settings_and_licenses  =   apply_filters( 'ksd_display_licenses', $settings ); 
             $licenses      = ( isset( $settings_and_licenses['licenses'] ) ? $settings_and_licenses['licenses'] : array() ) ;
             if ( count($licenses) > 0 ) {//If some licenses were retrieved, display the licenses tab ?>
                <h3><?php _e("Licenses","kanzu-support-desk"); ?></h3>
                <div>                    
                    <?php //Iterate through the licenses and display them
                    foreach ( $licenses as $license_details ):?>
                        <div class="setting">
                            <label for="<?php echo $license_details['license_db_key']; ?>"><?php echo $license_details['addon_name']; ?></label>
                            <input type="text" value="<?php echo $license_details['license']; ?>" size="30" name="<?php echo $license_details['license_db_key']; ?>" />
                            <?php if( $license_details['license_status'] == 'valid' ) { ?>
                                <span class="license_status valid"><?php _e( 'active', 'kanzu-support-desk' ); ?></span>
                                <input type="submit" class="button-secondary ksd-license ksd-deactivate_license" name="<?php echo $license_details['license_status_db_key']; ?>" value="<?php _e('Deactivate License','kanzu-support-desk'); ?>"/>
                            <?php } else { ?>
                                <span class="license_status <?php echo $license_details['license_status'];?>"><?php echo ( empty( $license_details['license'] ) ? __( 'not set','kanzu-support-desk') : __( 'invalid','kanzu-support-desk') );  ?></span>
				<input type="submit" class="button-secondary ksd-license ksd-activate_license" name="<?php echo $license_details['license_status_db_key']; ?>" value="<?php _e('Activate License','kanzu-support-desk'); ?>"/>
                            <?php } ?>
                        </div>                  
                    <?php endforeach;
                    ?>
                </div>
                 <?php
             }   
             ?>   
    </div><!--.ksd-settings-accordion-->
    <input name="action" type="hidden" value="ksd_update_settings" />    
    <?php wp_nonce_field( 'ksd-update-settings', 'update-settings-nonce' ); ?>
    <input type="submit" value="<?php _e( "Update","kanzu-support-desk" ); ?>" name="ksd-settings-submit" class="ksd-submit button button-primary button-large"/>
    <input type="submit" value="<?php _e( "Reset to Defaults","kanzu-support-desk" ); ?>" name="ksd-settings-reset" class="ksd-submit ksd-reset button action button-large"/>
 </form>
