(function($){
        

        // next step click from sif pop
        $( "#esig-fluentform-create" ).click(function() {
          
 
                   var form_id= $('select[name="esig_ff_form_id"]').val();
                 
                   $("#esig-fluentform-form-first-step").hide();
                   
                   // jquery ajax to get form field . 
                   jQuery.post(esigAjax.ajaxurl,{ action:"esig_fluent_form_fields",form_id:form_id},function( data ){ 
                       
				      $("#esig-ff-field-option").html(data);
				},"html");
                   
                   $("#esig-ff-second-step").show();                        
  
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



