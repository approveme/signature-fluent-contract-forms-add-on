<?php

/**
 *
 * @package ESIG_FFDS_Admin
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */

use FluentForm\App\Helpers\Helper;
use esigFluentIntegration\esigFluentSetting;
use FluentForm\Framework\Helpers\ArrayHelper;

if (!class_exists('ESIG_FFDS_Admin')) :

    class ESIG_FFDS_Admin{

        private static $fluentFormId = null;

        public static function setFluentFormID($ID)
        {
            self::$fluentFormId = $ID;
        }
        public static function getFluentFormID()
        {
            return self::$fluentFormId;
        }

        /**
         * Instance of this class.
         * @since    1.0.1
         * @var      object
         */
        protected static $instance = null;
        public $name;
        private $plugin_slug, $document_view;

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
            $plugin = ESIG_FFDS::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();

            $this->name = __('Esignature', 'esig-FFDS');
            
            $this->document_view = new esig_fluentform_document_view();
            
            add_filter('esig_sif_buttons_filter', array($this, 'add_sif_fluentform_buttons'), 12, 1);
            add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_fluentform_text_menu'), 12, 1);
            add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_shortcode('esigfluent', array($this, 'render_shortcode_esigfluent'));
            add_action('wp_ajax_esig_fluent_form_fields', array($this, 'esig_fluent_form_fields'));
            add_action('fluentform_before_form_actions_processing', array($this, 'fluentform_submission'), 10, 3);

            add_filter('fluentform_submission_confirmation',  array($this, 'fluentform_submission_confirmation'), 10, 3);
            //add_filter('fluentform_form_submission_confirmation',  array($this, 'fluentform_before_confirmation'), 10, 3);
           
            add_action('admin_menu', array($this, 'adminmenu'));
            add_action('admin_init', array($this, 'esig_almost_done_fluentform_settings'));
            add_filter('show_sad_invite_link', array($this, 'show_sad_invite_link'), 10, 3);
       
        
        }

        final function show_sad_invite_link($show, $doc, $page_id)
        {
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigfluent')) {

                $show = false;
                return $show;
            }
            return $show;
        }

        final function esig_almost_done_fluentform_settings() {

            if (!function_exists('WP_E_Sig'))
                return;

            // getting sad document id 
            $sad_document_id = ESIG_GET('doc_preview_id');


            if (!$sad_document_id) {
                return;
            }
            // creating esignature api here 
            $api = new WP_E_Api();

            $documents = $api->document->getDocument($sad_document_id);


            $document_content = $documents->document_content;

            $document_raw = $api->signature->decrypt(ENCRYPTION_KEY, $document_content);


            if (has_shortcode($document_raw, 'esigfluent')) {

                preg_match_all('/' . get_shortcode_regex() . '/s', $document_raw, $matches, PREG_SET_ORDER);

                //$ninja_shortcode = $matches[0][0];

                $fluent_shortcode = '';
                $fluentFormid = '';
                foreach ($matches as $match) {
                    if (in_array('esigfluent', $match)) {
                        
                        $atts = shortcode_parse_atts($match[0]);
                        extract(shortcode_atts(array(
                    'formid' => '',
                    'field_id' => '', //foo is a default value
                                ), $atts, 'esigfluent'));
                        if(is_numeric($formid)){
                            $fluentFormid = $formid ; 
                            break;
                        }
                         //$ninja_shortcode = $match[0];
                       
                    }
                }
               
                WP_E_Sig()->document->saveFormIntegration($sad_document_id, 'ninja');

                $getModules = get_option('fluentform_global_modules_status');

                if($getModules['wpesignature'] == 'no'){
                    $getModules['wpesignature'] = 'yes';
                    update_option('fluentform_global_modules_status',$getModules);
                }
            
                $data = array("form_id" => $fluentFormid);
                $display_notice = dirname(__FILE__) . '/views/alert-almost-done.php';
                $api->view->renderPartial('', $data, true, '', $display_notice);
            }
        }

        public function adminmenu() {
            $esigAbout = new esig_esff_Addon_About("Fluentform");
            add_submenu_page('fluent_forms', __('E-Signature', 'esig'), __('E-Signature', 'esig'), 'read', 'esign-fluentform-about', array($esigAbout, 'about_page'));
        }
        
        public function render_shortcode_esigfluent($atts) {
            
            extract(shortcode_atts(array(
                'formid' => '',
                'label' => '', 
                'field_id' => '', 
                'field_type' => '', 
                'display' => '',
                'option' => 'default'
                            ), $atts, 'esigfluent'));

            global $esigFluentInsertId,$esigFluentFormdata;
          
            if (function_exists('wpFluentForm')) {
                $esigFeed = esigFluentSetting::getEsigFeedSettings($formid);           
                $submit_type = esig_esff_get('underline_data',$esigFeed);
            }

            $newFormId = self::getFluentFormID();       
            $ff_value = esigFluentSetting::get_value($esigFluentFormdata,$label,$formid,$field_id, $display, $option,$submit_type,$field_type);
            
            if (!$ff_value) return false;
            $allowOtherFormData = apply_filters("esig_fluent_allow_otherform_data",false);
            if(!wp_validate_boolean($allowOtherFormData))
            {
            if ($newFormId != $formid) return false;  
            }

            return esigFluentSetting::display_value($ff_value, $submit_type);

        }



        public function esig_fluent_form_fields() {


            if (!function_exists('WP_E_Sig')) return false;
    
            $html = '';
    
            $html .= '<select id="esig_ff_field_id" name="esig_ff_field_id" class="chosen-select" style="width:250px;">';
            $form_id = esigpost('form_id');            
    
            $formFields = esigFluentSetting::getAllFluentFormFields($form_id);
    
            $html .= '<option value="all">Insert all fields</option>';
            

            

            foreach ($formFields as $fields) {
                $html .= '<option data-type='. esc_attr($fields['type']) .' data-id="'. esc_attr($fields['label']) .'" value=' . esc_attr($fields['name']) . '>' . esc_attr($fields['label']) . '</option>';
            }
            echo $html;
    
            die();
        }
    
        public function document_add_data($more_contents) {
    
    
            $document_view = new esig_fluentform_document_view();
            $more_contents .= $document_view->add_document_view();
    
    
            return $more_contents;
        }
    
        public function add_sif_fluentform_buttons($sif_menu) {
    
            $esig_type = esig_esff_get('esig_type');
            $document_id = esff_sanitize_init(esig_esff_get('document_id'));
    
            if (empty($esig_type) && !empty($document_id)) {
    
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }
    
            if ($esig_type != 'sad') return $sif_menu;
    
            $sif_menu .= ' {text: "Fluent Form Data",value: "fluentform", onclick: function () { tb_show( "+ Fluent form option", "#TB_inline?width=450&height=300&inlineId=esig-fluentform-option");esign.tbSize(450);}},';
    
            return $sif_menu;
        }
    
        public function add_sif_fluentform_text_menu($sif_menu) {
    
            $esig_type = esig_esff_get('esig_type');
            $document_id = esff_sanitize_init(esig_esff_get('document_id'));
    
            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }
    
            if ($esig_type != 'sad') return $sif_menu;
            $sif_menu['Fluentform'] = array('label' => "Fluent Form Data");
            return $sif_menu;
        }

    
        public function enqueue_admin_scripts() {
    
            
            $screen = get_current_screen();
           
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document',
                'toplevel_page_fluent_forms',
            );

            $fluent_setting_screens = array(               
                'toplevel_page_fluent_forms',
            );

            if (in_array(esig_esff_get("id",$screen), $fluent_setting_screens)) {              

                wp_enqueue_script('jquery');
                wp_enqueue_script('fluentform-setting-script', plugins_url('assets/js/esig-fluentform-setting-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.0.1', true);

            }
    
            if (in_array(esig_esff_get("id",$screen), $admin_screens)) {
                
                wp_enqueue_script('jquery');
                wp_enqueue_script('fluentform-add-admin-script', plugins_url('assets/js/esig-add-fluentform.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }
            
            if (esig_esff_get("id",$screen) != "plugins") {
                wp_enqueue_script('fluentform-add-admin-script', plugins_url('assets/js/esig-fluentform-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }

            if (str_contains(esig_esff_get("id",$screen), 'esign-fluentform')){
                
                wp_enqueue_script('esign-iframe-script', plugins_url('assets/js/esign-iframe.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.0.1', true);
                
                wp_register_style( 'esig_fluent_enqueue_style', plugins_url('about/assets/css/esig-about.css', __FILE__), false, '1.0.0' );
                wp_enqueue_style( 'esig_fluent_enqueue_style' );
                wp_enqueue_style( 'esig-google-fonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@200;300;400;600;700;900&display=swap', false );
                wp_enqueue_style( 'esig-snip-styles', plugins_url('about/assets/css/snip-styles.css', __FILE__), false, '0.0.1' );
            }

            
    
        }
    
        
    
        public function fluentform_submission($insertId, $formData, $form)
        {
            
            if (!function_exists('WP_E_Sig')) return false;
            
            if(!class_exists('esig_sad_document')) return false;
            $sad = new esig_sad_document();    
    
            $formId = $form->id;  
            self::setFluentFormID($formId);        
            $feedValue = esigFluentSetting::getEsigFeedSettings($formId);
           
            if(!wp_validate_boolean(esig_esff_get("enable_esig",$feedValue))) return false;
          
         //  $ArrayHelper = new ArrayHelper();
         //  $signer_name = $ArrayHelper->get($feedValue, 'signer_name');
            $email_field = esig_esff_get('signer_email',$feedValue);
            $name_field = esig_esff_get('signer_name',$feedValue); 


            $signer_email = sanitize_email(esig_esff_get($email_field,$formData));           
            $signer_name = esig_esff_get($name_field,$formData);   
            $signing_logic = esig_esff_get('signing_logic',$feedValue);
            $sad_page_id = esig_esff_get('select_sad_doc', $feedValue);
           
            if(is_array($signer_name)){
                $signer_name = sanitize_text_field(esigFluentSetting::prepareNames($signer_name));
            }

            $document_id = $sad->get_sad_id($sad_page_id);                    
            $docStatus  = WP_E_Sig()->document->getStatus($document_id);
                
            if($docStatus !="stand_alone") return false;
            
            if (!is_email($signer_email)) return false;
            //sending email invitation / redirecting .
            self::esig_invite_document($document_id, $signer_email, $signer_name, $formId,$insertId, $signing_logic,$formData,$feedValue,$form);
    
        }


        public static function esig_invite_document($old_doc_id, $signer_email, $signer_name, $form_id,$insertId, $signing_logic, $formData,$feedValue,$form) {

            if (!function_exists('WP_E_Sig')) return false;

            /* make it a basic document and then send to sign */
            $old_doc = WP_E_Sig()->document->getDocument($old_doc_id);
    
            global $esigFluentInsertId , $esigFluentFormdata;
            $esigFluentInsertId = $insertId;
            $esigFluentFormdata = $formData;
            // Copy the document
            $doc_id = WP_E_Sig()->document->copy($old_doc_id);
            
            WP_E_Sig()->meta->add($doc_id, 'esig_ff_form_id', $form_id);
            WP_E_Sig()->meta->add($doc_id, 'esig_ff_entry_id', $insertId);
          
            WP_E_Sig()->document->saveFormIntegration($doc_id, 'fluentform');
            
            esigFluentSetting::save_submission_value($doc_id, $form_id,$formData);
           
            $esig_common = new WP_E_Common();
            $esig_common->set_document_timezone($doc_id);
            // Create the user=
            $recipient = array(
                "user_email" => $signer_email,
                "first_name" => $signer_name,
                "document_id" => $doc_id,
                "wp_user_id" => '',
                "user_title" => '',
                "last_name" => ''
            );
    
            $recipient['id'] = WP_E_Sig()->user->insert($recipient);
    
            $doc_title = $old_doc->document_title . ' - ' . $signer_name;
            // Update the doc title
    
    
            
    
            WP_E_Sig()->document->updateTitle($doc_id, $doc_title);
            WP_E_Sig()->document->updateType($doc_id, 'normal');
            WP_E_Sig()->document->updateStatus($doc_id, 'awaiting');
            
            $doc = WP_E_Sig()->document->getDocument($doc_id);
    
            // trigger an action after document save .
            do_action('esig_sad_document_invite_send', array(
                'document' => $doc,
                'old_doc_id' => $old_doc_id,
            ));

            if(isset($feedValue['signing_reminder'])){
               self::enableReminder($feedValue,$doc_id);
            }
           
            // Get Owner
            $owner = WP_E_Sig()->user->getUserByID($doc->user_id);
            // Create the invitation?
            $invitation = array(
                "recipient_id" => $recipient['id'],
                "recipient_email" => $recipient['user_email'],
                "recipient_name" => $recipient['first_name'],
                "document_id" => $doc_id,
                "document_title" => $doc->document_title,
                "sender_name" => $owner->first_name . ' ' . $owner->last_name,
                "sender_email" => $owner->user_email,
                "sender_id" => 'stand alone',
                "document_checksum" => $doc->document_checksum,
                "sad_doc_id" => $old_doc_id,
            );
    
            $invite_controller = new WP_E_invitationsController();

            // save entry  id with fluentform submission meta
            Helper::setSubmissionMeta($insertId, 'esig_document_id', $doc_id);
            global $esigFluentDocumentId;
            $esigFluentDocumentId = $doc_id;
            
            if ($signing_logic == "email") {
    
                if ($invite_controller->saveThenSend($invitation, $doc)) {
                  
                    return true;
                }
       
            } elseif ($signing_logic == "redirect") {
                
                $invitation_id = $invite_controller->save($invitation);
                $invite_hash = WP_E_Sig()->invite->getInviteHash($invitation_id);
               
                $invite_url = WP_E_Invite::get_invite_url($invite_hash, $doc->document_checksum);   
                WP_E_Sig()->meta->add($doc_id, "esig_fluent_forms_invite_url", wp_sanitize_redirect(urldecode( $invite_url)));
            }
        }


        private static function enableReminder($esignConfig,$docId)
        {

            $reminder_set =  esff_sanitize_init(esig_esff_get('signing_reminder',$esignConfig));
            $reminderEmail = esff_sanitize_init(esig_esff_get('reminder_email',$esignConfig));
            $first_reminder_send = esff_sanitize_init(esig_esff_get('first_reminder_send',$esignConfig));
            $expire_reminder = esff_sanitize_init(esig_esff_get('expire_reminder',$esignConfig));

            if($reminderEmail < 1 || $first_reminder_send < 1 || $expire_reminder < 1)
            {
                return false ; 
            }

            if ($reminder_set == '1') {

                $esig_ff_reminders_settings = array(
                    "esig_reminder_for" => absint($reminderEmail),
                    "esig_reminder_repeat" => absint($first_reminder_send),
                    "esig_reminder_expire" => abs($expire_reminder),
                );

                WP_E_Sig()->meta->add($docId, "esig_reminder_settings_", json_encode($esig_ff_reminders_settings));
                WP_E_Sig()->meta->add($docId, "esig_reminder_send_", "1");
            }

        }
        
         public function fluentform_submission_confirmation($returnData, $form, $confirmation)
        {
           $formId = $form->id;          
           $feedValue = esigFluentSetting::getEsigFeedSettings($formId);

           $signing_logic = esig_esff_get('signing_logic',$feedValue);

            global $esigFluentDocumentId;
        
             if(!is_null($esigFluentDocumentId) && $signing_logic == "redirect"){

                
                $url =  WP_E_Sig()->meta->get($esigFluentDocumentId, "esig_fluent_forms_invite_url");           
               
                $returnData = [  
                   'message'     => 'Form Submitted! Now redirecting to WP E-Signature document for signing',
                   'action'      => 'hide_form',
                   'redirectTo'  => 'customUrl',
                   'redirectUrl' => $url,
               ];  
             
            } 
           
            return $returnData;  
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

