<div class="ksd-new-ticket-form-wrap ksd-form-short-code">
    <?php $settings = Kanzu_Support_Desk::get_settings(); ?>
        <div class="ksd-close-form-wrapper">
            <img src="<?php echo KSD_PLUGIN_URL.'assets/images/icons/close.png'; ?>" class="ksd_close_button" width="32" height="32" Alt="<?php __('Close','kanzu-support-desk'); ?>" />
        </div>
        <form method="POST" class="ksd-new-ticket-public ksd-form-short-code-form">
            <ul>      
            <li class="ksd-subject">       
              <input type="text" value="<?php _e('Subject','kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_subject" label="Subject" class="ksd-subject" minlength="2" required/>
            </li>
              <li class="ksd-message">     
                  <textarea value="<?php _e('Message','kanzu-support-desk'); ?>" rows="7" class="ksd-message" name="ksd_tkt_message" required></textarea>
              </li>
            <!--Add Google reCAPTCHA-->
            <?php if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ): ?>
                <li class="ksd-g-recaptcha">
                    <span class="ksd-g-recaptcha-error"></span>
                    <div class="g-recaptcha" data-sitekey="<?php echo $settings['recaptcha_site_key']; ?>"></div>
                </li>
            <?php endif; ?>
              <li class="ksd-public-submit">
                <img src="<?php echo KSD_PLUGIN_URL.'assets/images/loading_dialog.gif'; ?>" class="hidden ksd_loading_dialog" width="45" height="35" />
                <input type="submit" value="<?php _e( 'Send Message', 'kanzu-support-desk'); ?>" name="ksd-submit-tab-new-ticket" class="ksd-submit"/>
              </li>
            </ul>
            <input name="action" type="hidden" value="ksd_log_new_ticket" />
            <input name="ksd_tkt_channel" type="hidden" value="support-tab" />
            <?php wp_nonce_field( 'ksd-new-ticket', 'new-ticket-nonce' ); ?>
        </form>
        <div class="ksd-form-short-code-form-response ksd-public-form-response hidden"></div>
    </div>