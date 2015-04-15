<?php global $tab_id; ?>
<div class="ksd-ticket-extras">
        <div class="ksd-ticket-refresh">
            <button class="button" title="<?php _e( 'Refresh', 'kanzu-support-desk');?>"></button>
        </div>
        <div class="ksd-pagination-field">
            <?php _e( 'Show:', 'kanzu-support-desk');?> <input type="number" value="5" maxlength="3"  class="ksd-pagination-limit" id="ksd_pagination_limit_<?php echo $tab_id; ?>" max="999" min="1" step="1"/>
        </div>
        <div class="ksd-ticket-search">
            <input type="type" size="18" name="ksd_tkt_search_input_<?php echo $tab_id;?>" class="ksd_tkt_search_input" /> 
            <button class="ksd-tkt-search-btn button" id="ksd_tkt_search_btn_<?php echo $tab_id;?>"><?php _e( 'Search Tickets', 'kanzu-support-desk'); ?></button>
        </div>
</div>

<div class="ksd-grid-container">
    <div class="ticket-list">
    </div>
</div>    
  


<div class="ksd-grid-container">
    <div class="ksd-row">
        <div class="ksd-col-6 ksd-ticket-nav">
            <nav id="ksd_pagination_<?php echo $tab_id;?>">  
                <ul>  
                    <!-- Pagination -->
                </ul>  
            </nav>  
        </div>
    </div>
</div>
 

