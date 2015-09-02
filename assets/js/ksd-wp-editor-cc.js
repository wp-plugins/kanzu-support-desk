(function() { 
if(jQuery("form#edit-ticket").length ){
    tinymce.PluginManager.add('KSDCC', function( editor, url ) {    
        editor.addButton( 'ksd_cc_button', {
            text: ksd_admin.ksd_labels.lbl_CC,
            icon: false,
            onclick: function(){
                if ( jQuery("form#edit-ticket input[name=ksd_tkt_cc]").css("display") == "none" )
                {
                    jQuery("form#edit-ticket input[name=ksd_tkt_cc]").css({"display":"block"});
                }else{
                    jQuery("form#edit-ticket input[name=ksd_tkt_cc]").css({"display":"none"});
                    jQuery("form#edit-ticket input[name=ksd_tkt_cc]").val(ksd_admin.ksd_labels.lbl_CC);
                }
            }
        });
    });	
}

})();