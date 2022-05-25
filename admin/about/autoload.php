<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


 // About page directory path
            $pluginName = "fluentforms" ; 

            if (!defined('ESIGN_FLUENTFORM_ABOUT_PATH'))
                    define('ESIGN_FLUENTFORM_ABOUT_PATH', dirname(__FILE__));
            
            if (!defined('ESIGN_'. strtoupper(preg_replace("/\s+/", "", $pluginName)) . '_ABOUT_URL'))
                    define('ESIGN_'. strtoupper(preg_replace("/\s+/", "", $pluginName)) . '_ABOUT_URL', plugins_url("/", __FILE__));

require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-activations-states.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-about-load.php' );



$esigAbout = new esig_Addon_About($pluginName);
$esigAbout->hooks();

add_action('admin_notices', array($esigAbout, 'requirement'));
add_action('esig_admin_notices', array($esigAbout, 'requirement'));

function fluentforms_message($esigStatus,$pluginName)
        {
            $screen = get_current_screen();
            $screenName = "admin_page_esign-fluentforms-about";
            $asterisk = "" ; 
            if($screen->id == $screenName)
            {
               $asterisk = "*"; 
            }
            
            switch ($esigStatus){

                case 'wpe_inactive':
                  return '<span class="esig-icon-esig-alert"></span><h4> ' . $asterisk . 'WP E-Signature is not activated. Please activate WP E-Signature to finish setting up your integration. <a class="about-button" href="'. esig_plugin_activation_link("e-signature/e-signature.php") .'">Activate WP E-Signature</a></h4>';
                  break;
                case 'wpe_expired':
                  return '<span class="esig-icon-esig-alert"></span><h4>' . $asterisk . 'You willl need to activate your WP E-Signature license to run the Fluent forms Signature add-on.  <a class="about-button" href="admin.php?page=esign-licenses-general">Enter your license here</a> </h4>';
                  break;
                case 'wpe_active_basic':
                  return '<span class="esig-icon-esig-alert"></span><h4>' . $asterisk . 'Your WP E-Signature install is missing the Pro Add-Ons. Advanced functionality will not work without these add-ons installed. <a class="about-button" href="'. admin_url("admin.php?page=esign-addons") .'">Install Pro Add-Ons</a></h4>';
                  break;
                case 'wpe_inactive_pro':
                  return '<span class="esig-icon-esig-alert"></span><h4 style="font-size:0.95em;">' . $asterisk . 'Your WP E-Signature Pro Add-Ons are installed but not enabled.  Advanced functionality will not work without these add-ons enabled. <a class="about-button" href="'. esig_plugin_activation_link("e-signature-business-add-ons/e-signature-business-add-ons.php") .'">Enable Pro Add-Ons</a></h4>';
                  break;
                case 'wpe_active_pro':

                  if (!function_exists('wpFluentForm')) {// Notice about add-on dependent 3rd party plugin if not installed
                   return '<span class="esig-icon-esig-alert"></span><h4>Fluent forms plugin is not installed. Please install Fluent forms version 2.0 or greater - <a href="https://wordpress.org/plugins/fluentform/">Get it here now</a></h4>';
                  }
                  elseif(!class_exists('ESIG_SAD_Admin')){// Notice about stand alone documents if not enabled
                    return '<span class="esig-icon-esig-alert"></span><h4>WP E-Signature <a href="https://www.approveme.com/downloads/stand-alone-documents/?utm_source=wprepo&utm_medium=link&utm_campaign=ninjaforms" target="_blank">"Stand Alone Documents"</a> Add-on is not active. Please enable WP E-Signature Stand Alone Documents  <a class="about-button" href="'. admin_url("admin.php?page=esign-addons&tab=disable&esig_action=enable&plugin_url=esig-stand-alone-docs%2Fesig-sad.php&plugin_name=WP%20E-Signature%20-%20Stand%20Alone%20Documents") .'">Enable it now </a> </h4>';
                  }

                  break;
                case 'no_wpe':
                    return '<span class="esig-icon-esig-alert"></span> <h4>' . $asterisk . 'WP E-Signature is not installed. It is required to run the Fluent forms Signature add-on. &nbsp; <span class="button-container"><a class="about-button" href="https://www.approveme.com/ninja-forms-signature-special/?utm_campaign=wprepo&&utm_campaign=ninjaforms">Get your WP E-Signature license</a></span></h4>';
                    break;
                default:
                  return false;
                  break;
              }
        }

  /**
  *  Remove all admin notices from e-signature pages. 
  */
 add_action('in_admin_header', function () {

        $page  = isset($_GET['page']) ? $_GET['page'] : false ;

      if (empty($page)) 
      {
        return false;
      }

      if(!empty($page)  && !preg_match("/esign-/i",$page))
      {
        return false;
      }
      
      remove_all_actions('admin_notices');
      remove_all_actions('all_admin_notices');

},1000);


