<div id="esig-fluent-almost-done" style="display: none;"> 

        	<div class="esig-dialog-header">
        	 <h3><?php _e('Almost there... you\'re 50% complete','esig'); ?></h3>
		   
		  
		  
		   <h2><?php _e('Lets head over to your form settings to complete setup','esig'); ?></h2>
		</div>
        

         <div > <img src="<?php echo esc_url(plugins_url("fluent-forms-screenshot.png",__FILE__)); ?>" style="border: 1px solid #efefef; width: 550px; height:148px" /> </div>

        
        <div class="esig-updater-button">

		  <span> <a href="#" class="button esig-secondary-btn"  id="esig-fluent-setting-later"> <?php _e('I\'LL DO THIS LATER','esig-nf');?> </a></span>
                  <span> <a href="admin.php?page=fluent_forms&form_id=<?php echo esc_attr($data['form_id']); ?>&route=settings&sub_route=form_settings#/all-integrations" class="form__btn btn btn--secondary btn--fit" id="esig-fluent-lets-go"> <?php _e('LET\'S GO NOW!','esig');?> </a></span>

		</div>

 </div>
