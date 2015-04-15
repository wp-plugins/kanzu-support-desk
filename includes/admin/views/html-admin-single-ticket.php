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
            <li>OPEN</li><!--@TODO Internalize this. CAN only be done after changing receiving logic to map item position to these corresponding values for correct storage in the Db -->
            <li>PENDING</li>
            <li>RESOLVED</li>
        </ul>
    </li>
    <li>
        <a href="#" class="add-new-h2 change_severity"><?php _e('Change Severity','kanzu-support-desk'); ?></a>
        <ul class="severity hidden">
            <li>LOW</li>
            <li>MEDIUM</li>
            <li>HIGH</li>
            <li>URGENT</li>
        </ul>
    </li>
</ul>
<div id="ksd-single-ticket">
    <h1 class="ksd-single-ticket-subject"></h1>
    <div class="author_and_date">
        <span class="author"><?php  _e('Loading...','kanzu-support-desk');?></span>
        <span class="date"></span>
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
                <textarea name="ksd_ticket_reply" rows="5" cols="100"></textarea> 
            </div>
           <div id="update_private_note" class="single-ticket-textarea">
                <textarea name="tkt_private_note" rows="5" cols="100"></textarea> 
            </div>
       </div>
        <input name="action" type="hidden" value="ksd_reply_ticket" />
        <input name="tkt_id" type="hidden" value="<?php echo $_GET['ticket'];?>" />  
        <input name="ksd_rep_created_by" type="hidden" value="<?php echo get_current_user_id();?>" />           
        <?php wp_nonce_field( 'ksd-edit-ticket', 'edit-ticket-nonce' ); ?>
        <input type="submit" value="<?php  _e('Reply','kanzu-support-desk');?>" name="edit-ticket" id="edit-ticket-submit" class="button button-primary button-large ksd-submit"/>        
    </form>
  </div>
</div>