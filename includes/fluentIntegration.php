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
    protected $document_view,$plugin_slug;

    public function __construct(Application $app )
    {
        parent::__construct(
            $app,
            'WP E-Signature',
            'wpesignature',
            '_fluentform_wpesignature_settings',
            'wpesignature_feeds',
            1
        );

        $plugin = ESIG_FFDS::get_instance();
        
        $this->plugin_slug = $plugin->get_plugin_slug();

        $this->document_view = new esig_fluentform_document_view();

        //$this->userApi = new UserRegistrationApi;

        $this->logo = ESIG_ESFF_ADDON_URL . "admin/assets/images/e-signature-logo.svg"; //$this->app->url('public/img/integrations/user_registration.png');

        $this->description = 'This add-on allows you to redirect your form-filler or email an individual to review and sign an electronic document.';

        add_filter('fluentform/save_integration_value_' . $this->integrationKey, [$this, 'validate'], 10, 3);
      
       $this->registerAdminHooks();
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
        if(class_exists('esig_sad_document')){
            foreach (esigFluentSetting::get_sad_documents() as $key => $column) {
                $SadFieldOptions[$key] = $column;
            }
        }
      

        $signerName = esigFluentSetting::get_signer_info_field($formId,'name');
        $signerEmail = esigFluentSetting::get_signer_info_field($formId,'email');

        
        $fields = apply_filters('fluentform_wpesignature_feed_fields', [
           
            [
                'key'         => 'enable_esig',
                'label'       => 'E-Signature Integration',
                'required'    => true,
                'placeholder' => 'Your Feed Name',
                'component'   => 'checkbox-single',
                'checkbox_label' => __('Enable E-Signature for this contact form', 'esig'),
            ],
           
            [
                'key'         => 'name',
                'label'       => 'Name',
                'required'    => true,
                'placeholder' => 'Your Feed Name',
                'component'   => 'text'
            ],

            [
                'key'         => 'signer_name',
                'label'       => 'Signer Name',
                'tips'      => 'Select the name field from your fluent form. This field is what the signers full name will be on their WP E-Signature contract.',
                'required'    =>  true, // true/false
                'component'   => 'select', //  component type
                'placeholder' => 'Signer Name',
                'options'     => $signerName
            ],   
            [
                'key'         => 'signer_email',
                'label'       => 'Signer Email',
                'tips'      => 'Select the email field of your signer from your fluent form fields. This field is what the signers email address will be on their WP E-Signature contract.',
                'required'    =>  true, // true/false
                'component'   => 'select', //  component type
                'placeholder' => 'Signer Email',
                'options'     => $signerEmail
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
                'placeholder' => 'Select document',
                'options'     => $SadFieldOptions
            ],           
           
            [
                'key'         => 'underline_data',
                'label'       => 'Display Type',
                'tips'      => 'Please select your desired display type once value display in agreement.',
                'component'   => 'select', //  component type
                'placeholder' => 'Select your desired display type',
                'options'     => [
                    'underline' => 'Underline the data That was submitted from this Fluent form',
                    'notunderline' => 'Do not underline the data that was submitted from the Fluent form',
                ]
            ],
            [
                'key'         => 'signing_reminder',
                'label'       => 'Signing Reminder Email',
                'required'    => false,               
                'component'   => 'checkbox-single',
                'checkbox_label' => __('Enable signing reminders to automatically email the signer if they have not signed.', 'esig'),
                
            ],

            [
                'key'         => 'reminder_email',
                'label'       => 'First Reminder',
                'required'    => false,
                'component'   => 'number',               
                'tips'         => 'Send the first reminder to the signer FIELD days after the initial signing request.',
            ],

            [
                'key'         => 'first_reminder_send',
                'label'       => 'Second Reminder',
                'required'    => false,
                'tips'         => 'Send the second reminder to the signer FIELD days after the initial signing request.',
                'component'   => 'number'
            ],
            [
                'key'         => 'expire_reminder',
                'label'       => 'Last Reminder',
                'required'    => false,
                'tips'         => 'Send the last reminder to the signer FIELD days after the initial signing request.',
                'component'   => 'number'
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
        $errors = [];
        
        $settingsFields = $this->getSettingsFields($settings, $formId);
        foreach ($settingsFields['fields'] as $field) {

            if(empty($settings['enable_esig'])){
                $errors[] = 'Please enabled E-Signature Integration';
            }elseif(empty($settings['signer_name'])){
                $errors[] = 'Signer Name is required.';
            }elseif(empty($settings['signer_email'])){
                $errors[] = 'Signer Email is required.';
            }
            elseif(empty($settings['signing_logic'])){
                $errors[] = 'Signing Logic is required.';
            } elseif (empty($settings['select_sad_doc'])) {
                $errors[] = 'Please select document.';
            }
            elseif(array_key_exists('signing_reminder', $settings) && $settings['signing_reminder'] == '1'){

                if(!array_key_exists('signing_reminder', $settings)){
                    $settings['signing_reminder'] = '';
                }

                if(!array_key_exists('reminder_email', $settings)){
                    $settings['reminder_email'] = '';
                }
                if(!array_key_exists('first_reminder_send', $settings)){
                    $settings['first_reminder_send'] = '';
                }
                if(!array_key_exists('expire_reminder', $settings)){
                    $settings['expire_reminder'] = '';
                }

                if($settings['signing_reminder'] != '1'){
                    $errors[] = 'Please enabled signing reminder first';
                }

                $first_reminder_email = $settings['reminder_email'];
                $second_reminder_email = $settings['first_reminder_send'];
                $expire_reminder = $settings['expire_reminder'];

                if(empty($first_reminder_email)){
                    $errors[] = 'Please enter First Reminder';
                }elseif(empty($second_reminder_email)){
                    $errors[] = 'Please enter Second Reminder';
                }elseif(empty($expire_reminder)){
                    $errors[] = 'Please enter Last Reminder ';
                } 

                if(strpos($first_reminder_email, '-') !== false || $first_reminder_email == '0' || preg_match("/[a-z]/i", $first_reminder_email)){
                    $errors[] = 'Please enter a valid value for signing reminder';
                }

                if(strpos($second_reminder_email, '-') !== false ||  $second_reminder_email == '0' || preg_match("/[a-z]/i",  $second_reminder_email)){
                    $errors[] = 'Please enter a valid value for signing reminder';
                }

                if(strpos($expire_reminder, '-') !== false ||  $expire_reminder == '0' || preg_match("/[a-z]/i",  $expire_reminder)){
                    $errors[] = 'Please enter a valid value for signing reminder';
                }

               
                if (intval($second_reminder_email) <= intval($first_reminder_email)){
                    $errors[] = 'Second reminder should be Greater than First reminder';
                }
                
                if (intval($expire_reminder) <= intval($second_reminder_email)){
                    $errors[] = 'Last reminder should be Greater than Second reminder';
                }	
                

            }
        }

        if ($errors) {
            wp_send_json_error([
                'message' => array_shift($errors),
                'errors' => $errors
            ], 422);
        }

        Helper::setFormMeta($formId, '_has_wpesignature', 'yes');

        return $settings;
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

    
}
