<?php
/**
 *
 * @package ESIG_FLUENTYFORM_DOCUMENT_VIEW
 * @author  Abu Shoaib <abushoaib73@gmail.com>
 */

use esigFluentIntegration\esigFluentSetting;
use FluentForm\App\Api\FormProperties;
use FluentForm\App\Modules\Form\FormFieldsParser;

if (! class_exists('esig-fluentform-document-view')):
class esig_fluentform_document_view {
    
    
            /**
        	 * Initialize the plugin by loading admin scripts & styles and adding a
        	 * settings page and menu.
        	 * @since     0.1
        	 */
        	final function __construct() {
                        
        	}
        	
        	/**
        	 *  This is add document view which is used to load content in 
        	 *  esig view document page
        	 *  @since 1.1.0
        	 */
        	
        	final function add_document_view()
        	{
        	    
        	    if(!function_exists('WP_E_Sig'))
                                return ;
                    
                    
				if(function_exists('wpFluentForm'))
				{								 
					$forms = esigFluentSetting::getAllFluentForm();			
				}				
                    
        	    
        	    //$api = WP_E_Sig();
        	    $assets_dir = ESIGN_ASSETS_DIR_URI;
        	    
                    
        	   $more_option_page = ''; 
        	   
        	    
        	    $more_option_page .= '<div id="esig-fluentform-option" class="esign-form-panel" style="display:none;">
        	        
        	        
                	               <div align="center"><img src="' . esc_url($assets_dir) .'/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
                    			
                                    
                    				<div id="esig-fluentform-form-first-step">
                        				
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What Are You Trying To Do?', 'esig').'</div>
                                            	
                        				<p id="create_fluentform" align="center">';
                                	    
                                	    $more_option_page .=	'
                        			
                        				<p id="select-fluentform-form-list" align="center">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-fluentform-form-id" name="esig_ff_form_id">
                        			     <option value="sddelect">'.__('Select a Fluent Form', 'esig').'</option>';
                                	    
										

                                        
                                           
                                           
                                            if(!empty($forms)){
                                            
												foreach($forms as $form_id=>$form)
												{
												
												
													$more_option_page .=	'<option value="'. $form['id'] . '">'. $form['title'] .'</option>';
												}
                                            }
                                            
                            	         
                                                        
                                            
                                	    $more_option_page .='</select>
                                	    
                        				</p>
                         	  
                                	    </p>
                                	    
                                        <p id="upload_fluentform_button" align="center">
                                           <a href="#" id="esig-fluentform-create" class="button-primary esig-button-large">'.__('Next Step', 'esig').'</a>
                                         </p>
                                     
                                    </div>  <!-- Frist step end here  --> ';
                            
                                    
                 $more_option_page .='<!-- Caldera form second step start here -->
                                            <div id="esig-ff-second-step" style="display:none;">
                                            
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What fluent form field data would you like to insert?', 'esig').'</div>
                                            
                                            <p id="esig-ff-field-option" align="center">
                               



                                             </p>
                                            
                                             <p id="select-fluentform-field-display-type" align="center">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-fluentform-form-id" name="esig_fluentform_value_display_type">
                        			     <option value="value">'.__('Select a display type', 'esig').'</option>
                                          
                                         
                                           <option value="value">Display value</option>
										   <option value="label">Display label</option>
                                           <option value="label_value">Display label + value</option>';
                                	   
                                           
                                	    $more_option_page .='</select>
                                	    
                        				</p>
                                             <p id="upload_fluentform_button" align="center">
                                           <a href="#" id="esig-fluentform-insert" class="button-primary esig-button-large">'.__('Add to Document', 'esig').'</a>
                                         </p>
                                            
                                            </div>
                                    <!-- fluentform form second step end here -->';           
                                    
                                    
        	    
        	    $more_option_page .= '</div><!--- fluentform option end here -->' ;
        	    
        	    
        	    return $more_option_page ; 
        	}
        	
        	
	   
    }
endif ; 

