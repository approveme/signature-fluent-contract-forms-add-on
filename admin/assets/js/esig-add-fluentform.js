(function($){
        

        // next step click from sif pop
        $( "#esig-fluentform-create" ).click(function() {
          
                   var form_id= $('select[name="esig_ff_form_id"]').val();
                 
                   $("#esig-fluentform-form-first-step").hide();
                   
                   // Show loading indicator
                   var loadingHtml = '<div id="esig-fluentform-loading" style="text-align: center; padding: 40px 20px;">' +
                       '<div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: esig-spin 1s linear infinite;"></div>' +
                       '<p style="margin-top: 15px; color: #666; font-size: 14px;">Loading form fields...</p>' +
                       '</div>';
                   $("#esig-ff-field-option").html(loadingHtml);
                   $("#esig-ff-second-step").show();
                   
                   // Add CSS animation for spinner if not already added
                   if (!$('#esig-fluentform-spinner-style').length) {
                       $('<style id="esig-fluentform-spinner-style">@keyframes esig-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
                   }
                   
                   // jquery ajax to get form field with nonce for security
                   var ajaxUrl = (typeof esigFluentAjax !== 'undefined') ? esigFluentAjax.ajaxurl : esigAjax.ajaxurl;
                   var nonce = (typeof esigFluentAjax !== 'undefined') ? esigFluentAjax.esig_fluent_nonce : '';
                   
                   jQuery.ajax({
                       url: ajaxUrl,
                       type: 'POST',
                       data: {
                           action: "esig_fluent_form_fields",
                           form_id: form_id,
                           esig_fluent_nonce: nonce
                       },
                       dataType: 'text', // Accept both JSON and HTML
                       success: function(data, textStatus, xhr) {
                           // Check if response is JSON (error) by trying to parse it
                           var isJson = false;
                           var jsonData = null;
                           
                           // Check Content-Type header first
                           var contentType = xhr.getResponseHeader('Content-Type') || '';
                           if (contentType.indexOf('application/json') !== -1) {
                               isJson = true;
                           }
                           
                           // Also check if response looks like JSON (starts with {)
                           if (!isJson && data.trim().charAt(0) === '{') {
                               isJson = true;
                           }
                           
                           // If it's JSON, try to parse it
                           if (isJson) {
                               try {
                                   jsonData = jQuery.parseJSON(data);
                                   if (jsonData && jsonData.success === false) {
                                       // It's an error response - show error message only
                                       var errorMessage = jsonData.data && jsonData.data.message ? jsonData.data.message : 'Error loading form fields. Please try again.';
                                       alert(errorMessage);
                                       $("#esig-fluentform-form-first-step").show();
                                       $("#esig-ff-second-step").hide();
                                       return;
                                   }
                               } catch (e) {
                                   // Failed to parse JSON, treat as HTML
                               }
                           }
                           
                           // Success - treat as HTML and insert into DOM
                           $("#esig-ff-field-option").html(data);
                           $("#esig-ff-second-step").show();
                       },
                       error: function(xhr, status, error) {
                           // Try to parse error response as JSON to get error message
                           var errorMessage = 'Error loading form fields. Please try again.';
                           try {
                               var jsonData = jQuery.parseJSON(xhr.responseText);
                               if (jsonData && jsonData.data && jsonData.data.message) {
                                   errorMessage = jsonData.data.message;
                               }
                           } catch (e) {
                               // Use default error message
                           }
                           alert(errorMessage);
                           $("#esig-fluentform-form-first-step").show();
                           $("#esig-ff-second-step").hide();
                       }
                   });
  
        });
 
        // ninja add to document button clicked 
        $( "#esig-fluentform-insert" ).click(function() {
 
                   var form_id= $('select[name="esig_ff_form_id"]').val();
                   
                   var field_id =$('select[name="esig_ff_field_id"]').val();
                   var label = $('select[name="esig_ff_field_id"]').find(':selected').data('id');
                   var displayType =$('select[name="esig_fluentform_value_display_type"]').val();
                   var field_type = $('select[name="esig_ff_field_id"]').find(':selected').data('type');
                   // 
                  
                  if (field_id == "all") {
                        $('select#esig_ff_field_id').find('option').each(function () {
                               
                                // Add $(this).val() to your list
                                let allField = $(this).val();
                                let allLabel = $(this).data('id'); 
                                let alltype = $(this).data('type');  
                                                                
                                if (allField == "all") return true;                               


                                var return_text = '<p>[esigfluent formid="'+ form_id +'" label="'+ allLabel +'" field_id="'+ allField +'" field_type="'+ alltype +'" display="'+ displayType +'"]</p>';
		                esig_sif_admin_controls.insertContent(return_text);
                        });
                }
                else {
                  var return_text = '[esigfluent formid="'+ form_id +'" label="'+ label +'" field_id="'+ field_id +'" field_type="'+ field_type +'" display="'+ displayType +'" ]';
		  esig_sif_admin_controls.insertContent(return_text);

                }
            
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-fluentform-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });
	
})(jQuery);



