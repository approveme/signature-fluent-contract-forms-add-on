<?php
namespace esigFluentIntegration;

use WP_E_Invite;

class esigFluentSetting {

    const ESIG_FF_COOKIE = 'esig-ff-redirect';
        const FF_COOKIE = 'esig-ff-temp-data';
        const FF_FORM_ID_META = 'esig_ff_form_id';
        const FF_ENTRY_ID_META = 'esig_ff_entry_id';
    public static function get_sad_documents()
    {
        if (!function_exists('WP_E_Sig'))
        return;

        $api = WP_E_Sig();


        if (!class_exists('esig_sad_document'))
             return;

        $sad = new \esig_sad_document();

        $sad_pages = $sad->esig_get_sad_pages();

        $choices = [];


        foreach ($sad_pages as $page) {
            $document_status = $api->document->getStatus($page->document_id);

            if ($document_status != 'trash') {
                if ('publish' === get_post_status($page->page_id)) {
                    $choices[$page->page_id] = get_the_title($page->page_id);
                    
                }
            }

            
        }

        return $choices;
    }

    public static function getEsigFeedSettings($formId){

       $getEsigFeed = (new \FluentForm\App\Modules\Form\Form(wpFluentForm()));
       $feedValue = $getEsigFeed->getMeta($formId, 'wpesignature_feeds', true);

       return $feedValue ;

    }

    public static function getAllFluentForm(){
        $forms = wpFluent()->table('fluentform_forms')
								->select(['id', 'title'])
								->orderBy('id', 'DESC')
								->get();

        $formArray = json_decode(json_encode($forms), true);

        return $formArray;
    }

    public static function getAllFluentFormFields($formID){
        $forms = wpFluent()->table('fluentform_forms')
								->select(['form_fields'])
								->orderBy('id', 'DESC')
                                ->where('id', $formID)
								->get();

        $formArray = json_decode(json_encode($forms), true);

        return $formArray;
    }

    public static function esigget($name, $array = null) {

        if (!isset($array) && function_exists('ESIG_GET')) {
            return ESIG_GET($name);
        }

        if (is_array($array)) {
            if (isset($array[$name])) {
                return wp_unslash($array[$name]);
            }
            return false;
        }

        if (is_object($array)) {
            if (isset($array->$name)) {
                return wp_unslash($array->$name);
            }
            return false;
        }

        return false;
    }

    public static function save_submission_value($document_id, $form_id, $formData) 
    {
        WP_E_Sig()->meta->add($document_id, "esig_fluent_forms_submission_value", json_encode($formData));
    }

    public static function save_invite_url($invite_hash, $document_checksum) {
        $invite_url = WP_E_Invite::get_invite_url($invite_hash, $document_checksum);
        
        echo $invite_url ;
        exit;
        
        esig_setcookie(self::ESIG_FF_COOKIE, $invite_url, 600);
        $_COOKIE[self::ESIG_FF_COOKIE] = $invite_url;
    }

    public static function get_invite_url() {
        return esigget(self::ESIG_FF_COOKIE, $_COOKIE);
    }

    public static function remove_invite_url() {
        setcookie(self::ESIG_FF_COOKIE, null, time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
    }
    

    
}