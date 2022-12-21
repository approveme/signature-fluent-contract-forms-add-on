<?php
namespace esigFluentIntegration;

use Mpdf\Tag\U;
use WP_E_Invite;

class esigFluentSetting {

        
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

    public static function get_signer_info_field($form_id,$fields)
    {
        

        $choices = [];
        $signerInfo = [];
        $formFields = esigFluentSetting::getAllFluentFormFields($form_id);

        foreach ($formFields as $fieldlabel=>$fieldname) {
            if($fieldname != "email" && $fields == 'email'){
                continue;
            }
            if($fieldname != "names" && $fields == 'name'){
                continue;
            }
             $choices[$fieldname] = $fieldlabel;            
        }       

        foreach ($choices as $key => $column) {
            $signerInfo[$key] = $column;
        }

        return $signerInfo;
    }
   
    
     public static function getEntryValue($formId,$enttyID){

       
       $entyValue = wpFluent()->table('fluentform_entry_details')
                    ->where('form_id', $formId)
                    ->where('submission_id', $enttyID);

       return $entyValue ;

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

    public static function getHtmlFieldsValue($formID,$names){

        if(!function_exists('wpFluent')) return false;

        $forms = wpFluent()->table('fluentform_forms')
								->select(['form_fields'])
								->orderBy('id', 'DESC')
                                ->where('id', $formID)
								->get();

        $formArray = json_decode(json_encode($forms), true);
        if(!is_array($formArray)) return false;       
        $fields = json_decode($formArray[0]['form_fields'], true);
	    $labelname = '';

        if(!is_array($fields)) return false;

		foreach ($fields as $value) {
                 
                    foreach ($value as $name) {
                        
                        if(array_key_exists("html_codes",$name['settings'])){
                          return $name['settings']['html_codes'];
                                                 
                        }
                                        
                    }                     
		}               
            

    }

    public static function getAllFluentFormFields($formID){
        $forms = wpFluent()->table('fluentform_forms')
								->select(['form_fields'])
								->orderBy('id', 'DESC')
                                ->where('id', $formID)
								->get();

        $formArray = json_decode(json_encode($forms), true);       
        $fields = json_decode($formArray[0]['form_fields'], true);
	    $fieldsArray = [];
        
		foreach ($fields as $value) {

            
                  
                    foreach ($value as $name) {
                        
                      

                        if (array_key_exists("label",$name['settings']))
                        {
                        $labelname = $name['settings']['label'];
                        }                        
                        else{
                            $labelname = $name['settings']['admin_field_label'];
                        } 
                        
                        
                        if(array_key_exists("html_codes",$name['settings'])){
                            $labelname = 'Custom/Html';
                            $fieldsArray[$labelname]= 'html_codes';                          
                        } else{
                            $fieldsArray[$labelname]= $name['attributes']['name']; 
                        }    
                        
                        
                       
                    }
                    
                    return $fieldsArray;
                  
		}
                
                
    }

    public static function save_submission_value($document_id, $form_id, $formData) 
    {
        WP_E_Sig()->meta->add($document_id, "esig_fluent_forms_submission_value", json_encode($formData));
    }

    public static function checkboxValue($value)
    {
        if(!is_array($value)) return false;

        $items = '';
        foreach ($value as $item) {
            if ($item) {
                $items .= '<li><input type="checkbox" onclick="return false;" readonly checked="checked">' . esc_attr($item) . '</li>';
            }
        }
        return  "<ul class='esig-checkbox-tick'>$items</ul>";
    }

    public static function checkboxGridValue($value)
    {
        if(!is_array($value)) return false;

        $items = '';
        foreach ($value as $key => $item) {
            foreach ($item as $newItem) {
                $items .= '<li>'. $key .' - <input type="checkbox" onclick="return false;" readonly checked="checked">' . esc_attr($newItem) . '</li>';
            }

        }
        return  "<ul class='esig-checkbox-tick'>$items</ul>";
    }
    

    public static function repeaterValue($value)
    {
        if (!is_array($value)) return false;
        $items = '';
        foreach ($value as $val) {

            foreach ($val as $item) {
                if ($item) {
                    $items .=  $item . '<br>';
                }
            }
        } 
        return $items; 
    }

    public static function arrayValue($value)
    {
        if (!is_array($value)) return false;
        $items = '';
        foreach ($value as $item) {
            if ($item) {
                $items .=  $item . ' ';
            }
        }
        return $items; 
    }

    public static function addressValue($value)
    {
        if(!is_array($value)) return false;
        $result = '';
        foreach ($value as $key => $val) {

            if ($key == 'country') {
                $countries = wpFluentForm()->load(
                    wpFluentForm()->appPath('Services/FormBuilder/CountryNames.php')
                );
                $result .= $countries[$val] . '.';
            } else {
                if ($val) {
                    $result .= $val . ',  ';
                }
            }
        }
        return $result;
    }


    public static function fileValue($value,$style)
    {
        
        $items = '';
            foreach ($value as $item) {               
                if ($item) {
                    $items .=  '<a href='.$item.' style='.$style.' >'.basename($item).'</a><br>';  
                }
            }
                           
        return $items;
    }

    public static function generateValue($data,$fieldId,$formId,$displayType)
    {
        $style = '';
        if($displayType == 'underline'){
            $style = 'text-decoration:underline;';
        }

        if(!is_array($data)) return false;
        $value  = esig_esff_get($fieldId,$data);
        switch($fieldId){
            case "checkbox":
                return self::checkboxValue($value);
                break;
            case "tabular_grid":
                return self::checkboxGridValue($value);
                break;
            case "multi_select":
                return self::checkboxValue($value);
                break;
            case "repeater_field":
                return self::repeaterValue($value);
                break;
            case "address_1":
                return self::addressValue($value);
                break;
            case "html_codes":
                return self::getHtmlFieldsValue($formId, 'html_codes');
                break;            
            case "email":
                return '<a style="'. esc_attr($style) .'" href="mailto:' . esc_url($value) . '" target="_blank">' . esc_attr($value) . '</a>' ;
                break;  
            case "url":
                return '<a style="'. esc_attr($style) .'" href="' . esc_url($value) . '" target="_blank">' . esc_attr($value) . '</a>' ;
                break;
            case "file-upload":            
                return self::fileValue($value,$style);
                break;   
            case "image-upload":            
                return self::fileValue($value,$style);
                break;     
            default:
                if(is_array($value)) return self::arrayValue($value);
                return $value;
        }
    }

            /**
         * Generate fields option using form id
         * @param type $form_id
         * @return string
         */
        public static function get_value($data,$label,$formid,$field_id, $display, $option,$submit_type) {
            
            if ($display == "label") {
                return $label;
            }

            $displayValue = self::generateValue($data,$field_id,$formid,$submit_type);

            if($display == "value") return $displayValue;

            if($display == "label_value") return $label  . ": " . $displayValue;

            return false;

        }
        
        
        public static function display_value($ff_value, $submit_type) {

            $result = '';
            if ($submit_type == "underline") {
                $result .= '<u>' . $ff_value . '</u>';
            } else {
                $result .= $ff_value;
            }
            return $result;
        }
    
        public static function parseInput($string)
        {
            $results = preg_replace('/^{(.*)}$/', '$1', $string);
            $array = explode(".", $results);
            return esig_esff_get("1",$array);
        }

        public static function prepareNames($names)
        {
            if(!is_array($names)) return false;
            $result = false;
            foreach($names as $name)
            {
                $result .= $name . " ";
            }
            return rtrim($result);
        }

    
}
