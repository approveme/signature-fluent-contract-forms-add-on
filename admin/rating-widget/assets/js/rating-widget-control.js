(function($){
        

       
        $( ".esign_ratting_widget_yes_button" ).click(function(e) {
                e.preventDefault();
                 var ratting_url = $("#rating-url").val();
                
                 
              
 
                 $(".esign_ratting_widget_info").html("Thats's awesome! Could you please do me Big Favor and give it a 5-star rating on Wordpress to help us spready the word and boost our motivation");     
                 $(".esign_ratting_widget_yes").removeClass("col-sm-1");
                 $(".esign_ratting_widget_yes").addClass("col-sm-2");
                 $(".esign_ratting_widget_yes").html("<a onclick='hide_permanent()' href='"+ratting_url+"' class='button-primary'>OK, you deserve it!</a>"); 
                 $(".esign_ratting_widget_no").removeClass("col-sm-4");
                 $(".esign_ratting_widget_no").addClass("col-sm-3");
                 $(".esign_ratting_widget_no").attr("id","rating_widget_hide");
                 $(".esign_ratting_widget_no").html("<a onclick='hide_permanent()' href='#'>Not Thanks</a>"); 
     
     
               
     
     
                });



        
        
    
        $( ".esign_ratting_widget_no_button" ).click(function(e) {
                e.preventDefault();

                var pluginName = $("#plugin-name").val();
 
                 $(".esign_ratting_widget_info").html("We're sorry to hear you aren't enjoying our WP E-Signature and "+pluginName+" Form integration. We would love a change to improve. Could you take a minute and let us know what we can do better?");     
                 $(".esign_ratting_widget_yes").removeClass("col-sm-1");
                 $(".esign_ratting_widget_yes").addClass("col-sm-2");
                 $(".esign_ratting_widget_yes").html('<input type="submit" id="esig-action-ratting-widget" class="button action" onclick="giveFeedback()" value="Give Feedback">'); 
                 $(".esign_ratting_widget_no").removeClass("col-sm-4");
                 $(".esign_ratting_widget_no").addClass("col-sm-3");
                 $(".esign_ratting_widget_no").attr("id","rating_widget_hide");                                 
                 $(".esign_ratting_widget_no").html("<a onclick='hide_permanent()' href='#'>Not Thanks</a>"); 

        }); 


        
        
        
        
	
})(jQuery);


    

function giveFeedback() {
        var pluginName = document.getElementById('plugin-name').value;
        hide_permanent();

        var feedback = document.getElementById('feedback-url').value;
        window.location.replace(feedback);
} 

function hide_permanent() {


        var pluginName = document.getElementById('plugin-name').value;
        esigRemoteRequest("esig_ratting_widget_remove", "POST", function(pluginName){

                var esignRatting = document.getElementById('esign-ratting');
                esignRatting.parentNode.removeChild(esignRatting);

        });
} 
