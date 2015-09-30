(function($) { 
    var cc = "";     
    var _replyToAll = function(){    
        //Reply to all
        $("#ksd-cc-field").parent().on('click','span.ksd-button',function(){
                var ccArr       = cc.split(',');
                var ccLen       = ccArr.length;
                var ksdCcData   = "";
                for( i=0; i < ccLen; i++ ){
                    if( $('#ksd-cc-field').val().toLowerCase().indexOf( ccArr[i].toLowerCase() ) < 0 ){
                        ksdCcData = ksdCcData + "," + ccArr[i];
                    }
                }                
                if( $('#ksd-cc-field').val().length  < 1 ){
                    ksdCcData = ksdCcData.replace(/^,/,''); //trim leading commas
                }
                $('#ksd-cc-field').val( $('#ksd-cc-field').val() + ksdCcData ) ;
 
        });
    };
    
    tinymce.PluginManager.add('KSDCC', function( editor, url ) {       
        var lblCc = lblReplyToAll = "";
        if( 'undefined' !==  typeof ksd_admin ) { 
            lblCc           = ksd_admin.ksd_labels.lbl_CC;
            lblReplyToAll   = ksd_admin.ksd_labels.lbl_populate_cc;
        }else{
            lblCc           = ksd_public.ksd_public_labels.lbl_CC;
            lblReplyToAll   = ksd_public.ksd_public_labels.lbl_populate_cc;
        }
        //Add CC filed
        var editorWrapperId = 'wp-'+editor.id + '-wrap'; 
        var ccField         = '<input class="form-input ksd-cc" id="ksd-cc-field" type="text"  placeholder="'+ lblCc +'" name="ksd_tkt_cc" />';
        var ccReplyToAll    = '<span id="ksd-reply-to-all" class="button ksd-button">'+lblReplyToAll+'</span>';
        var html            = '<div id="ksd-cc-div" class="hidden">'+ccField+'</div>' ;

        $( "#" + editorWrapperId ).parent().prepend( html );
        $('#ksd-cc-field').tooltip();
        
        editor.addButton( 'ksd_cc_button', {
            text: lblCc,
            icon: false,
            onclick: function(){
                if( $('#ksd-ticket-replies').length || $('#ksd-ticket-message').length ){ //Only show replytoall on reply page 
                    cc = $('.ksd-ticket-cc span.ksd-cc-emails').text();                    
                    if( $('.ksd-reply-cc:first').length ){
                        cc += $('.ksd-reply-cc:first span.ksd-cc-emails').text();
                    }
                    if( cc.length && ! $('#ksd-reply-to-all').length ){//Don't create multiple reply buttons
                        $('#ksd-cc-field').parent().append(ccReplyToAll);
                    }else{
                        $('#ksd-cc-field').parent().find('span').remove();
                    }
                    _replyToAll();
                } 
                $("#ksd-cc-div").toggle();
            }
        });
    });	

})(jQuery);