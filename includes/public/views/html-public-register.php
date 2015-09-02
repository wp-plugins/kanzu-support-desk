<?php
/* 
 * Template for our custom registration forms. It is used for two registration forms:
 *      1. The one that slides in and out when the public support button is clicked
 *      2. The one that's displayed on pages/posts that have the [ksd_my_tickets] shortcode WHEN a user isn't logged in
 *         Note that in this second scenario, if the user is logged in, templates/default/list-my-tickets.php is used
 * These two forms are differentiated using class $form_position_class. In case 1, the value is ksd-form-hidden-tab and it is ksd-form-short-code in case 2
 * 
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @since 2.0.0
 */
?>
<div class="ksd-register-form-wrap <?php echo $form_position_class; ?> hidden">
    <?php $settings = Kanzu_Support_Desk::get_settings();?>
    <div class="ksd-close-form-wrapper">
        <img src="<?php echo KSD_PLUGIN_URL.'assets/images/icons/close.png'; ?>" class="ksd_close_button" width="32" height="32" Alt="<?php __('Close','kanzu-support-desk'); ?>" />
    </div>
    <form method="POST" class="ksd-register-public <?php echo $form_position_class; ?>-form">
        <div class="ksd-register-description">
            <p><?php _e( 'Before you submit a ticket, you need to be logged in.', 'kanzu-support-desk' );  ?></p>
            <p><?php printf( __( 'If you already have an account, please %1$s login here %2$s. If you do not, register below.', 'kanzu-support-desk' ), '<a href="'.get_admin_url().'" class="button">', '</a>' );  ?></p>
        </div>
        <ul>
            <li class="ksd-first-name">               
                  <input type="text" value="<?php _e('First Name','kanzu-support-desk'); ?>" size="30" name="ksd_cust_firstname" label="<?php _e( 'First Name', 'kanzu-support-desk'); ?>" class="ksd-customer-name" minlength="2" required/>
            </li>
            <li class="ksd-last-name">               
                  <input type="text" value="<?php _e('Last Name','kanzu-support-desk'); ?>" size="30" name="ksd_cust_lastname" label="<?php _e( 'Last Name', 'kanzu-support-desk'); ?>" class="ksd-customer-name" minlength="2"/>
            </li>
            <li class="ksd-email">
                  <input type="email" value="<?php _e('Email','kanzu-support-desk'); ?>" size="30" name="ksd_cust_email" label="<?php _e( 'Customer Email', 'kanzu-support-desk'); ?>" class="ksd-customer-email" required/>
            </li>   
            <li class="ksd-username">               
                  <input type="text" value="<?php _e('Username','kanzu-support-desk'); ?>" size="30" name="ksd_cust_username" label="<?php _e( 'Username', 'kanzu-support-desk'); ?>" class="ksd-customer-name" minlength="2" required/>
            </li>    
            <li class="ksd-password">       
                <input type="password" value="" maxlength="32" name="ksd_cust_password" label="<?php _e( 'Password', 'kanzu-support-desk'); ?>" class="ksd-password" minlength="8" placeholder="<?php _e( 'Password', 'kanzu-support-desk'); ?>"  required/>
            </li>
        <!--Add Google reCAPTCHA-->
        <?php if( "yes" == $settings['enable_recaptcha'] && $settings['recaptcha_site_key'] !== '' ): ?>
            <li class="ksd-g-recaptcha">
                <span class="ksd-g-recaptcha-error"></span>
                <div class="g-recaptcha" data-sitekey="<?php echo $settings['recaptcha_site_key']; ?>"></div>
            </li>
        <?php endif; ?>
            <li class="ksd-show-password"><input type="checkbox" name="ksd_cust_show_password" /><?php _e( 'Show Password','kanzu-support-desk' ); ?></li>
            <li class="ksd-public-submit">
              <img src="<?php echo KSD_PLUGIN_URL.'assets/images/loading_dialog.gif'; ?>" class="hidden ksd_loading_dialog" width="45" height="35" />
              <input type="submit" value="<?php _e( 'Register', 'kanzu-support-desk'); ?>" name="ksd-submit-tab-register" class="ksd-submit"/>
            </li>

        </ul>
        <input name="action" type="hidden" value="ksd_register_user" />
        <input name="ksd_tkt_channel" type="hidden" value="support_tab" />
        <?php wp_nonce_field( 'ksd-register', 'register-nonce' );?>
    </form>
    <div class="<?php echo $form_position_class; ?>-form-response ksd-register-public-response hidden"></div>
</div>