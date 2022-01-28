<?php

/**
 *
 * @package ESIG_FFDS_Admin
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */

use esigFluentIntegration\esigFluentSetting;

if (!class_exists('ESIG_FFDS_Admin')) :

    class ESIG_FFDS_Admin{

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
            $plugin = ESIG_FFDS::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();

            $this->name = __('Esignature', 'esig-FFDS');
            
            $this->document_view = new esig_fluentform_document_view();
            
            add_filter('esig_sif_buttons_filter', array($this, 'add_sif_fluentform_buttons'), 12, 1);
            add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_fluentform_text_menu'), 12, 1);
            add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wp_ajax_esig_fluent_form_fields', array($this, 'esig_fluent_form_fields'));
            add_action('fluenform_before_submission_confirmation', array($this, 'fluentform_submission'), 10, 3);
             add_filter('fluentform_submission_confirmation',  array($this, 'fluentform_submission_confirmation'), 10, 3);
            add_shortcode('esigfluentform', array($this, 'render_shortcode_esigfluentform'));
            add_action('admin_menu', array($this, 'adminmenu'));
       
        }

        public function adminmenu() {
            $esigAbout = new esig_Addon_About("Fluentform");
            add_submenu_page('fluent_forms', __('E-signature', 'esig'), __('E-signature', 'esig'), 'read', 'esign-fluentform-about', array($esigAbout, 'about_page'));
           
        }
        
        public function render_shortcode_esigfluentform($atts) {


            extract(shortcode_atts(array(
                'formid' => '',
                'field_id' => '', 
                'display' => '',
                'option' => 'default'
                            ), $atts, 'esigfluentform'));

            if (!function_exists('WP_E_Sig'))
                return;


            $csum = isset($_GET['csum']) ? sanitize_text_field($_GET['csum']) : null;

            if (empty($csum)) {
                $document_id = get_option('esig_global_document_id');
            } else {
                $document_id = WP_E_Sig()->document->document_id_by_csum($csum);
            }

            $form_id = WP_E_Sig()->meta->get($document_id, 'esig_ff_form_id');
            $entry_id = WP_E_Sig()->meta->get($document_id, 'esig_ff_entry_id');


            if (empty($entry_id)) {
                return;
            }


            //$forms = Caldera_Forms::get_forms();
            if (function_exists('wpFluentForm')) {
                $esigFeed = esigFluentSetting::getEsigFeedSettings($form_id);                
                $submit_type = $form['underline_data'];
            }



            $ff_value = esigFluentSetting::get_value($document_id, $form_id,$entry_id, $field_id, $display, $option);

            if (!$cf_value) {
                return;
            }


            if (is_array($cf_value)) {

                /*  if (is_array($cf_value)) {
                  $checkboxvalue = $cf_value;
                  } else {
                  $checkboxvalue = json_decode($cf_value, true);
                  } */
                $html = '';

                foreach ($cf_value as $value) {
                    $html .= $value . " ,";
                    /*  if ($submit_type == "underline") {
                      $html .= '<input type="checkbox" disabled readonly value="' . $value . '" checked="checked" ><u>' . $value . '</u>';
                      } else {
                      $html .= '<input type="checkbox" disabled readonly value="' . $value . '" checked="checked" >' . $value;
                      } */
                }
                return substr($html, 0, strlen($html) - 2);
            }

            if (strpos($cf_value, 'click') !== false) {
                $html = '';
                return $html;
            }
            return self::display_value($form, $form_id, $cf_value, $submit_type);
        }



        public function esig_fluent_form_fields() {


            if (!function_exists('WP_E_Sig'))
                return;
    
    
            $html = '';
    
            $html .= '<select name="esig_ff_field_id" class="chosen-select" style="width:250px;">';
            $form_id = $_POST['form_id'];
            
    
            $formFields = esigFluentSetting::getAllFluentFormFields($form_id);
    
        
            foreach ($formFields as $fieldlabel=>$fieldname) {
                
                $html .= '<option value=' . $fieldname . '>' . $fieldlabel . '</option>';
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
    
            $esig_type = isset($_GET['esig_type']) ? $_GET['esig_type'] : null;
            $document_id = isset($_GET['document_id']) ? $_GET['document_id'] : null;
    
            if (empty($esig_type) && !empty($document_id)) {
    
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }
    
            if ($esig_type != 'sad') {
                return $sif_menu;
            }
    
            $sif_menu .= ' {text: "Fluent form Form Data",value: "fluentform", onclick: function () { tb_show( "+ Fluent form option", "#TB_inline?width=450&height=300&inlineId=esig-fluentform-option");esign.tbSize(450);}},';
    
            return $sif_menu;
        }
    
        public function add_sif_fluentform_text_menu($sif_menu) {
    
            $esig_type = esigget('esig_type');
            $document_id = esigget('document_id');
    
            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }
    
            if ($esig_type != 'sad') {
                return $sif_menu;
            }
            $sif_menu['Fluentform'] = array('label' => "Fluent Form Data");
            return $sif_menu;
        }
    
        public function enqueue_admin_scripts() {
    
    
            
            $screen = get_current_screen();
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document',
            );
    
            if (in_array(esigFluentSetting::esigget("id",$screen), $admin_screens)) {
                
                wp_enqueue_script('jquery');
                wp_enqueue_script('fluentform-add-admin-script', plugins_url('assets/js/esig-add-fluentform.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }
            
            if (esigFluentSetting::esigget("id",$screen) != "plugins") {
                wp_enqueue_script('fluentform-add-admin-script', plugins_url('assets/js/esig-fluentform-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }
    
        }
    
        
    
        public function fluentform_submission($insertId, $formData, $form)
        {
            
            
    
            if (!function_exists('WP_E_Sig')) {
                return;
            }
            
            if(!class_exists('esig_sad_document')){
                return false;
            }
    
            $sad = new esig_sad_document();    
    
            
           
           $formId = $form->id;          
           $feedValue = esigFluentSetting::getEsigFeedSettings($formId);
    
           
    
         //  $ArrayHelper = new ArrayHelper();
         //  $signer_name = $ArrayHelper->get($feedValue, 'signer_name');
    
           $sad_page_id = $feedValue['select_sad_doc'];
           $signer_name = $feedValue['signer_name'];
           $signer_email = $feedValue['signer_email'];
           $signing_logic = $feedValue['signing_logic'];
    
           $document_id = $sad->get_sad_id($sad_page_id);
                
           $docStatus  = WP_E_Sig()->document->getStatus($document_id);
                
            if($docStatus !="stand_alone"){
                return false;
            }
    
            if (!is_email($signer_email)) {
                    return;
            }
            
           
    
            //sending email invitation / redirecting .
            self::esig_invite_document($document_id, $signer_email, $signer_name, $formId,$insertId, $signing_logic,$formData,);
    
           
    
          
        }


        public static function esig_invite_document($old_doc_id, $signer_email, $signer_name, $form_id,$insertId, $signing_logic, $formData) {


           
            
            if (!function_exists('WP_E_Sig'))
                return;
    
    
            global $wpdb;
    
            /* make it a basic document and then send to sign */
            $old_doc = WP_E_Sig()->document->getDocument($old_doc_id);
    
            // Copy the document
            $doc_id = WP_E_Sig()->document->copy($old_doc_id);
    
            WP_E_Sig()->meta->add($doc_id, 'esig_ff_form_id', $form_id);
            WP_E_Sig()->meta->add($doc_id, 'esig_ff_entry_id', $insertId);
          
            WP_E_Sig()->document->saveFormIntegration($doc_id, 'ff');
            
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
    
    
            // Enable reminder from cf7 e-signature settings. 
          //  self::enableReminder($form_id,$doc_id);
    
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
    
            if ($signing_logic == "email") {
    
                if ($invite_controller->saveThenSend($invitation, $doc)) {
                  
                    return true;
                }
    
                
            } elseif ($signing_logic == "redirect") {
                
               
              
                $invitation_id = $invite_controller->save($invitation);
                $invite_hash = WP_E_Sig()->invite->getInviteHash($invitation_id);
                $url =  esigFluentSetting::save_invite_url($invite_hash, $doc->document_checksum);
                
                
            }
        }
        




            
         public function fluentform_submission_confirmation($returnData, $form, $confirmation)
        {
           $formId = $form->id;          
           $feedValue = esigFluentSetting::getEsigFeedSettings($formId);

           $signing_logic = $feedValue['signing_logic'];
             
         
             if($signing_logic == "redirect"){                  
               
                $url =  esigFluentSetting::get_invite_url();            
                $returnData = [  
                   'message'     => 'Form Submitted! Now Redirecting WP E-Signature document for signing',
                   'action'      => 'hide_form',
                   'redirectTo'  => 'customUrl',
                   'redirectUrl' => $url,

               ];  
                
               
              
            } else{
                $confirmation = [
                    'redirectTo'           => 'samePage',  // or customUrl or customPage
                    'messageToShow'        => 'Thank you for your message. We will get in touch with you shortly',
                    'customPage'           => '' ,
                    'samePageFormBehavior' => 'hide_form', // or reset_form 
                    'customUrl'            => '',
                ];
                
                 $returnData = [
                'message' => $confirmation['messageToShow'],
                'action' => $confirmation['samePageFormBehavior'],
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

