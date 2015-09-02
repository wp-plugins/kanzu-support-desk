<div class="ksd-misc-severity misc-pub-section">
    <span><?php _e('Severity: ','kanzu-support-desk'); ?></span>
    <?php   $the_severity = get_post_meta( $post->ID, '_ksd_tkt_info_severity', true );
            $current_severity = ( empty( $the_severity ) ? 'low' : $the_severity ); ?>
    <span class="ksd-misc-value <?php echo $current_severity; ?>" id="ksd-misc-current-severity"><?php echo $current_severity; ?></span>
    <a href="#severity" class="edit-severity"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_severity ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_severity"> 
            <?php foreach ( $this->get_severity_list()  as $severity_label => $severity ) : ?>
                    <option value="<?php echo $severity_label; ?>" 
                    <?php selected( $severity_label, get_post_meta( $post->ID, '_ksd_tkt_info_severity', true ) ); ?>> 
                    <?php echo $severity; ?>  
                    </option>
            <?php endforeach; ?>
        </select>
        <a class="save-severity button" href="#severity"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-severity button-cancel" href="#severity"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<div class="ksd-misc-assign-to misc-pub-section">
    <span><?php _e('Assigned To:','kanzu-support-desk'); ?></span>
    <span class="ksd-misc-value" id="ksd-misc-assign-to"><?php    $assigned_to_ID = get_post_meta( $post->ID, '_ksd_tkt_info_assigned_to', true ); 
        $assigned_to = ( 0 == $assigned_to_ID || empty ( $assigned_to_ID ) ? __( 'No One', 'kanzu-support-desk' ) : get_userdata ( $assigned_to_ID )->display_name  );
        echo    $assigned_to; ?></span>
    <a href="#assign-to" class="edit-assign-to"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_assigned_to ksd_tkt_info_wrapper hidden">    
        <select name="_ksd_tkt_info_assigned_to">
            <?php foreach ( get_users() as $agent ) { ?>
            <option value="<?php echo $agent->ID; ?>" 
                <?php selected( $agent->ID, get_post_meta( $post->ID, '_ksd_tkt_info_assigned_to', true ) ); ?>> 
                <?php echo $agent->display_name; ?>  
            </option>
            <?php }; ?>
            <option value="0"><?php _e('No One', 'kanzu-support-desk'); ?></option>
        </select>
        <a class="save-assign-to button" href="#assign-to"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-assign-to button-cancel" href="#assign-to"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
</div>
<input type="hidden" value="<?php echo $post->post_status; ?>" id="hidden_ksd_post_status" name="hidden_ksd_post_status"><!--On change, save the ticket status-->
<input type="hidden" value="admin-form" id="_ksd_tkt_info_channel" name="_ksd_tkt_info_channel"><!--On change, save the ticket status-->
<div class="ksd-misc-customer misc-pub-section">
    <span><?php _e('Customer: ','kanzu-support-desk');  ?></span>
    <span class="ksd-misc-value" id="ksd-misc-customer"><?php echo get_userdata( $post->post_author )->display_name; ?></span>
    <?php if( !isset( $_GET['post'] ) ):?><a href="#customer" class="edit-customer"><?php _e( 'Edit','kanzu-support-desk' ); ?></a>
    <div class="ksd_tkt_info_customer ksd_tkt_info_wrapper hidden">
        <select name="_ksd_tkt_info_customer"> 
            <?php
                $ksd_customer_list      = get_users( array('role' => 'ksd_customer' ) );
                $ksd_customer_list[]    = get_userdata( $post->post_author );
                foreach ( $ksd_customer_list  as $ksd_customer ) : ?>
                    <option value="<?php echo $ksd_customer->ID; ?>" 
                    <?php selected( $ksd_customer->ID , $post->post_author ); ?>> 
                        <?php echo $ksd_customer->display_name; ?>  
                    </option>
            <?php endforeach; ?>
        </select>
        <a class="save-customer button" href="#customer"><?php _e( 'OK','kanzu-support-desk' ); ?></a>
        <a class="cancel-customer button-cancel" href="#customer"><?php _e( 'Cancel','kanzu-support-desk' ); ?></a>
    </div>
    <?php endif;?>
</div>