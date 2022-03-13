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

        $this->logo = ESIG_FLUENT_ADDON_URL . "admin/assets/images/e-signature-logo.svg"; //$this->app->url('public/img/integrations/user_registration.png');

        $this->description = 'This add-on makes it possible to automatically email or redirect WP E-Signature document after the user has succesfully submitted a Fluent Forms';

        add_filter('fluentform_save_integration_value_' . $this->integrationKey, [$this, 'validate'], 10, 3);
      
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
        //echo $formId . "testing";
        //update_option("rupom", $formId);
        $SadFieldOptions = [];
        foreach (esigFluentSetting::get_sad_documents() as $key => $column) {
            $SadFieldOptions[$key] = $column;
        }
        
        $fields = apply_filters('fluentform_wpesignature_feed_fields', [
           
            [
                'key'         => 'enable_esig',
                'label'       => 'E-Signature Integration',
                'required'    => true,
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
                'component'    => 'value_text',
                'input_options' => 'all',
            ],
            [
                'key'          => 'signer_email',
                'required' => true,
                'label'        => __('Signer Email', 'esig'),
                'placeholder'  => __('Signer Email', 'esig'),
                'component'    => 'value_text',
                'input_options' => 'emails',
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
                    'underline' => 'Underline the data That was submitted from this Fluent form',
                    'notunderline' => 'Do not underline the data that was submitted from the Fluent form',
                ]
            ],
            [
                'key'         => 'signing_reminder',
                'label'       => 'Signing Reminder Email',
                'required'    => false,               
                'component'   => 'checkbox-single',
                'checkbox_label' => __('Enabling signing reminder email. If/When user has not sign the document', 'esig'),
                
            ],
            [
                'key'                => 'signing_reminder_fields',
                'require_list'       => false, 
                'component'          => 'map_fields',
                'primary_fileds'     => [
                    [
                        'key'           => 'reminder_email',
                        'type'          => 'input_text',
                        'label'         => ' SEND THE FIRST REMINDER TO THE SIGNER AFTER',
                        'required'      => false,
                        'component'   => 'input_number',
                        'help_text' => 'AFTER THE INITIAL SIGNING REQUEST',
                    ],
                    [
                        'key'       => 'first_reminder_send',
                        'label'     => 'SEND THE SECOND REMINDER TO THE SIGNER AFTER',
                        'required'      => false,
                        'component'   => 'text',
                        'help_text' => 'AFTER THE INITIAL SIGNING REQUEST',
                    ],
                    [
                        'key'       => 'expire_reminder',
                        'label'     => 'SEND THE LAST REMINDER TO THE SIGNER AFTER',
                        'required'      => false,
                        'component'   => 'text',
                        'help_text' => 'AFTER THE INITIAL SIGNING REQUEST',
                    ],
                ]
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
        
        $settingsFields = $this->getSettingsFields($settings);
        foreach ($settingsFields['fields'] as $field) {
            if (!wp_validate_boolean($field['required'])) continue;
            if(empty($settings[$field['key']]))
            {
                $errors[$field['key']] = $field['label'] . ' is required.';
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
