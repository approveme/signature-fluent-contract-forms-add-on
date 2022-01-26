<?php
/**
 * @package   	      Fluent Forms Signature Contract Add-on
 * @contributors      Kevin Michael Gray (Approve Me), Abu Sohib (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       Fluent Forms Signature Contract Add-on by ApproveMe
 * Plugin URI:        http://aprv.me/2lfrDYG
 * Description:       This add-on makes it possible to automatically email a WP E-Signature document (or redirect a user to a document) after the user has succesfully submitted a Fluent Forms. You can also insert data from the submitted Fluent Form into the WP E-Signature document.
 * Version:           1.0.0
 * Author:            Approve Me
 * Author URI:        https://www.approveme.com/
 * Text Domain:       esig-ff
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// define constant 
if (!defined("ESIG_FLUENT_ADDON_PATH")) {
    define('ESIG_FLUENT_ADDON_PATH', dirname(__FILE__));
}
if (!defined("ESIG_FLUENT_ADDON_URL")) {
    define('ESIG_FLUENT_ADDON_URL', plugins_url("/", __FILE__));
}
if (!defined("ESIG_FLUENT_ADDON_DIR")) {
    define('ESIG_FLUENT_ADDON_DIR', plugin_dir_path(__FILE__));
}



 function display_requried_notice()
    {
        add_action('admin_notices', function () {           

            $class = 'notice notice-error';            
            $message = 'FluentForm Mautic Add-On Requires Fluent Forms Add On Plugin, <b><a href="google.com">' . $install_url_text . '</a></b>';
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
    }

register_activation_hook( __FILE__, function () {
    $globalModules = get_option('fluentform_global_modules_status');
    if(!$globalModules || !is_array($globalModules)) {
        $globalModules = [];
    }

    $globalModules['esig'] = 'yes';
    update_option('fluentform_global_modules_status', $globalModules);
});

add_action('plugins_loaded', function () {
      if (!defined('FLUENTFORM')) {
            display_requried_notice();
        }

         include_once ESIG_FLUENT_ADDON_DIR.'includes/Esigature.php';

        if (function_exists('wpFluentForm')) {
             new \FluentFormEsigature\Integrations\Esigature(wpFluentForm());
        }
});


