<h3><?php _e( "Documentation","kanzu-support-desk" ); ?></h3>
    <p><?php _e( "Kindly go through readme.txt. If the instructions there aren't sufficient, please visit: <a href='https://kanzucode.com/documentation/wordpress-customer-service-plugin-ksd-getting-started/' target='_blank'>Kanzu Support Desk Documentation</a>","kanzu-support-desk" ); ?></p>
<h3><?php _e( "Support","kanzu-support-desk" ); ?></h3>
    <p><?php _e( "If you have any trouble, please get in touch with us: <a href='http://kanzucode.com/support' target='_blank'>Kanzu Support</a>","kanzu-support-desk" ); ?></p>
<h3><?php _e( "Spread the Love","kanzu-support-desk" ); ?></h3>
    <p><?php _e( "We are working to make providing great customer service easy. Please support us by <a href='https://wordpress.org/support/view/plugin-reviews/kanzu-support-desk' target='_blank' class='button button-primary'>Rating Us</a> now","kanzu-support-desk" ); ?></p>
<h3><?php _e( "Feedback","kanzu-support-desk" ); ?></h3>
    <form action="#" id="ksd-feedback" method="POST">
        <p><?php _e( "We are all about making KSD better. We'd truly, truly love to hear from you. What's your experience with <strong>Kanzu Support Desk</strong>? What do you like? What do you love? What don't you like? What do you want us to fix or improve?","kanzu-support-desk" ); ?></p>
        <p><textarea name="ksd_user_feedback" rows="5" cols="100"></textarea></p>
        <input name="action" type="hidden" value="ksd_send_feedback" />
        <?php wp_nonce_field( 'ksd-send-feedback', 'feedback-nonce' ); ?>
        <p><input type="submit" class="button-primary" name="ksd-feedback-submit" value="<?php _e('Send','kanzu-support-desk'); ?>"/><span class="feedback_note"><?php _e( "PS: This sends us an email","kanzu-support-desk");?></span></p>
    </form>
<h3><?php _e( "Stay on top!","kanzu-support-desk" ); ?></h3>
    <p><?php _e( "The KSD team's constantly improving your experience. Get the latest tips fresh off the keyboard","kanzu-support-desk" ); ?></p>
    <div id="mc_embed_signup">
        <form action="//kanzucode.us6.list-manage.com/subscribe/post?u=072b3e28db&amp;id=dc9cab2edd" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
            <div id="mc_embed_signup_scroll"> 
                <div class="mc-field-group">
                    <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span></label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                <div class="mc-field-group">
                   <label for="mce-FNAME">First Name  <span class="asterisk">*</span></label>
                   <input type="text" value="" name="FNAME" class="required" id="mce-FNAME">
                </div>
            <div class="mc-field-group">
                    <label for="mce-LNAME">Last Name </label>
                    <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
            </div>
            <div class="mc-field-group input-group">
            <strong>Kanzu Code Newsletters </strong>
            <ul>
                <li><input type="checkbox" value="1" name="group[14649][1]" id="mce-group[14649]-14649-0"><label for="mce-group[14649]-14649-0">Kanzu Support Desk</label></li>
                <li><input type="checkbox" value="2" name="group[14649][2]" id="mce-group[14649]-14649-1"><label for="mce-group[14649]-14649-1">Start-up journey</label></li>
            </ul>
            </div>
            <div id="mce-responses" class="clear">
		<div class="response" id="mce-error-response" style="display:none"></div>
		<div class="response" id="mce-success-response" style="display:none"></div>
            </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
            <div style="position: absolute; left: -5000px;"><input type="text" name="b_072b3e28db_dc9cab2edd" tabindex="-1" value=""></div>
            <div class="clear"><input type="submit" value="I'm in!" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
            </div>
        </form>
    </div>
<!--End mc_embed_signup-->
<!--Display help messages from add-ons-->
<?php do_action('ksd_display_help')?>