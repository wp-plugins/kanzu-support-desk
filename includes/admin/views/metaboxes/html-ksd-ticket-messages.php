<div id="ksd-messages-metabox">
    <div id="ksd-ticket-message">
        <?php $ksd_cc = get_post_meta ( $the_ticket->ID, '_ksd_tkt_info_cc', true ); 
        if ( ! empty ( $ksd_cc ) ) : ?><div class="ksd-ticket-cc"><?php _e ( 'CC', 'ksd-support-desk' ) ; ?>:<span class="ksd-cc-emails"><?php echo $ksd_cc; ?></span></div><?php endif; ?>
        <?php echo $the_ticket->post_content; ?></div>
    <ul id="ksd-ticket-replies" class="pending"><?php _e('Loading Replies...', 'kanzu-support-desk'); ?></ul>   
    <div id="edit-ticket-tabs"> 
        <ul class="edit-ticket-options">
            <li><a href="#reply_ticket"><?php _e('Public Reply', 'kanzu-support-desk'); ?></a></li>
            <li><a href="#update_private_note"><?php _e('Private Note', 'kanzu-support-desk'); ?></a></li>
        </ul>
        <div class="edit-ticket-description" id="reply_ticket">
            <input type="text" value="<?php _e('CC', 'kanzu-support-desk'); ?>" maxlength="255" name="ksd_tkt_cc" label="<?php _e('CC', 'kanzu-support-desk'); ?>" class="ksd-cc" minlength="2" style="display:none;" data-rule-ccRule /> 
            <?php wp_editor('', 'ksd_ticket_reply', array("media_buttons" => true, "textarea_rows" => 5)); ?> 
        </div>
        <div id="update_private_note" class="single-ticket-textarea">
            <textarea name="tkt_private_note" rows="5" cols="100"></textarea> 
        </div>
    </div> 
    <?php wp_nonce_field( 'ksd-add-new-reply', 'ksd_new_reply_nonce' ); ?>
    <div class="ksd-reply-submit-wrapper">
        <span class="spinner ksd-reply-spinner hidden"></span>
        <input type="submit" value="<?php _e('Send', 'kanzu-support-desk'); ?>" name="ksd_reply_ticket" id="ksd-reply-ticket-submit" class="button button-primary button-large ksd-submit"/>         
    </div>
</div>  