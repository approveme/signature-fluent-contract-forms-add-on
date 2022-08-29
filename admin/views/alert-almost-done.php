<div id="esig-fluent-almost-done" style="display: none;"> 

        	<div class="esig-dialog-header">
        	<div class="esig-alert">
            	<span class="esig-icon-esig-alert"></span>
            </div>
		   <h3><?php _e('Almost there... you\'re 50% complete','esig-nf'); ?></h3>
		   
		   <p class="esig-updater-text"><?php 
		   
		    $esig_user= new WP_E_User();
		    
		    $wpid = get_current_user_id();
		    
		    $users = $esig_user->getUserByWPID($wpid); 
		    echo esc_attr($users->first_name) . ","; 
		   
		   ?>
		   
		   
		  <?php _e('Congrats on setting up your document! You\'ve got part 1 of 2 complete! Now you need to
          head over to the "Form Settings" tab for the Fluent Form you are trying to connect it to.' ,'esig-nf'); ?> </p>
		</div>
        

         <div > <img src="<?php echo esc_url(plugins_url("fluent-forms-screenshot.png",__FILE__)); ?>" style="border: 1px solid #efefef; width: 550px; height:148px" /> </div>

        
        <div class="esig-updater-button">

		  <span> <a href="#" class="button esig-secondary-btn"  id="esig-fluent-setting-later"> <?php _e('I\'LL DO THIS LATER','esig-nf');?> </a></span>
                  <span> <a href="admin.php?page=fluent_forms&form_id=<?php echo esc_attr($data['form_id']); ?>&route=settings&sub_route=form_settings#/all-integrations" class="button esig-dgr-btn" id="esig-fluent-lets-go"> <?php _e('LET\'S GO NOW!','esig-nf');?> </a></span>

		</div>

 </div>
