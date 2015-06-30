<div id="ksd-intro-one" class="wrap about-wrap">
    <div class="ceo-caricature"></div>
    <?php 
    global $current_user;
    if ( $_GET['ksd-intro'] == 1 ): ?>
    <h1>Welcome to Kanzu Support Desk</h1>
    <div class="about-text">
        Hi <?php echo $current_user->display_name; ?>,<br />
        I’m Kakoma, a retired ninja, the CEO and founder of Kanzu Code, the team behind this plugin. Thanks for choosing Kanzu Support Desk (KSD). We built it to simplify giving great, personal customer support to all those who look to you for it. We had a small business, like ours, in mind while building it. <br /><br />
        We focused a lot on simplicity, with the goal of making it as simple to use as email. I’m glad you’ve chosen to use it. Quick one though – why did you choose KSD? What features do you hope to find here?<br /><br />
        I ask this because it is essential in making sure we, as a team, deliver on what our users want. Hit ‘reply’ below and I’ll get your message. <br />
        <?php echo KSD_Admin::output_feeback_form('intro','Reply'); ?>        
        Thanks,<br />
        Kakoma,<br />
        CEO, Kanzu Code
        <br />
        <p>PS: If you do run into any issues (or have any feedback whatsoever), we are just one click away in the <em>KSD Help</em> section.</p>
        <a class="button-primary ksd-start-intro" href="<?php echo admin_url( 'admin.php?page='.KSD_SLUG ); ?>">Start KSD Intro Tour</a>
    </div>
    <?php else: ?>
    <div id="ksd-intro-weekone" class="about-text">
        One week in! Yay! Kakoma here again, the retired ninja. How’s KSD so far? Do share your thoughts; hit reply below. One tiny thing though – could you enable usage & error statistics? Those figures give us the plugin’s performance, usage and customization data that allows us make it more useful, stable and secure.
        <br />
        <button class="ksd_enable_usage_stats button-primary">Enable Usage & Error Statistics</button>
        <br /><br />
        Thanks,<br />
        Kakoma,<br />
        CEO, Kanzu Code
        <h4>Thoughts on KSD so far</h4>
       <?php echo KSD_Admin::output_feeback_form('oneweek','Reply'); ?>
        <br />
        <a class="button-primary ksd-so-far" href="<?php echo admin_url( 'admin.php?page='.KSD_SLUG ); ?>">Back to KSD</a>
    </div>
    <?php endif; ?>
</div>
