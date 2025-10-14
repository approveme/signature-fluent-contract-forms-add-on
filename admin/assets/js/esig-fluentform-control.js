(function($){
	
	// Only initialize dialog if element exists
	if ( $( "#esig-fluent-almost-done" ).length ) {
		
		// Initialize the almost done modal dialog
		$( "#esig-fluent-almost-done" ).dialog({
			dialogClass: 'esig-dialog',
			height: 350,
			width: 350,
			modal: true,
			closeOnEscape: true,
			draggable: false,
			resizable: false,
			position: { my: "center", at: "center", of: window },
			// Properly clean up when dialog closes
			close: function(event, ui) {
				// Remove the dialog and overlay completely
				$(this).dialog('destroy');
				// Ensure overlay is removed
				$('.ui-widget-overlay').remove();
			}
		});
		
		// Do later button click - close dialog
		$( "#esig-fluent-setting-later" ).click(function(e) {
			e.preventDefault();
			$( '#esig-fluent-almost-done' ).dialog( "close" );
		});
		
		// Let's go button - close dialog before navigation
		$( "#esig-fluent-lets-go" ).click(function(e) {
			// Close the dialog before navigating
			$( '#esig-fluent-almost-done' ).dialog( "close" );
			// Let the link navigate naturally
		});
	}
		
})(jQuery);






