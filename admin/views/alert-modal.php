<?php

/**
 * ESIG WP ADMIN  ALERTS
 * @package   WP E-Signature - Gravity Form
 */

require_once( dirname(__DIR__,2) . '/admin/about/includes/esig-activations-states.php' );
$esigStatus = esig_get_activation_state();


switch ($esigStatus){
  
  case 'wpe_inactive':
  case 'wpe_expired':
    echo '<div class="bangBar error"> <h4>*You willl need to activate your WP E-Signature license to run the Fluent Forms Signature add-on. <a href="admin.php?page=esign-licenses-general">Enter your license here</a></h4></div>';
    break;
  case 'wpe_active_basic':
    echo '<div class="bangBar error"> <h4>*Your WP E-Signature install is missing the Pro Add-Ons. Advanced functionality will not work without these add-ons installed. <a href="https://www.approveme.com/profile/">Install Pro Add-Ons</a></h4></div>';
  case 'wpe_active_pro':
    
    if(!class_exists('wpFluentForm')) {// Notice about add-on dependent 3rd party plugin if not installed
     echo '<div class="error ' . esc_attr($esigStatus) . '"><span class="esig-icon-esig-alert"></span><h4>Fluent Forms plugin is not installed. Please install Fluent Forms version 2.0 or greater - <a href="https://wordpress.org/plugins/fluentform/">Get it here now</a></h4></div>';
    }elseif(!class_exists('ESIG_SAD_Admin')){// Notice about stand alone documents if not enabled
      echo '<div class="error ' . esc_attr($esigStatus) . '"><span class="esig-icon-esig-alert"></span><h4>WP E-Signature <a href="https://www.approveme.com/downloads/stand-alone-documents/?utm_source=wprepo&utm_medium=link&utm_campaign=fluent-forms" target="_blank">"Stand Alone Documents"</a> Add-on is not installed. Please install WP E-Signature Stand Alone Documents - version 1.2.5 or greater.  </h4></div>';
    }
    break;
  case 'no_wpe':
  default:
    echo '<div class="bangBar error"> <h4>*WP E-Signature is not active. &nbsp; It is required to run the Fluent Forms Signature add-on. &nbsp;<a href="https://aprv.me/fluentforms">Get your business license now</a></h4></div>';
    break;
}

