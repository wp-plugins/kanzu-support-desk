<p><?php _e( 'Take your Kanzu Support Desk experience to the next level by activating an add-on', 'kanzu-support-desk'); ?></p>
<?php 
do_action( 'ksd_load_addons' );

    $prospective_addons = array();
    $prospective_addons[] = array(
        'name' => 'Customer Satisfaction',
        'image' => 'feedback',
        'description' => 'Get to know how well your customer service is performing by asking your customer to rate their experience. Get an NPS score that shows your performance'
    );
    $prospective_addons[] = array(
        'name' => 'Reports',
        'image' => 'reports',
        'description' => 'Get detailed reports in your dashboard showing top support agents, tickets by channel, average first reply time, etc '
    );
    $prospective_addons[] = array(
        'name' => 'Twitter',
        'image' => 'twitter',
        'description' => 'Have tickets automatically created from tweets. Handle the new tickets like you would any other ticket'
    );
    $prospective_addons[] = array(
        'name' => 'Facebook',
        'description' => 'Automatically turn posts on your wall into tickets. Respond to these posts from inside Kanzu Support Desk',
        'image' => 'facebook'
    );
    $prospective_addons[] = array(
        'name' => 'Knowledge Base',
        'description' => 'Over 50% of your customers prefer self-service; empower them to do just that with this add-on. Create tutorials & in-depth articles to help your DIY customer.',
        'image' => 'knowledge'
    );
    $prospective_addons[] = array(
        'name' => 'Live Chat',
        'image' => 'chat',
        'description' => 'Your customer prefers a quick chat with a real human being; give them just that. Add a live chat window to your site.'
    );
    $prospective_addons[] = array(
        'name' => 'Labels',
        'image' => 'labels',
        'description' => 'Add tags to your tickets to differentiate them based on product, service, company or whatever you choose.'
    );
    shuffle( $prospective_addons );//Shuffle the array to eliminate item position from being a factor
    ?><span class="ksd-dummy-addons"><?php
    foreach ($prospective_addons as $dummy_addon):?>
        <li class = "ksd-feed-add-on ksd-dummy">
            <h3><?php echo $dummy_addon['name']; ?></h3> 
            <img width = "128" height = "128" alt = "<?php echo $dummy_addon['name']; ?>" class = "attachment-post-thumbnail wp-post-image" src = "<?php echo KSD_PLUGIN_URL.'/assets/images/icons/dummy-addons/'.$dummy_addon['image'].'.png'; ?>">
                <p></p><p><?php echo $dummy_addon['description']; ?></p>
                <p class="ksd-price">Price: $29/year</p>
            <a class = "button button-primary button-large" target = "_blank" href = "#">Purchase this add-on</a>
        </li>
    <?php endforeach; ?>
    </span>
    <div id="ksd-dummy-plugin-dialog" class="hidden" title="<?php _e('Sorry, under construction...', 'kanzu-support-desk'); ?>">
        <span class="ksd-addon-name"></span><?php _e( " is not yet ready for use. Would you like us to let you know when it's ready? Or, just anonymously let us know that it is interesting.", 'kanzu-support-desk'); ?>
    </div>
