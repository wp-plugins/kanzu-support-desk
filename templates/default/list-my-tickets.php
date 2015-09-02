<ul id="ksd-list-my-tickets">
    <?php
        global $current_user;//Current user
        $ksd_admin =  KSD_Admin::get_instance();
        $my_tickets = $ksd_admin->get_customer_tickets( $current_user->ID );
        
        if ( $my_tickets->have_posts() ) :  
            while ( $my_tickets->have_posts() ) : $my_tickets->the_post(); ?>
            <li class="ksd-my-ticket">
                <span class="ksd-my-ticket-status <?php echo get_post_status() ; ?>"><?php echo get_post_status() ; ?></span>
                <span class="ksd-my-ticket-title"><a href='<?php the_permalink(); ?>'><?php the_title(); ?></a>                </span>
                <span class="ksd-my-ticket-date"><?php echo get_post_modified_time( 'd M Y, @ H:i' ); ?></span>                
            </li><?php
            endwhile;
            wp_reset_postdata();//Restore original Post Data
        else:
           echo '<li>'. __( 'You have not logged any tickets yet','kanzu-support-desk' ).'</li>';
        endif;?>        
</ul>
<?php $current_settings = Kanzu_Support_Desk::get_settings(); ?>
<a class="button button-large button-primary ksd-button" href="<?php echo get_permalink( $current_settings['page_submit_ticket'] ); ?>"><?php _e( 'Submit Ticket', 'kanzu-support-desk' ); ?></a>