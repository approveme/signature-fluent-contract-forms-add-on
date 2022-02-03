<?php

namespace FluentFormEsigature\Integrations;

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class Esigature extends IntegrationManager
{
    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'Esignature',
            'esignature',
            'fluentform_esig_settings',
            'esig_feed',
            36
        );
        $this->logo = ESIG_FLUENT_ADDON_URL . 'assets/images/e-signature-logo.svg';
        $this->description = 'This add-on makes it possible to automatically email a WP E-Signature document (or redirect a user to a document) after the user has succesfully submitted a Fluent Forms.';
       
        $this->registerAdminHooks();
        add_filter('fluentform_form_settings_menu', array($this, 'addFormMenu'));
    }
    
        public function addFormMenu($settingsMenus)
    {
        $settingsMenus['esigature'] = array(
            'slug' => 'form_settings',
            'hash' => 'esignature',
            'route' => '/esigature',
            'title' => $this->title,
        );
        return $settingsMenus;
    }

    public function getGlobalFields($fields)
    {
        return [
            'logo' => $this->logo,
            'menu_title' => __('Esigature Settings', 'esig'),
            'menu_description' => $this->description,
            'valid_message' => __('Your Esig API Key is valid', 'esig'),
            'invalid_message' => __('Your Esig API Key is not valid', 'esig'),
            'save_button_text' => __('Save Settings', 'esig'),
            'fields' => [
                'signer_name' => [
                    'type' => 'text',
                    'placeholder' => 'Name of Fields',
                    'label_tips' => __("Select the name field from your Fluentform. This field is what the signers full name will be on their WP E-Signature contract. You can add first name and last name separated by space.", 'esig'),
                    'label' => __('Signer Name', 'esig'),
                ],
                'signer_email' => [
                    'type' => 'text',
                    'placeholder' => 'Name of Fields',
                    'label_tips' => __("Select the name field from your Fluentform. This field is what the signers full name will be on their WP E-Signature contract. You can add first name and last name separated by space.", 'esig'),
                    'label' => __('Signer Email', 'esig'),
                ],
                'signing_logic' => [
                    'type' => 'select',
                    'placeholder' => 'Select Signing Logic',
                     'label_tips' => __("Please select your desired signing logic once this form is submitted.", 'esig'),
                     'label' => __('Signing Logic', 'esig'),
                     'options' => [
                            
                            'Redirect user to Contract/Agreement after Submission',
                            'Send User an Email Requesting their Signature after Submission',
                            
                      ],
                ],
                'sad_doc' => [
                    'type' => 'select',
                    'placeholder' => 'Select stand alone document',
                     'label_tips' => __("If you would like to can create new document", 'esig'),
                     'label' => __('Select stand alone document', 'esig'),
                     'options' => $clients
                ],
                 'form_data_type' => [
                    'type' => 'select',
                    'placeholder' => 'Select from data type',
                     'label_tips' => __("Select from data type", 'esig'),
                     'label' => __('Select from data type', 'esig'),
                     'options' => $clients
                
                
                ],
            ],

        ];
    }

    public function getGlobalSettings($settings)
    {
        $globalSettings = get_option($this->optionKey);
        if (!$globalSettings) {
            $globalSettings = [];
        }
        $defaults = [
            'signer_name' => '',
            'signer_email' => '',
            'signing_logic' => '',
            'sad_doc' => '',
            'form_data_type' => '',
            'expire_at' => false
        ];

        return wp_parse_args($globalSettings, $defaults);
    }

    public function saveGlobalSettings($settings)
    {


        // Verify API key now
        try {
            $oldSettings = $this->getGlobalSettings([]);
            $oldSettings['signer_name'] = esc_url_raw($settings['signer_name']);
            $oldSettings['signer_email'] = sanitize_text_field($settings['signer_email']);
            $oldSettings['signing_logic'] = sanitize_text_field($settings['signing_logic']);
            $oldSettings['sad_doc'] = sanitize_text_field($settings['sad_doc']);
            $oldSettings['form_data_type'] = sanitize_text_field($settings['form_data_type']);
           
            
            

            update_option($this->optionKey, $oldSettings, 'no');
            wp_send_json_success([
                'message' => $this->optionKey,
                'redirect_url' => admin_url('admin.php?page=fluent_forms_settings#general-esigature-settings')
            ], 200);
        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function pushIntegration($integrations, $formId)
    {
        $integrations[$this->integrationKey] = [
            'title' => $this->title . ' Integration',
            'logo' => $this->logo,
            'is_active' => $this->isConfigured(),
            'configure_title' => 'Configration required!',
            'global_configure_url' => admin_url('admin.php?page=fluent_forms_settings#general-getgist-settings'),
            'configure_message' => 'Esig is not configured yet! Please configure your Esig api first',
            'configure_button_text' => 'Set Esig API'
        ];
        return $integrations;
    }

    public function getIntegrationDefaults($settings, $formId)
    {
        return [
            'name' => '',
            'list_id' => '',
            'fields' => (object)[],
            'other_fields_mapping' => [
                [
                    'item_value' => '',
                    'label' => ''
                ]
            ],
            'conditionals' => [
                'conditions' => [],
                'status' => false,
                'type' => 'all'
            ],
            'resubscribe' => false,
            'enabled' => true
        ];
    }

    public function getSettingsFields($settings, $formId)
    {
        return [
            'fields' => [
                [
                    'key' => 'name',
                    'label' => 'Feed Name',
                    'required' => true,
                    'placeholder' => 'Your Feed Name',
                    'component' => 'text'
                ],
                
                [
                    'key' => 'CustomFields',
                    'label' => 'Map Fields',
                    'tips' => 'Select which Fluent Form fields pair with their<br /> respective Esig fields.',
                    'component' => 'map_fields',
                    'field_label_remote' => 'Esig Fields',
                    'field_label_local' => 'Form Field',
                    'primary_fileds' => [
                        [
                            'key' => 'email',
                            'label' => 'Email Address',
                            'required' => true,
                            'input_options' => 'emails'
                        ]
                    ]
                ],
                [
                    'key' => 'other_fields_mapping',
                    'require_list' => false,
                    'label' => 'Other Fields',
                    'tips' => 'Select which Fluent Form fields pair with their<br /> respective Esig fields.',
                    'component' => 'dropdown_many_fields',
                    'field_label_remote' => 'Esig Field',
                    'field_label_local' => 'Esig Field',
                    'options' => $this->otherFields()
                ],
                [
                    'key' => 'tags',
                    'label' => 'Lead Tags',
                    'required' => false,
                    'placeholder' => 'Tags',
                    'component' => 'value_text',
                    'inline_tip' => 'Use comma separated value. You can use smart tags here'
                ],
                [
                    'key' => 'landing_url',
                    'label' => 'Landing URL',
                    'tips' => 'When this option is enabled, FluentForm will pass the form page url to the Esig lead',
                    'component' => 'checkbox-single',
                    'checkobox_label' => 'Enable Landing URL'
                ],
                [
                    'key' => 'last_seen_ip',
                    'label' => 'Push IP Address',
                    'tips' => 'When this option is enabled, FluentForm will pass the last_seen_ip to Esig',
                    'component' => 'checkbox-single',
                    'checkobox_label' => 'Enable last IP address'
                ],
                [
                    'key' => 'conditionals',
                    'label' => 'Conditional Logics',
                    'tips' => 'Allow Esig integration conditionally based on your submission values',
                    'component' => 'conditional_block'
                ],
                [
                    'key' => 'enabled',
                    'label' => 'Status',
                    'component' => 'checkbox-single',
                    'checkobox_label' => 'Enable This feed'
                ]
            ],
            'integration_title' => $this->title
        ];
    }

    protected function getLists()
    {
        return [];
    }

    public function getMergeFields($list = false, $listId = false, $formId = false)
    {
        return [];
    }

    public function otherFields()
    {
        // BIND STATIC CAUSE SOME FIELDS ARE NOT SUPPORTED
        $attributes = [
            "title" => "Title",
            "firstname" => "FirstName",
            "lastname" => "Last Name",
            "company" => "Company",
            "position" => "Position",
            "phone" => "Phone",
            "mobile" => "Mobile",
            "address1" => "Address1",
            "address2" => "Address2",
            "city" => "City",
            "zipcode" => "Zipcode",
            "country" => "Country",
            "fax" => "Fax",
            "website" => "Website",
            "facebook" => "Facebook",
            "foursquare" => "Foursquare",
            "googleplus" => "Googleplus",
            "instagram" => "Instagram",
            "linkedin" => "Linkedin",
            "skype" => "Skype",
            "twitter" => "Twitter"
        ];

        return $attributes;
    }

    /*
     * Form Submission Hooks Here
     */
    public function notify($feed, $formData, $entry, $form)
    {
        $feedData = $feed['processedValues'];


        $subscriber = [
            'name' => ArrayHelper::get($feedData, 'lead_name'),
            'email' => ArrayHelper::get($feedData, 'email'),
            'phone' => ArrayHelper::get($feedData, 'phone'),
            'created_at' => time(),
            'last_seen_at' => time()
        ];

        $tags = ArrayHelper::get($feedData, 'tags');
        if ($tags) {
            $tags = explode(',', $tags);
            $formtedTags = [];
            foreach ($tags as $tag) {
                $formtedTags[] = wp_strip_all_tags(trim($tag));
            }
            $subscriber['tags'] = $formtedTags;
        }

        if (ArrayHelper::isTrue($feedData, 'landing_url')) {
            $subscriber['landing_url'] = $entry->source_url;
        }

        if (ArrayHelper::isTrue($feedData, 'last_seen_ip')) {
            $subscriber['last_seen_ip'] = $entry->ip;
        }

        $subscriber = array_filter($subscriber);

        if (!empty($subscriber['email']) && !is_email($subscriber['email'])) {
            $subscriber['email'] = ArrayHelper::get($formData, $subscriber['email']);
        }

        foreach (ArrayHelper::get($feedData, 'other_fields_mapping') as $item) {
            $subscriber[$item['label']] = $item['item_value'];
        }


        if (!is_email($subscriber['email'])) {
            return;
        }

    //    $api = $this->getRemoteClient();
        $response = $api->subscribe($subscriber);

        if (is_wp_error($response)) {
            // it's failed
            do_action('ff_log_data', [
                'parent_source_id' => $form->id,
                'source_type' => 'submission_item',
                'source_id' => $entry->id,
                'component' => $this->integrationKey,
                'status' => 'failed',
                'title' => $feed['settings']['name'],
                'description' => $response->errors['error'][0][0]['message']
            ]);
        } else {
            // It's success
            do_action('ff_log_data', [
                'parent_source_id' => $form->id,
                'source_type' => 'submission_item',
                'source_id' => $entry->id,
                'component' => $this->integrationKey,
                'status' => 'success',
                'title' => $feed['settings']['name'],
                'description' => 'Esig feed has been successfully initialed and pushed data'
            ]);
        }
    }


}
