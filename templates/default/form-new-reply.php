<?php
    wp_editor( '',  'ksd-public-new-reply', array( "textarea_rows" => 5 ) );
?>
<div id="ksd-public-reply-error" class="hidden"></div>
<div id="ksd-public-reply-success" class="hidden"></div>
<?php wp_nonce_field( 'ksd-add-new-reply', 'ksd_new_reply_nonce' ); ?>
<span class="spinner ksd-public-spinner"></span>
<input type="submit" value="<?php _e('Send', 'kanzu-support-desk'); ?>" name="ksd_reply_ticket" id="ksd-public-reply-submit" class="button button-primary button-large ksd-submit"/>