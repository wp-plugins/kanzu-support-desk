<?php global $post;?>
<ul id="ksd-ticket-replies" class="ticket-<?php echo $post->ID; ?>">
    <?php   $ksd_admin =  KSD_Admin::get_instance();
            $replies = $ksd_admin->do_get_ticket_replies_and_notes( $post->ID, false );
            foreach ( $replies as $reply ): ?>
        <li class="ticket-reply">
            <span class="reply_author"><?php echo $reply->post_author; ?></span>
            <span class="reply_date"><?php echo $reply->post_date; ?></span>
            <div class="reply_message">
                <p><?php echo $reply->post_content; ?></p>
            </div>
        </li>
    <?php endforeach; ?>
</ul>