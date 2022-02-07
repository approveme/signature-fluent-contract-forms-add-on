<?php
/**
 * @package   	      Fluent Forms Signature Contract Add-on
 * @contributors      Kevin Michael Gray (Approve Me), Abu Sohib (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       Fluent Forms Signature Contract Add-on by ApproveMe.com
 * Plugin URI:        http://aprv.me/2lfrDYG
 * Description:       This add-on makes it possible to automatically email a WP E-Signature document (or redirect a user to a document) after the user has succesfully submitted a Fluent Forms. You can also insert data from the submitted Fluent Form into the WP E-Signature document.
 * Version:           1.0.0
 * Author:            ApproveMe.com
 * Author URI:        https://www.approveme.com/
 * Text Domain:       esig-ffds
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

require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-ffds.php' );
register_activation_hook( __FILE__, array( 'ESIG_FFDS', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ESIG_FFDS', 'deactivate' ) );

require_once( plugin_dir_path( __FILE__ ) . 'admin/about/autoload.php' );

require_once(plugin_dir_path(__FILE__) . 'includes/fluentEsigSettings.php');
require_once(plugin_dir_path(__FILE__) . 'includes/fluentIntegration.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/esig-fluentform-document-view.php' );


add_action("init","loadEsigFluentIntegration",11);

function loadEsigFluentIntegration()
{
    if (function_exists('wpFluentForm')) {
        new esigFluentIntegration\esigFluent(wpFluentForm());
    }
}

require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-ffds-admin.php' );
add_action( 'plugins_loaded', array( 'ESIG_FFDS_Admin', 'get_instance' ) );

require_once( plugin_dir_path( __FILE__ ) . 'admin/rating-widget/esign-rating-widget.php' );
add_action( 'plugins_loaded', array( 'esignRatingWidgetFluentForm', 'get_instance' ) );
