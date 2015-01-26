<ul class="ksd-top-nav wrap">
    <li class="back"><a href="<?php echo admin_url('admin.php?page=ksd-tickets'); ?>" class="add-new-h2"><?php _e('Back','kanzu-support-desk'); ?></a></li>
    <li><a href="<?php echo admin_url('admin.php?page=ksd-new-ticket'); ?>" class="add-new-h2"><?php _e('New Ticket','kanzu-support-desk'); ?></a></li>
</ul>
<div id="ksd-single-ticket">
    <div class="author_and_subject"><?php  _e('Loading...','kanzu-support-desk');?></div>
    <div class="description pending">
        <?php  _e('Loading...','kanzu-support-desk');?>
    </div>
    <div id="ticket-replies">
        <?php  _e('Loading...','kanzu-support-desk');?>
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
        <?php wp_nonce_field( 'ksd-edit-ticket', 'edit-ticket-nonce' ); ?>
        <input type="submit" value="<?php  _e('Reply','kanzu-support-desk');?>" name="edit-ticket" id="edit-ticket-submit" class="button button-primary button-large ksd-submit"/>        
    </form>
  </div>
</div>