<?php

namespace esigFluentIntegration;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use ESIG_FFDS;
use ESIG_FFFDS;
use esig_fluentform_document_view;
use esig_sad_document;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\ConditionAssesor;
use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use WP_E_Common;
use WP_E_invitationsController;

class esigFluent extends IntegrationManager
{
    public $category = 'wp_core';
    public $disableGlobalSettings = 'yes';
    protected $form;

    public function __construct(Application $app )
    {
        parent::__construct(
            $app,
            'WP E-signature',
            'wpesignature',
            '_fluentform_wpesignature_settings',
            'wpesignature_feeds',
            1
        );

        $plugin = ESIG_FFDS::get_instance();
        
        $this->plugin_slug = $plugin->get_plugin_slug();

        $this->document_view = new esig_fluentform_document_view();

        //$this->userApi = new UserRegistrationApi;

        $this->logo = ESIG_FLUENT_ADDON_URL . "assets/images/e-signature-logo.svg"; //$this->app->url('public/img/integrations/user_registration.png');

        $this->description = 'Create signature document when when a form is submitted.';

       // add_filter('fluentform_notifying_async_UserRegistration', '__return_false');

       // add_filter('fluentform_save_integration_value_' . $this->integrationKey, [$this, 'validate'], 10, 3);

       // add_filter('fluentform_validation_user_registration_errors', [$this, 'validateSubmittedForm'], 10, 3);
       
       add_filter('esig_sif_buttons_filter', array($this, 'add_sif_fluentform_buttons'), 12, 1);
       add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_fluentform_text_menu'), 12, 1);
       add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
       add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
       add_action('wp_ajax_esig_fluent_form_fields', array($this, 'esig_fluent_form_fields'));
       add_action('fluentform_before_form_actions_processing', array($this, 'fluentform_submission'), 10, 3);
       
      
       $this->registerAdminHooks();
    }


    public function esig_fluent_form_fields() {


        if (!function_exists('WP_E_Sig'))
            return;


        $html = '';

        $html .= '<select name="esig_ff_field_id" class="chosen-select" style="width:250px;">';
        $form_id = $_POST['form_id'];
        //$forms = Caldera_Forms::get_forms();

        $test = esigFluentSetting::getAllFluentFormFields($form_id);

        print_r($test);
        $form = $getEsigFeed = (new \FluentForm\App\Modules\Form\Form(wpFluentForm()));
        $form = apply_filters('caldera_forms_render_get_form', $form);
        foreach ($form['fields'] as $field) {
            if ($field['label'] == 'submit') {
                continue;
            }
            $html .= '<option value=' . $field['ID'] . '>' . $field['label'] . '</option>';
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
            wp_enqueue_script('fluentform-add-admin-script', plugins_url('js/esig-add-fluentform.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
        }
        
        if (esigFluentSetting::esigget("id",$screen) != "plugins") {
            wp_enqueue_script('fluentform-add-admin-script', plugins_url('js/esig-fluentform-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
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
        self::esig_invite_document($document_id, $signer_email, $signer_name, $formId,$insertId, $signing_logic,$formData);

       

      
    }
    

    public function pushIntegration($integrations, $formId)
    {
        
        $integrations[$this->integrationKey] = [
            'category'                => 'wp_core',
            'disable_global_settings' => 'yes',
            'logo'                    => $this->logo,
            'title'                   => $this->title,
            'is_active'               => $this->isConfigured()
        ];

        return $integrations;
    }

    public function getIntegrationDefaults($settings, $formId = null)
    {
        $fields = [
            'name'                 => '',
            'Email'                => '',
            'username'             => '',
            'CustomFields'         => (object)[],
            'userRole'             => 'subscriber',
            'userMeta'             => [
                [
                    'label' => '', 'item_value' => ''
                ]
            ],
            'enableAutoLogin'      => false,
            'sendEmailToNewUser'   => false,
            'validateForUserEmail' => true,
            'conditionals'         => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'              => true
        ];

        return apply_filters('fluentform_wpesignature_field_defaults', $fields, $formId);
    }

    public function getSettingsFields($settings, $formId = null)
    {

        $SadFieldOptions = [];
        foreach (esigFluentSetting::get_sad_documents() as $key => $column) {
            $SadFieldOptions[$key] = $column;
        }
        
        $fields = apply_filters('fluentform_wpesignature_feed_fields', [
           
            [
                'key'         => 'enable_esig',
                'label'       => 'E-Signature Integration',
                'required'    => false,
                'placeholder' => 'Your Feed Name',
                'component'   => 'checkbox-single',
                'checkbox_label' => __('Enable E-Signature for this contract form', 'esig'),
            ],
           
            [
                'key'         => 'name',
                'label'       => 'Name',
                'required'    => true,
                'placeholder' => 'Your Feed Name',
                'component'   => 'text'
            ],
            [
                'key'          => 'signer_name',
                'required' => true,
                'label'        => __('Signer Name', 'esig'),
                'placeholder'  => __('Signer Name', 'esig'),
                'component'    => 'value_text'
            ],
            [
                'key'          => 'signer_email',
                'required' => true,
                'label'        => __('Signer Email', 'esig'),
                'placeholder'  => __('Signer Email', 'esig'),
                'component'    => 'value_text'
            ],
            [
                'key'         => 'signing_logic',
                'label'       => 'Signing Logic',
                'tips'      => 'Please select your desired signing logic once this form is submitted.',
                'required'    =>  true, // true/false
                'component'   => 'select', //  component type
                'placeholder' => 'Select desired signing logic',
                'options'     => [
                    'redirect' => 'Redirect user to Contract/Agreement after Submission',
                    'email' => 'Send User an Email Requesting their Signature after Submission',
                ]
            ],   
            [
                'key'         => 'select_sad_doc',
                'label'       => 'Select Document',
                'tips'      => 'If you would like to can create new document',
                'required'    =>  true, // true/false
                'component'   => 'select', //  component type
                'placeholder' => 'Select Sad document',
                'options'     => $SadFieldOptions
            ],           
           
            [
                'key'         => 'underline_data',
                'label'       => 'Display Type',
                'tips'      => 'Please select your desired display type once value display in agreement.',
                'component'   => 'select', //  component type
                'placeholder' => 'Select your desired display type',
                'options'     => [
                    'underline' => 'Underline the data That was submitted from this Formidable form',
                    'notunderline' => 'Do not underline the data that was submitted from the Formidable Form',
                ]
            ],
            [
                'key'         => 'signing_reminder',
                'label'       => 'Signing Reminder Email',
                'required'    => false,               
                'component'   => 'checkbox-single',
                'checkbox_label' => __('Enabling signing reminder email. If/When user has not sign the document', 'esig'),
            ],
            
        ], $formId);

        return [
            'fields'              => $fields,
            'button_require_list' => false,
            'integration_title'   => $this->title
        ];
    }

    public function validate($settings, $integrationId, $formId)
    {
        $parseSettings = $this->userApi->validate(
            $settings,
            $this->getSettingsFields($settings)
        );

        Helper::setFormMeta($formId, '_has_user_registration', 'yes');

        return $parseSettings;
    }

    public function validateSubmittedForm($errors, $data, $form)
    {
        $feeds = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $form->id)
            ->where('meta_key', 'user_registration_feeds')
            ->get();

        if (!$feeds) {
            return $errors;
        }

        foreach ($feeds as $feed) {
            $parsedValue = json_decode($feed->value, true);

            if (!ArrayHelper::isTrue($parsedValue, 'validateForUserEmail')) {
                continue;
            }

            if ($parsedValue && ArrayHelper::isTrue($parsedValue, 'enabled')) {
                // Now check if conditions matched or not
                $isConditionMatched = $this->checkCondition($parsedValue, $data);
                if (!$isConditionMatched) {
                    continue;
                }
                $email = ArrayHelper::get($data, $parsedValue['Email']);
                if (!$email) {
                    continue;
                }

                if (email_exists($email)) {
                    if (!isset($errors['restricted'])) {
                        $errors['restricted'] = [];
                    }
                    $errors['restricted'][] = __('This email is already registered. Please choose another one.', 'fluentformpro');
                    return $errors;
                }

                if (!empty($parsedValue['username'])) {
                    $userName = ArrayHelper::get($data, $parsedValue['username']);
                    if ($userName) {
                        if (username_exists($userName)) {
                            if (!isset($errors['restricted'])) {
                                $errors['restricted'] = [];
                            }
                            $errors['restricted'][] = __('This username is already registered. Please choose another one.', 'fluentformpro');
                            return $errors;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /*
     * Form Submission Hooks Here
     */
    public function notify($feed, $formData, $entry, $form)
    {

        $feedData = $feed['processedValues'];
        die();

        do_action('ff_log_data', [
            'parent_source_id' => $form->id,
            'source_type' => 'submission_item',
            'source_id' => $entry->id,
            'component' => $this->integrationKey,
            'status' => 'failed',
            'title' => $feed['settings']['name'],
            'description' =>  $feedData
        ]);
        exit;

        $feedData = $feed['processedValues'];
        $row = [];
        $metaFields = $feedData['meta_fields'];

        if(!$metaFields) {
            return do_action('ff_integration_action_result', $feed, 'failed', 'No meta fields found');
        }

        foreach ($metaFields as $field) {
            $row[] = wp_unslash(sanitize_textarea_field(ArrayHelper::get($field, 'item_value')));
        }
    }

    // There is no global settings, so we need
    // to return true to make this module work.
    public function isConfigured()
    {
        return true;
    }

    // This is an absttract method, so it's required.
    public function getMergeFields($list, $listId, $formId)
    {
        // ...
    }

    // This method should return global settings. It's not required for
    // this class. So we should return the default settings otherwise
    // there will be an empty global settings page for this module.
    public function addGlobalMenu($setting)
    {
        return $setting;
    }

    private function checkCondition($parsedValue, $formData)
    {
        $conditionSettings = ArrayHelper::get($parsedValue, 'conditionals');
        if (
            !$conditionSettings ||
            !ArrayHelper::isTrue($conditionSettings, 'status') ||
            !count(ArrayHelper::get($conditionSettings, 'conditions'))
        ) {
            return true;
        }

        return ConditionAssesor::evaluate($parsedValue, $formData);
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
        //esigFluentSetting::save_file_url($doc_id);
        // set document timezone
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

            esigFluentSetting::save_invite_url($invite_hash, $doc->document_checksum);
        }
    }
}
