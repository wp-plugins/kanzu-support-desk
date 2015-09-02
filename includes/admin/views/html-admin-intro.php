<div id="ksd-intro-one" class="wrap about-wrap">
    <div class="ceo-caricature"></div>
    <?php 
    global $current_user;
    $ksd_display_message = sanitize_key( $_GET['ksd-intro'] );
    if ( $ksd_display_message == 1 ): ?>
        <h1>Welcome to Kanzu Support Desk</h1>
        <div class="about-text"><!--@TODO Internationalize this-->
            Hi <?php echo $current_user->display_name; ?>,<br />
            I’m Kakoma, a retired ninja and the Lead Developer at <span class="ksd-blue">Kanzu Code</span>, the team behind this plugin. Thanks for choosing <span class="ksd-blue">Kanzu Support Desk (KSD)</span>. We built it to simplify giving great, personal customer support to all those who look to you for it. We had a small business, like ours, in mind while building it. <br /><br />
            We focused a lot on simplicity, with the goal of making it as simple to use as WordPress itself. I’m glad you’ve chosen to use it. 
            To get you started, we've added a few sample tickets so you can see what everything looks like.<br />
            Quick one though – <span class="ksd-blue">why did you choose KSD? What features do you hope to find here?</span><br /><br />
            I ask this because it is essential in making sure we deliver on what you want. Hit ‘reply’ below and I’ll get your message. <br />
            <?php echo KSD_Admin::output_feeback_form('intro','Reply'); ?>        
            Thanks,<br />
            Kakoma,<br />
            Lead Developer, <span class="ksd-blue">Kanzu Code</span>
            <br />
            <p>PS: If you do run into any issues (or have any feedback whatsoever), get in touch on <a href="mailto:feedback@kanzucode.com">feedback@kanzucode.com</a></p>
            <a class="button-primary ksd-start-intro" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket' ); ?>">Start using KSD</a>
        </div>
    <?php elseif ( $ksd_display_message == 'v200' ): ?>
        <h1>Welcome to Kanzu Support Desk 2.0.0</h1>
        <div class="about-text"><!--@TODO Internationalize this-->
            Hi <?php echo $current_user->display_name; ?>,<br />
            I’m Kakoma, a retired ninja and the Lead Developer at <span class="ksd-blue">Kanzu Code</span>, the team behind this plugin. A few weeks ago, based on your feedback and our usage of the plugin, we discovered a major flaw
            in the way we were handling tickets - there was no way for your customer to easily follow a ticket's progress. To address this, we had to overhaul a very big part of KSD. Dark, hard days I tell you. <br /><br />
            Today though, you get to enjoy an enhanced KSD experience - <span class="ksd-blue">KSD 2.0.0</span>. You will immediately notice that it is very different from the previous versions; it looks a lot more like WordPress now. In your settings,
            you'll need to use the <span class="ksd-blue">Migration Assistant</span> to move your tickets into this new version.<br /><br />
            This version gives you much better interaction with your customer. We hope you like it.<br />    
            Thanks,<br />
            Kakoma,<br />
            Lead Developer, <span class="ksd-blue">Kanzu Code</span>
            <br />
            <p>PS: If you do run into any issues (or have any feedback whatsoever), get in touch on <a href="mailto:feedback@kanzucode.com">feedback@kanzucode.com</a></p>
            <a class="button-primary ksd-start-intro" href="<?php echo admin_url( 'edit.php?post_type=ksd_ticket&page=ksd-settings&active_tab=migration' ); ?>">Start Migration</a>
        </div>    
    <?php else: ?>
        <div id="ksd-intro-weekone" class="about-text">
            One week in! Yay! Kakoma here again, the retired ninja. How’s KSD so far? Do share your thoughts; hit reply below. One tiny thing though – could you enable usage & error statistics? Those figures give us the plugin’s performance, usage and customization data that allows us make it more useful, stable and secure.
            <br />
            <button class="ksd_enable_usage_stats button-primary">Enable Usage & Error Statistics</button>
            <br /><br />
            Thanks,<br />
            Kakoma,<br />
            Lead Developer, Kanzu Code
            <h4>Thoughts on KSD so far</h4>
                <?php echo KSD_Admin::output_feeback_form( 'oneweek','Reply' ); ?>
            <br />
            <a class="button-primary ksd-so-far" href="<?php echo admin_url( 'admin.php?page='.KSD_SLUG ); ?>">Back to KSD</a>
        </div>
    <?php endif; ?>
</div>