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
                   var displayType =$('select[name="esig_fluentform_value_display_type"]').val();
                   // 
                   var return_text = ' [esigfluentform formid="'+ form_id +'" field_id="'+ field_id +'" display="'+ displayType +'" ] ';
		  esig_sif_admin_controls.insertContent(return_text);
            
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-fluentform-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });
	
})(jQuery);



