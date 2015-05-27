<div id="admin-kanzu-support-desk">
    <div class="admin-ksd-title">
        <h2><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
        <span class="more_nav"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/more_top.png'; ?>" title="<?php _e('Notifications','kanzu-support-desk'); ?>" /></span>
    </div>
	<div id="tabs" class="admin-ksd-container">
		<ul class="ksd-main-nav">
			<li><a href="#dashboard"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/dashboard.png'; ?>" title="<?php _e('Home','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#tickets"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/tickets.png'; ?>" title="<?php _e('Inbox','kanzu-support-desk'); ?>" /></a></li>
                        <li><a href="#new_ticket"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/newticket.png'; ?>" title="<?php _e('New Ticket','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#settings"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/settings.png'; ?>" title="<?php _e('Settings','kanzu-support-desk'); ?>"/></a></li>
			<li><a href="#add-ons"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/addons.png'; ?>" title="<?php _e('Add-ons','kanzu-support-desk'); ?>" /></a></li>
			<li><a href="#help"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/help.png'; ?>" title="<?php _e('Help','kanzu-support-desk'); ?>" /></a></li>	
		</ul>
		<div id="dashboard" class="admin-ksd-content">
			<?php include_once('html-admin-dashboard.php'); ?>
		</div>
		<div id="tickets" class="admin-ksd-content">
			<?php include_once('html-admin-tickets.php'); ?>
		</div>
                <div id="new_ticket" class="admin-ksd-content">
			<?php include_once('html-admin-new-ticket.php'); ?>
		</div>
		<div id="settings" class="admin-ksd-content">
			<?php include_once('html-admin-settings.php'); ?>
		</div>
		<div id="add-ons" class="admin-ksd-content">
			<?php include_once('html-admin-addons.php'); ?>
		</div>
		<div id="help" class="admin-ksd-content">
			<?php include_once('html-admin-help.php'); ?>
		</div>   
            <div class="ksd-dialog loading hidden"><?php __( 'Loading...', 'kanzu-support-desk'); ?></div>
            <div class="ksd-dialog error hidden"><?php __( 'Error...', 'kanzu-support-desk'); ?></div>
            <div class="ksd-dialog success hidden"><?php __( 'Success...', 'kanzu-support-desk'); ?></div> 
            <div class="ksd-loading-tickets ksd-loading-tickets-overlay hidden"></div> 
            <div id="ksd-notifications"><?php _e( 'Loading...', 'kanzu-support-desk'); ?></div>
            <div class="ticket-actions-top-menu hidden">
                <a class="trash" href="#"><?php _e( 'Trash', 'kanzu-support-desk'); ?></a> |
                <a class="change_status" href="#"><?php _e( 'Change Status', 'kanzu-support-desk'); ?></a> |
                <a class="assign_to" href="#"><?php _e( 'Assign To', 'kanzu-support-desk'); ?></a>     
                <ul class="status hidden">
                    <li class="OPEN"><?php _e( 'OPEN', 'kanzu-support-desk'); ?></li>
                    <li class="PENDING"><?php _e( 'PENDING', 'kanzu-support-desk'); ?></li>
                    <li class="RESOLVED"><?php _e( 'RESOLVED', 'kanzu-support-desk'); ?></li>
                </ul>
                <?php echo KSD_Admin::get_agent_list(); ?>
            </div>
    </div>
</div>