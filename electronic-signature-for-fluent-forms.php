<?php
/**
 * @package   	      Electronic Signature Add-on for Fluent Forms
 * @contributors      Kevin Michael Gray (Approve Me), Abu Sohib (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       Electronic Signature Add-on for Fluent Forms by ApproveMe.com
 * Plugin URI:        https://aprv.me/fluentforms
 * Description:       This add-on makes it possible to automatically email a WP E-Signature document (or redirect a user to a document) after the user has succesfully submitted a Fluent Forms. You can also insert data from the submitted Fluent Form into the WP E-Signature document.
 * Version:           1.1.4
 * Author:            ApproveMe.com
 * Author URI:        https://www.approveme.com/
 * Text Domain:       esig-esff
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined("ESIG_ESFF_ADDON_URL")) {
    define('ESIG_ESFF_ADDON_URL', plugins_url("/", __FILE__));
}

require_once( plugin_dir_path( __FILE__ ) . 'includes/esff-function.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-ffds.php' );
register_activation_hook( __FILE__, array( 'ESIG_FFDS', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ESIG_FFDS', 'deactivate' ) );
require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-fluentform-document-view.php' );

require_once(plugin_dir_path(__FILE__) . 'admin/about/autoload.php'); 

add_action('init',"loadEsigFluentIntegration",11);
require_once(plugin_dir_path(__FILE__) . 'includes/fluentEsigSettings.php');
function loadEsigFluentIntegration()
{
    if (function_exists('wpFluentForm')) {
        require_once(plugin_dir_path(__FILE__) . 'includes/fluentIntegration.php');
        new esigFluentIntegration\esigFluent(wpFluentForm());
    }
}

require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-ffds-admin.php' );
add_action( 'plugins_loaded', array( 'ESIG_FFDS_Admin', 'get_instance' ) );
