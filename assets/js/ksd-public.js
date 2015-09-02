jQuery( document ).ready(function() { 
   /**Toggle display of new ticket form on click of the Support button*/   
   if( jQuery("button#ksd-new-ticket-public").length ){//Check if the button exists.        
        jQuery( "button#ksd-new-ticket-public" ).click(function(e) {//Toggle on button click
            e.preventDefault();            
            jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" );
    });
   };
   //Unhide registration forms added by shortcodes
   if( jQuery("div.ksd-form-short-code").length ){//Check if the button exists.      
       jQuery("div.ksd-form-short-code").removeClass( 'hidden' );//Unhide the form
       jQuery('div.ksd-form-short-code div.ksd-close-form-wrapper').remove();
   }
    /**AJAX: Log new ticket on submission of the new ticket form**/
    logNewTicket    = function(form){
        //First ensure that the Google reCAPTCHA checkbox was checked 
        if ( 'undefined' !== typeof(grecaptcha) ){
            if (!grecaptcha.getResponse()){
                jQuery("span.ksd-g-recaptcha-error").html( ksd_public.ksd_public_labels.msg_grecaptcha_error );
                return;
            } 
        }
        targetFormClass = '.ksd-form-hidden-tab-form';  //The wrapper class for the form being targetted. We use this to
                                                        //make sure that the proceeding actions are on the correct form
        if( jQuery(form).hasClass('ksd-form-short-code-form')){
           targetFormClass = '.ksd-form-short-code-form';
        }
        if( jQuery(form).hasClass('ksd-register-public')){//For registration forms
           targetFormClass = '.ksd-register-public';
        }
        jQuery( targetFormClass+' img.ksd_loading_dialog' ).show();//Show the loading button
        jQuery('form'+targetFormClass+' :submit').hide(); //Hide the submit button
        jQuery.post(    ksd_public.ajax_url, 
                        jQuery(form).serialize(), //The action and nonce are hidden fields in the form
                        function( response ) { 
                            jQuery( targetFormClass+' img.ksd_loading_dialog' ).hide();//Hide the loading button
                            var respObj = {};
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                jQuery ( 'div'+targetFormClass+'-response' ).show().html( ksd_public.ksd_public_labels.msg_error_refresh );
                                jQuery('form'+targetFormClass+' :submit').show(); //Hide the submit button
                                return;
                            }
                            //Show the response received. Check for errors
                            if ( 'undefined' !== typeof(respObj.error) ){
                                jQuery ( 'div'+targetFormClass+'-response' ).show().html(respObj.error.message);
                                jQuery('form'+targetFormClass+' :submit').show(); //Show the submit button
                                return ;
                            }
                            jQuery ( 'div'+targetFormClass+'-response' ).show().html(respObj);
                            if( targetFormClass === '.ksd-register-public' ){//Registration successful. Redirect...
                                window.location.replace( ksd_public.ksd_submit_tickets_url );
                            }
                            //Remove the form
                            jQuery( 'form'+targetFormClass ).remove();
                });
            
        };   
     //Add validation to the front-end support form
    _attachValidateEventToSupportForm   =   function( theForm ){
    jQuery( theForm ).validate({
        submitHandler: function(form) {
            logNewTicket(form);
        }
        });
    };    
    if( jQuery("div.ksd-form-short-code form").length ){//Check if any forms entered using a shortcode exists
        _attachValidateEventToSupportForm("div.ksd-form-short-code form");
    };    
     if( jQuery("div.ksd-form-hidden-tab").length ){//Check if the support tab form is shown
        _attachValidateEventToSupportForm("div.ksd-form-hidden-tab form");
    };   

    
     /**In the front end forms, we use labels in the input fields to
        indicate what info each input requires. On click though, these labels
        need to disappear so the user can type. This function handles the toggling
        of the label's value from a phrase to empty and back as the user focuses/stops focussing 
        on an input **/
      _togglePublicFormFieldValues = function(){          
            //Toggles the form label's value
            function toggleFieldLabelText ( event ){
                     if(jQuery(this).val() === event.data.oldValue){
                        jQuery(this).val(event.data.newValue);                              
                    }                    
            };
            //The fields and their respective default label text
            var newFormFields = {//@TODO Message is deleted if you hover and hover away
                "ksd_cust_fullname" :   ksd_public.ksd_public_labels.lbl_name,               
                "ksd_tkt_subject"   :   ksd_public.ksd_public_labels.lbl_subject,
                "ksd_cust_email"    :   ksd_public.ksd_public_labels.lbl_email,
                "ksd_cust_firstname":   ksd_public.ksd_public_labels.lbl_first_name,            
                "ksd_cust_lastname" :   ksd_public.ksd_public_labels.lbl_last_name,           
                "ksd_cust_username" :   ksd_public.ksd_public_labels.lbl_username            
            };
            //Attach events to the fields 
            jQuery.each( newFormFields, function( fieldName, formValue ) {
                jQuery( 'input[name='+fieldName+']' ).on('focus',{
                                                    oldValue: formValue,
                                                    newValue: "",
                                                    fieldName: fieldName
                                                 }, toggleFieldLabelText);
                jQuery( 'input[name='+fieldName+']' ).on('blur',{
                                                    oldValue: "",
                                                    newValue: formValue,
                                                    fieldName: fieldName
                                               }, toggleFieldLabelText);
            });
            //Handle the textarea too
            jQuery( "textarea[name=ksd_tkt_message]" ).on('focus', function() {
                jQuery( this ).val('');
            });
        }
    _togglePublicFormFieldValues();
     
    //Close the support tab if the close button is clicked
    jQuery ( '.ksd-new-ticket-form-wrap img.ksd_close_button,.ksd-register-form-wrap img.ksd_close_button' ).click(function(){
         jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" );
    });
    //Toggle 'Show password'
    jQuery('li.ksd-show-password input[name=ksd_cust_show_password]').click( function(){
        if ( jQuery(this).is(':checked')) {
            jQuery( 'input[name=ksd_cust_password]' ).attr( 'type', 'text' );
        }
        else{
            jQuery( 'input[name=ksd_cust_password]' ).attr( 'type', 'password' );
        }
    });
    
    //In single ticket view, send a ticket reply
    if( jQuery( '#wp-ksd-public-new-reply-wrap' ).length ){
        jQuery( '#ksd-public-reply-submit' ).click(function(){           
            jQuery('.ksd-public-spinner').addClass('is-active').removeClass('hidden');
            tinyMCE.triggerSave(); //Required for the tinyMCE.activeEditor.getContent() below to work
            jQuery.post(    
                    ksd_public.ajax_url,
                    {   action: 'ksd_reply_ticket',
                        ksd_new_reply_nonce: jQuery('input[name=ksd_new_reply_nonce]').val(), 
                        ksd_ticket_reply: tinyMCE.activeEditor.getContent(),
                        ksd_reply_title: jQuery('h1.entry-title').text(),
                        tkt_id: jQuery('ul#ksd-ticket-replies').attr("class").replace("ticket-","")
                    },
                    function ( response ) {
                        var respObj = {};
                        //To catch cases when the ajax response is not json
                        try {
                            jQuery('.ksd-public-spinner').removeClass('is-active').addClass('hidden');
                            //to reduce cost of recalling parse
                            respObj = JSON.parse(response);
                        } catch (err) {
                            jQuery( '#ksd-public-reply-error' ).removeClass('hidden').html( ksd_public.ksd_public_labels.msg_error_refresh ); 
                            return;
                        }

                        //Check for error in request.
                        if ('undefined' !== typeof (respObj.error)) {
                           jQuery( '#ksd-public-reply-error' ).removeClass('hidden').html( respObj.error.message );
                            return;
                        }
       
                        var d = new Date();
                        replyData = "<li class='ticket-reply'>";
                        replyData += "<span class='reply_author'>"+respObj.post_author+"</span>";
                        replyData += '<span class="reply_date">' + d.toLocaleString() + '</span>';
                        replyData += "<div class='reply_message'>";

                        jQuery( '#ksd-public-reply-success' ).removeClass('hidden').html( ksd_public.ksd_public_labels.msg_reply_sent ).delay(3000).fadeOut();
                        replyData += tinyMCE.activeEditor.getContent();//Get the content                                 
                        tinyMCE.activeEditor.setContent(''); //Clear the reply field

                        replyData += "</div>";
                        replyData += "</li>";
                        jQuery("ul#ksd-ticket-replies").append( replyData );
                    });            
        });
    }
  });