(function($){

    /**
     * Insert content into the document editor, handling both Visual and Code/Text modes.
     *
     * When the editor is in Code/Text mode tinymce.get() returns null, which
     * causes a fatal JS error. This helper falls back to direct textarea insertion
     * so shortcodes are always placed at the cursor position regardless of mode.
     *
     * @since  2.0.2
     *
     * @param  {string} content  Shortcode or HTML string to insert.
     * @return {void}
     */
    function esigFluentInsertContent( content ) {
        var editor = ( typeof tinymce !== 'undefined' ) ? tinymce.get( 'document_content' ) : null;

        if ( editor && ! editor.isHidden() ) {
            // Visual (WYSIWYG) mode — use the TinyMCE API.
            editor.insertContent( content );
            return;
        }

        // Code/Text mode — write directly into the visible textarea.
        var textarea = document.getElementById( 'document_content' );
        if ( ! textarea ) {
            return;
        }

        var start = textarea.selectionStart || 0;
        var end   = textarea.selectionEnd   || start;
        var text  = textarea.value          || '';

        textarea.value = text.substring( 0, start ) + content + text.substring( end );
        textarea.selectionStart = textarea.selectionEnd = start + content.length;
        textarea.focus();

        // Notify WordPress auto-save and other listeners that the content changed.
        textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
    }

    /**
     * Display a Fluent Forms field-loading error.
     *
     * @since 2.0.4
     *
     * @param {string} message Error message to display.
     * @return {void}
     */
    function esigFluentShowFieldError( message ) {
        $( '#esig-fluentform-loading-container' )
            .text( message )
            .css( 'color', '#b32d2e' )
            .show();
        $( '#esig-ff-field-option, #select-fluentform-field-display-type, #upload_fluentform_button_step2' ).hide();
    }

    /**
     * Render fields returned by the Fluent Forms AJAX endpoint.
     *
     * @since 2.0.4
     *
     * @param {Array} fields Fluent Forms field definitions.
     * @return {void}
     */
    function esigFluentRenderFields( fields ) {
        var $fieldContainer = $( '#esig-ff-field-option' );
        var $fieldSelect = $( '<select>', {
            id: 'esig_ff_field_id',
            name: 'esig_ff_field_id',
            class: 'chosen-select'
        } ).css( 'width', '250px' );

        $fieldSelect.append( $( '<option>', { value: 'all', text: 'Insert all fields' } ) );

        fields.forEach( function( field ) {
            $fieldSelect.append(
                $( '<option>', {
                    value: field.name,
                    text: field.label
                } )
                    .attr( 'data-id', field.label )
                    .attr( 'data-type', field.type )
            );
        } );

        $fieldContainer.empty().append( $fieldSelect ).show();
        $( '#esig-fluentform-loading-container' ).hide();
        $( '#select-fluentform-field-display-type, #upload_fluentform_button_step2' ).show();

        if ( $.fn.chosen ) {
            $fieldSelect.chosen();

            var $displaySelect = $( '#select-fluentform-field-display-type .chosen-select' );
            if ( ! $displaySelect.data( 'chosen' ) ) {
                $displaySelect.chosen();
            }
        }
    }

    // Load form fields after the modal's first step. Delegation supports the
    // document editor rendering integration controls after this script loads.
    $( document ).on( 'click', '#esig-fluentform-create', function( event ) {
        event.preventDefault();

        var formId = $( 'select[name="esig_ff_form_id"]' ).val();
        var settings = ( typeof esigFluentAjax !== 'undefined' ) ? esigFluentAjax : {};
        var ajaxUrl = settings.ajaxurl || ( typeof ajaxurl !== 'undefined' ? ajaxurl : '' );

        if ( ! formId || ! /^\d+$/.test( formId ) ) {
            esigFluentShowFieldError( settings.selectForm || 'Select a Fluent Form first.' );
            return;
        }

        $( '#esig-fluentform-form-first-step' ).hide();
        $( '#esig-ff-second-step' ).show();
        $( '#esig-fluentform-loading-container' ).text( 'Loading form fields...' ).css( 'color', '#555' ).show();

        $.post( ajaxUrl, {
            action: 'esig_fluent_form_fields',
            form_id: formId,
            nonce: settings.nonce || ''
        } ).done( function( response ) {
            var fields = response && response.success && response.data ? response.data.fields : [];

            if ( ! Array.isArray( fields ) || ! fields.length ) {
                var message = response && response.data && response.data.message
                    ? response.data.message
                    : ( settings.noFields || 'No fields were found for this Fluent Form.' );
                esigFluentShowFieldError( message );
                return;
            }

            esigFluentRenderFields( fields );
        } ).fail( function( xhr ) {
            var response = xhr.responseJSON;
            var message = response && response.data && response.data.message
                ? response.data.message
                : ( settings.error || 'Unable to load Fluent Form fields. Please try again.' );
            esigFluentShowFieldError( message );
        } );
    } );
 
        // fluent form add to document button clicked 
        $(document).on("click", "#esig-fluentform-insert", function(e) {
                e.preventDefault();
 
                   var form_id= $('select[name="esig_ff_form_id"]').val();
                   
                   var field_id =$('select[name="esig_ff_field_id"]').val();
                   // Use attr() instead of data() to get the raw label value (preserves spaces)
                   var label = $('select[name="esig_ff_field_id"]').find(':selected').attr('data-id');
                   var displayType =$('select[name="esig_fluentform_value_display_type"]').val();
                   var field_type = $('select[name="esig_ff_field_id"]').find(':selected').attr('data-type');
                   // 
                  
                  if (field_id == "all") {
                        $('select#esig_ff_field_id').find('option').each(function () {
                               
                                // Add $(this).val() to your list
                                let allField = $(this).val();
                                // Use attr() instead of data() to get the raw label value (preserves spaces)
                                let allLabel = $(this).attr('data-id'); 
                                let alltype = $(this).attr('data-type');  
                                                                
                                if (allField == "all") return true;                               


                                // Ensure label is properly encoded to preserve spaces and special characters
                                if (!allLabel) allLabel = '';
                                // HTML encode the label to preserve spaces and special characters in shortcode
                                var escapedLabel = $('<div>').text(allLabel).html().replace(/"/g, '&quot;');
                                var return_text = '<p>[esigfluent formid="'+ form_id +'" label="'+ escapedLabel +'" field_id="'+ allField +'" field_type="'+ alltype +'" display="'+ displayType +'"]</p>';
		                 esigFluentInsertContent( return_text );
                        });
                }
                else {
                  // Ensure label is properly encoded to preserve spaces and special characters
                  if (!label) label = '';
                  // HTML encode the label to preserve spaces and special characters in shortcode
                  var escapedLabel = $('<div>').text(label).html().replace(/"/g, '&quot;');
                  var return_text = '[esigfluent formid="'+ form_id +'" label="'+ escapedLabel +'" field_id="'+ field_id +'" field_type="'+ field_type +'" display="'+ displayType +'" ]';
		   esigFluentInsertContent( return_text );

                }
            
             tb_remove();
                     
                   
        });
        
        
        //if overflow
        $( document ).on( 'click', '#select-fluentform-form-list', function() {
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });


          // display  gravity form option popup
        $( document ).on( 'click', '#wpesign__fluentform-sif-popup', function(e) {

                e.preventDefault();

                $( '#esig-fluentform-form-first-step' ).show();
                $( '#esig-ff-second-step' ).hide();
                $( '#esig-ff-field-option' ).empty().hide();
                $( '#esig-fluentform-loading-container' ).hide();
                $( '#select-fluentform-field-display-type, #upload_fluentform_button_step2' ).hide();
               
                tb_show( "+ Fluent Form Data", "#TB_inline?inlineId=esig-fluentform-option", false );
                

        });
        
	
})(jQuery);


