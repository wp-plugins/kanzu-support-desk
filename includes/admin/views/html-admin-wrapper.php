<div id="admin-kanzu-support-desk">
    <div class="admin-ksd-title">
        <h2><?php _e('Dashboard','kanzu-support-desk'); ?></h2>
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
            <div class="ksd-dialog loading hidden">Loading...</div>
            <div class="ksd-dialog error hidden">Error</div>
            <div class="ksd-dialog success hidden">Success</div> 
            <div class="ksd-loading-tickets ksd-loading-tickets-overlay hidden"></div> 
    </div>
</div>