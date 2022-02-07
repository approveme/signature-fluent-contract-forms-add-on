<?php

/**
 *
 * @package esignRatingWidget
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */
if (!class_exists('esignRatingWidgetFluentForm')) :

    class esignRatingWidgetFluentForm{

        /**
         * Instance of this class.
         * @since    1.0.1
         * @var      object
         */
        protected static $instance = null;
        public $name;

        /**
         * Slug of the plugin screen.
         * @since    1.0.1
         * @var      string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @since     0.1
         */
        public function __construct() {
            /*
             * Call $plugin_slug from public plugin class.
             */

            $this->feedbackURL = 'https://www.approveme.com/plugin-feedback/';
            
            add_action('esig_admin_notices', array($this, 'esignRatingWidget'));
            add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStyles'));
            add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
            add_action('wp_ajax_esig_ratting_widget_remove', array($this, 'esigRattingWidgetRemove'));
        
        }

        public function esigRattingWidgetRemove() {

            $pluginName = self::getIntegrationPluginName();            
            update_option('remove_rating_widget_'.$pluginName,'Yes');
            die();
        }
        
         public function enqueueAdminStyles() {
            $screen = get_current_screen();
            $current = $screen->id;
            
            if (($current == 'toplevel_page_esign-docs')) {
                wp_enqueue_style('esig-rating-widget-admin-styles', plugins_url('assets/css/esign-rating-widget.css', __FILE__), array(), '0.1.1');
            }
        }


        public function enqueueAdminScripts() {



            $screen = get_current_screen();
            $current = $screen->id;          

            
            if (($current == 'toplevel_page_esign-docs')) {
              
                 wp_enqueue_script('rating-widget-admin-script', plugins_url('assets/js/rating-widget-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.1', true);
            }

        }
        
        
        
        public static function checkSignedDoc($metakey){
            
            $alldocid = WP_E_Sig()->meta->getall_bykey($metakey);
            $arrayValue = json_decode(json_encode($alldocid),true);

            $signature = 0;
            foreach ($arrayValue as $value) {
                

                $getStatus = WP_E_Sig()->document->getStatus($value['document_id']);
                
                if($getStatus == 'signed'){
                    $signature++;
                }
            }

            if($signature >= 5) return true;
            
            return false;
           
           
            
        }

        public static function getIntegrationPluginName(){
            $dir = plugin_dir_path( __FILE__ );
            $pluginName = '';

            if(strpos($dir, 'caldera') !== false){
                $pluginName = 'caldera';
            } else if(strpos($dir, 'ninja') !== false){
                $pluginName = 'ninja';
            } else if(strpos($dir, 'gravity') !== false){
                $pluginName = 'gravity';
            } else if(strpos($dir, 'edd') !== false){
                $pluginName = 'edd';
            } else if(strpos($dir, 'wp-forms') !== false){
                $pluginName = 'wp-forms';
            } else if(strpos($dir, 'formidable') !== false){
                $pluginName = 'formidable';
            } else if(strpos($dir, 'woocommerce') !== false){
                $pluginName = 'woocommerce';
            }else if(strpos($dir, 'fluentforms') !== false){
                $pluginName = 'fluentforms';
            }

            return $pluginName;
        }

        public static function getIntegrationPluginUrl(){

            $formName = self::getIntegrationPluginName();
            $pluginURL = '';
            if($formName == 'caldera'){
                $pluginURL = 'https://wordpress.org/support/plugin/signature-caldera-forms-online-contract-add-on/reviews/#new-post';
            } else if($formName == 'ninja'){
                $pluginURL = 'https://wordpress.org/support/plugin/ninja-signature-contract-forms-add-on/reviews/#new-post';
            } else if($formName == 'gravity'){
                $pluginURL = 'https://wordpress.org/support/plugin/gravity-signature-forms-add-on/reviews/#new-post';
            } else if($formName == 'edd'){
                $pluginURL = 'https://wordpress.org/support/plugin/edd-digital-signature-add-on/reviews/#new-post';
            } else if($formName == 'wp-forms'){
                $pluginURL = 'https://wordpress.org/support/plugin/wp-forms-signature-contract-add-on/reviews/#new-post';
            } else if($formName == 'formidable'){
                $pluginURL = 'https://wordpress.org/support/plugin/forms-signature-formidable-online-contract-automation/reviews/#new-post';
            } else if($formName == 'woocommerce'){
                $pluginURL = 'https://wordpress.org/support/plugin/woocommerce-digital-signature/reviews/#new-post';
            }else if($formName == 'fluentforms'){
                $pluginURL = 'https://wordpress.org/support/plugin/fluentforms-signature-contract-add-on/reviews/#new-post';
            }

            return $pluginURL;
        }


        public static function getIntegrationPluginMetaKey(){
            
            $formName = self::getIntegrationPluginName();
            $metaKey = '';
            if($formName == 'caldera'){
                $metaKey = 'esig_caldera_entry_id';
            } else if($formName == 'ninja'){
                $metaKey = 'esig_ninja_entry_id';
            } else if($formName == 'gravity'){
                $metaKey = 'esig_gravity_entry_id';
            } else if($formName == 'edd'){
                $metaKey = '_esig_edd_meta_product_agreement';
            } else if($formName == 'wp-forms'){
                $metaKey = 'esig_wp_entry_id';
            } else if($formName == 'formidable'){
                $metaKey = 'esig_formidable_entry_id';
            } else if($formName == 'woocommerce'){
                $metaKey = 'https://wordpress.org/support/plugin/woocommerce-digital-signature/reviews/#new-post';
            }else if($formName == 'fluentforms'){
                $metaKey = 'esig_ff_entry_id';
            }

            return $metaKey;
        }
        
        
        
        public function esignRatingWidget(){
            
            
            
             if (!function_exists('WP_E_Sig')) return false;
            
              $screen = get_current_screen();
                           
              if( $screen->id != 'toplevel_page_esign-docs') return false;

              $pluginName = self::getIntegrationPluginName();

              $checkWidget = get_option('remove_rating_widget_'.$pluginName);

             

              if($checkWidget == "Yes") return false;

            
             $metaKey = self::getIntegrationPluginMetaKey();
              
             $checkRequierment = self::checkSignedDoc($metaKey);

             if(!wp_validate_boolean($checkRequierment)) return false;

             $pluginName = self::getIntegrationPluginName(); 

              $api = new WP_E_Api();
              
              $formName = ucfirst($pluginName.' Form'); 

            //  $feedbackUrl = 'https://www.approveme.com/plugin-feedback/';
              $pluginUrl = self::getIntegrationPluginUrl();
              $data = array("form_name" => $formName,"feedback_url"=>$this->feedbackURL,"plugin_url"=>$pluginUrl);
              $displayNotice = dirname(__FILE__) . '/views/esig-ratting-widget-view.php';
              $api->view->renderPartial('', $data, true, '', $displayNotice);
          
        }
        
         /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }
        

        

    }

    

    
endif;

