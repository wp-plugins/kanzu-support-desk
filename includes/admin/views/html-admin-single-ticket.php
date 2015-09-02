<ul class="ksd-top-nav wrap">
    <li class="back"><a href="<?php echo admin_url('admin.php?page=ksd-tickets'); ?>" class="add-new-h2"><?php _e('Back','kanzu-support-desk'); ?></a></li>
    <li><a href="<?php echo admin_url('admin.php?page=ksd-new-ticket'); ?>" class="add-new-h2"><?php _e('New Ticket','kanzu-support-desk'); ?></a></li>
    <li>
        <a href="#" class="add-new-h2 assign_to"><?php _e('Assign To','kanzu-support-desk'); ?></a>
        <?php echo KSD_Admin::get_agent_list(); ?>
    </li>
    <li>
        <a href="#" class="add-new-h2 change_status"><?php _e('Change Status','kanzu-support-desk'); ?></a>
        <ul class="status hidden">
            <li class="OPEN"><?php  _e('OPEN','kanzu-support-desk');?></li> 
            <li class="PENDING"><?php  _e('PENDING','kanzu-support-desk');?></li>
            <li class="RESOLVED"><?php  _e('RESOLVED','kanzu-support-desk');?></li>
        </ul>
    </li>
    <li>
        <a href="#" class="add-new-h2 change_severity"><?php _e('Change Severity','kanzu-support-desk'); ?></a>
        <ul class="severity hidden">
            <li class="LOW"><?php  _e('LOW','kanzu-support-desk');?></li>
            <li class="MEDIUM"><?php  _e('MEDIUM','kanzu-support-desk');?></li>
            <li class="HIGH"><?php  _e('HIGH','kanzu-support-desk');?></li>
            <li class="URGENT"><?php  _e('URGENT','kanzu-support-desk');?></li>
        </ul>
    </li>
    <li><a href="#" class="add-new-h2 mark_unread"><?php  _e('Mark as unread','kanzu-support-desk');?></a></li>
</ul>
<div id="ksd-single-ticket">
    <h1 class="ksd-single-ticket-subject"></h1>
    <div class="author_and_date">
        <span class="author"><?php  _e('Loading...','kanzu-support-desk');?></span>
        <span class="date"></span>
    </div>
    <div class="ticket_cc">
     <span class="ksd_cc"></span>
    </div>
    <div class="description pending">
        <?php  _e('Loading...','kanzu-support-desk');?>
    </div>
    <div id="ticket-replies">
        <p style="padding-left: 15px;" class="loading"><?php  _e('Loading...','kanzu-support-desk');?></p>
    </div>
    <div class="edit-ticket">
    <form id="edit-ticket" method="POST">
        
        <div id="edit-ticket-tabs"> 
            <ul class="edit-ticket-options">
                <li><a href="#reply_ticket"><?php _e('Reply','kanzu-support-desk'); ?></a></li>
                <li><a href="#update_private_note"><?php _e('Private Note','kanzu-support-desk'); ?></a></li>
            </ul>
 
            <div class="edit-ticket-description" id="reply_ticket">
                <input type="text" value="<?php _e( 'CC','kanzu-support-desk' ); ?>" maxlength="255" name="ksd_tkt_cc" label="<?php _e( 'CC','kanzu-support-desk' ); ?>" class="ksd-cc" minlength="2" style="display:none;" data-rule-ccRule /> 
                
                <?php wp_editor(  '' , 'ksd_ticket_reply', array( "media_buttons" => true, "textarea_rows" => 5 ) ); ?> 
                
            </div>
           <div id="update_private_note" class="single-ticket-textarea">
                <textarea name="tkt_private_note" rows="5" cols="100"></textarea> 
            </div>
       </div>        
        
        
        
        <input name="action" type="hidden" value="ksd_reply_ticket" />
        <input name="tkt_id" type="hidden" value="<?php echo $_GET['ksd_ticket'];?>" />  
        <input name="ksd_rep_created_by" type="hidden" value="<?php echo get_current_user_id();?>" />  
        <ul id="ksd_attachments-single-ticket" class="ksd-single-ticket">
        </ul>
        <?php wp_nonce_field( 'ksd-edit-ticket', 'edit-ticket-nonce' ); ?>

        <input type="submit" value="<?php  _e('Send','kanzu-support-desk');?>" name="edit-ticket" id="ksd-reply-ticket-submit" class="button button-primary button-large ksd-submit"/>        
        <span hascc="0" id="reply_toall_button" style="display: none; margin-left: 5px;" class=" button button-large" data=""><?php _e( 'Reply to all','kanzu-support-desk' ); ?></span>
    </form>
  </div>
</div>