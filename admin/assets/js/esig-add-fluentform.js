(function($){
        

        // next step click from sif pop
        $("#esig-fluentform-create").click(function() {
          
                var form_id = $('select[name="esig_ff_form_id"]').val();
                
                // Hide first step, show second step
                $("#esig-fluentform-form-first-step").hide();
                $("#esig-ff-second-step").show();
                
                // Show loading only on first load
                var isFirstLoad = $("#esig-ff-field-option").is(':empty') || $("#esig-ff-field-option").html().trim() === '';
                
                if (isFirstLoad) {
                        $("#esig-fluentform-loading-container").show();
                }
                
                // AJAX to get form fields
                jQuery.post(esigAjax.ajaxurl, { 
                        action: "esig_fluent_form_fields", 
                        form_id: form_id 
                }, function(data) {
                        
                        // Hide and remove loading message
                        $("#esig-fluentform-loading-container").fadeOut(200, function() {
                                $(this).remove();
                        });
                        
                        // Insert field options
                        $("#esig-ff-field-option").html(data);
                        
                        // Show all elements with proper targeting and spacing
                        setTimeout(function() {
                                // Get DOM elements directly - use step2 button ID!
                                var fieldOption = document.getElementById('esig-ff-field-option');
                                var displayType = document.getElementById('select-fluentform-field-display-type');
                                var buttonWrap = document.getElementById('upload_fluentform_button_step2');
                                
                                // Force inline styles with !important via setAttribute - add proper spacing
                                if (fieldOption) {
                                        fieldOption.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 15px 0 !important;');
                                }
                                if (displayType) {
                                        displayType.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 15px 0 !important;');
                                }
                                if (buttonWrap) {
                                        buttonWrap.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; margin: 20px 0 !important;');
                                        
                                        // Also force the button inside visible
                                        var button = buttonWrap.querySelector('#esig-fluentform-insert');
                                        if (button) {
                                                button.setAttribute('style', 'display: inline-block !important; visibility: visible !important; opacity: 1 !important;');
                                        }
                                }
                                
                        }, 100);
                        
                        // Re-initialize chosen for the dropdowns
                        setTimeout(function() {
                                if (jQuery.fn.chosen) {
                                        try {
                                                $("#esig-ff-field-option .chosen-select").chosen('destroy');
                                                $("#select-fluentform-field-display-type .chosen-select").chosen('destroy');
                                        } catch(e) {}
                                        
                                        $("#esig-ff-field-option .chosen-select").chosen();
                                        $("#select-fluentform-field-display-type .chosen-select").chosen();
                                }
                        }, 150);
                        
                }, "html").fail(function(xhr, status, error) {
                        $("#esig-fluentform-loading-container").html('<span style="color: red;">Error loading fields. Please try again.</span>');
                });
  
        });
 
        // fluent form add to document button clicked 
        $(document).on("click", "#esig-fluentform-insert", function(e) {
                e.preventDefault();
 
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
		                 tinymce.get('document_content').insertContent(return_text);
                        });
                }
                else {
                  var return_text = '[esigfluent formid="'+ form_id +'" label="'+ label +'" field_id="'+ field_id +'" field_type="'+ field_type +'" display="'+ displayType +'" ]';
		   tinymce.get('document_content').insertContent(return_text);

                }
            
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-fluentform-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });


          // display  gravity form option popup
        $("#wpesign__fluentform-sif-popup").on("click", function(e) {

                e.preventDefault();
               
                tb_show( "+ Gravity form option", "#TB_inline?inlineId=esig-fluentform-option", false );
                

        });
        
	
})(jQuery);



