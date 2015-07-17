jQuery( document ).ready(function() { 
   /**Toggle display of new ticket form on click of the Support button*/   
   if( jQuery("button#ksd-new-ticket-frontend").length ){//Check if the button exists.
   jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" ); //Hide it by default
    jQuery( "button#ksd-new-ticket-frontend" ).click(function(e) {//Toggle on button click
        e.preventDefault();
        jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" );
    });
   };
    /**AJAX: Log new ticket on submission of the new ticket form**/
    logNewTicket    = function(form){
        //First ensure that the Google reCAPTCHA checkbox was checked 
        if ( 'undefined' !== typeof(grecaptcha) ){
            if (!grecaptcha.getResponse()){
                jQuery("span.ksd-g-recaptcha-error").html( ksd_frontend.msg_gcaptcha_error );
                return;
            } 
        }
        targetFormClass = '.ksd-form-hidden-tab-form';  //The wrapper class for the form being targetted. We use this to
                                                        //make sure that the proceeding actions are on the correct form
        if( jQuery(form).hasClass('ksd-form-short-code-form')){
           targetFormClass = '.ksd-form-short-code-form';
        }
        jQuery( targetFormClass+' img.ksd_loading_dialog' ).show();//Show the loading button
        jQuery('form'+targetFormClass+' :submit').hide(); //Hide the submit button
        jQuery.post(    ksd_frontend.ajax_url, 
                        jQuery(form).serialize(), //The action and nonce are hidden fields in the form
                        function( response ) { 
                            jQuery( targetFormClass+' img.ksd_loading_dialog' ).hide();//Hide the loading button
                            var respObj = {};
                            try {
                                //to reduce cost of recalling parse
                                respObj = JSON.parse(response);
                            } catch (err) {
                                jQuery ( 'div'+targetFormClass+'-response' ).show().text( ksd_frontend.msg_error_refresh );
                                return;
                            }
                            //Show the response received. Check for errors
                            if ( 'undefined' !== typeof(respObj.error) ){
                                jQuery ( 'div'+targetFormClass+'-response' ).show().text(respObj.error.message);
                                return ;
                            }
                            jQuery ( 'div'+targetFormClass+'-response' ).show().text(respObj);
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
      _toggleFrontEndFormFieldValues = function(){          
            //Toggles the form label's value
            function toggleFieldLabelText ( event ){
                     if(jQuery(this).val() === event.data.oldValue){
                        jQuery(this).val(event.data.newValue);                              
                    }                    
            };
            //The fields and their respective default label text
            var newFormFields = {
                "ksd_cust_fullname" :   "Name",               
                "ksd_tkt_subject" :     "Subject",
                "ksd_cust_email" :      "Email"               
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
    _toggleFrontEndFormFieldValues();
     
    //Close the support tab if the close button is clicked
    jQuery ( '#ksd-new-ticket-frontend-wrap img.ksd_close_button' ).click(function(){
         jQuery( ".ksd-form-hidden-tab" ).toggle( "slide" );
    });
    
  });