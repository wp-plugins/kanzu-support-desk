<?php
global $tab_id;
if ( isset($_GET['ticket']) ):  
        include_once('html-admin-single-ticket.php');
  else: ?>
	<div id="ticket-tabs" class="admin-ksd-tickets-container">
		<ul>
			<li><a href="#tickets-tab-1"><?php _e('My unresolved','kanzu-support-desk'); ?></a>|</li>
			<li><a href="#tickets-tab-2"><?php _e('All','kanzu-support-desk'); ?></a>|</li>
			<li><a href="#tickets-tab-3"><?php _e('Unassigned','kanzu-support-desk'); ?></a>|</li>
			<li><a href="#tickets-tab-4"><?php _e('Recently updated','kanzu-support-desk'); ?></a>|</li>
			<li><a href="#tickets-tab-5"><?php _e('Recently resolved','kanzu-support-desk'); ?></a>|</li>
			<li><a href="#tickets-tab-6"><?php _e('Resolved','kanzu-support-desk'); ?></a></li>
		</ul>
		<div id="tickets-tab-1" class="admin-ksd-tickets-content pending">
			<?php $tab_id=1; include('html-admin-tickets-list-template.php'); ?>
		</div>
		<div id="tickets-tab-2" class="admin-ksd-tickets-content pending">
			<?php $tab_id=2; include('html-admin-tickets-list-template.php'); ?>
		</div>
		<div id="tickets-tab-3" class="admin-ksd-tickets-content pending">
			<?php $tab_id=3; include('html-admin-tickets-list-template.php'); ?>
		</div>
		<div id="tickets-tab-4" class="admin-ksd-tickets-content pending">
			<?php $tab_id=4; include('html-admin-tickets-list-template.php'); ?>
		</div>
		<div id="tickets-tab-5" class="admin-ksd-tickets-content pending">
			<?php $tab_id=6; include('html-admin-tickets-list-template.php'); ?>
		</div>
		<div id="tickets-tab-6" class="admin-ksd-tickets-content pending">
			<?php $tab_id=6; include('html-admin-tickets-list-template.php'); ?>
		</div>
            <div id="delete-dialog" class="hidden" title="<?php _e("Delete ticket","kanzu-support-desk"); ?>">
                <?php _e("Are you sure you want to delete this ticket and all data related to it?","kanzu-support-desk"); ?>
             </div>
	</div>
    
<?php endif; ?>
    
    