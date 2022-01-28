(function($){
    

	  // almost done modal dialog here 
       $( "#esig-fluent-almost-done" ).dialog({
			  dialogClass: 'esig-dialog',
			  height:350,
			  width:350,
			  modal: true,
			});
            
      // do later button click 
       $( "#esig-fluent-setting-later" ).click(function() {
          $( '#esig-fluent-almost-done' ).dialog( "close" );
        });
      
     
		
})(jQuery);






