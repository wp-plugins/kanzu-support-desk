<div id="admin-kanzu-support-desk" >
    <div class="admin-ksd-title">
        <?php $ksd_current_page = str_ireplace( 'ksd-', '', sanitize_key( $_GET['page'] ) );
             $ksd_current_page_name = array();//We do this to internationalize the names
             $ksd_current_page_name[ 'dashboard' ]  =   __( 'Dashboard','kanzu-support-desk' );
             $ksd_current_page_name[ 'settings' ]   =   __( 'Settings','kanzu-support-desk' );
             $ksd_current_page_name[ 'addons' ]     =   __( 'Add-ons','kanzu-support-desk' );
        ?>
        <h2><?php echo $ksd_current_page_name[ $ksd_current_page]; ?></h2>
        <span class="more_nav"><img src="<?php echo KSD_PLUGIN_URL. '/assets/images/icons/more_top.png'; ?>" title="<?php _e('Notifications','kanzu-support-desk'); ?>" /></span>
    </div>
    <div class="admin-ksd-container">
        <div id="<?php echo $ksd_current_page;?>" class="admin-ksd-content">
            <?php include_once('html-admin-'.$ksd_current_page.'.php'); ?>
        </div>  
        <div class="ksd-dialog loading hidden"><?php __( 'Loading...', 'kanzu-support-desk'); ?></div>
        <div class="ksd-dialog error hidden"><?php __( 'Error...', 'kanzu-support-desk'); ?></div>
        <div class="ksd-dialog success hidden"><?php __( 'Success...', 'kanzu-support-desk'); ?></div> 
        <div id="ksd-notifications"><?php _e( 'Loading...', 'kanzu-support-desk'); ?></div>
    </div>
</div>
