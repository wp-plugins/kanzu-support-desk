<h3> <?php _e( 'Migration Assistant', 'kanzu-support-desk'); ?> </h3>
    <div>
        <div class=setting>
            <span class="spinner ksd-migration-spinner"></span>
            <?php 
            $ksd_v2migration_status  = get_option( 'ksd_v2migration_status' ); 
            if( 'ksd_v2migration_deletetables' == $ksd_v2migration_status  ):?>   
                <a class="button action button-large" id="ksd-migration-deletetickets" href="#"><?php  _e("Delete tables", 'kanzu-support-desk'); ?> </a>
            <?php else:?>
                <a class="button action button-large" id="ksd-migration-tickets" href="#"><?php  _e("Migrate old tickets", 'kanzu-support-desk'); ?> </a>
            <?php endif;?>            
            <div id="ksd-migration-status"></div>
        </div>
    </div>                   