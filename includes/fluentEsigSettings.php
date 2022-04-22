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

    public static function checkboxValue($value)
    {
        if(!is_array($value)) return false;

        $items = '';
        foreach ($value as $item) {
            if ($item) {
                $items .= '<li><input type="checkbox" onclick="return false;" readonly checked="checked">' . $item . '</li>';
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
                $items .=  $item;
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

    public static function generateValue($data,$fieldId,$formId)
    {
        if(!is_array($data)) return false;
        $value  = esigget($fieldId,$data);
        switch($fieldId){
            case "checkbox":
                return self::checkboxValue($value);
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

            $displayValue = self::generateValue($data,$field_id,$formid);

            if($display == "value") return $displayValue;

            if($display == "label_value") return $label  . ": " . $displayValue;

            return false;

            // print_r($data);
            /*if (is_array($data)) {

                if ($display == "value") {                   
                    $value = isset($data[$field_id]) ? $data[$field_id] : false;
                   // return $data;
                    $result = '';
                    if (is_array($value)) {
                        
                      if($field_id == "file-upload" || $field_id == "image-upload"){
                           
                            $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .=  $item;  
                                }
                            }
                            return "<a href=".$items.">".basename($items)."</a>";
                        
                        }elseif($field_id == "address_1"){
                            
                            foreach ($value as $key => $val) {                           

                                if($key == 'country'){                                    
                                    $result .= $val . '.   ';
                                }else{
                                    if($val){
                                        $result .= $val . ', ';
                                    }                                    
                                    
                                }                              
                               
                            }                            
                        }else{
                            foreach ($value as $val) {
                                $result .= $val . ' ';
                            } 
                        }
                        
                        return substr($result, 0, strlen($result) - 2);
                    }
                    
                  
                    
                    if($field_id == "input_radio"){
                       $value = '<input type="radio" id='.$value.' onclick="return false;" readonly checked="checked"> '.$value.'';
                    }
                    
                    
                    if($field_id == "url"){
                        $value = ($submit_type == "underline") ? '<a href="' . $value . '" target="_blank"><u>' . $value . '</u></a>' : '<a href="' . $value . '" target="_blank">' . $value . '</a>';
                    }  
                    
                    if($field_id == "email"){
                        $value = ($submit_type == "underline") ?  '<a href="mailto:' . $value . '" target="_blank"><u>' . $value . '</u></a>' :  '<a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
                      
                    } 
                    
                    if($field_id == "html_codes"){
                       $value =  self::getHtmlFieldsValue($formid,'html_codes');
                    }
                    return $value;

                


                } elseif ($display == "label_value") {                   

                    $value = isset($data[$field_id]) ? $data[$field_id] : false;
                    $result = '';                          
                    
                    if (is_array($value)) {
                        

                        if($field_id == "address_1"){
                            
                            foreach ($value as $key => $val) {                           

                                if($key == 'country'){    
                                    $countries = wpFluentForm()->load(
                                    wpFluentForm()->appPath('Services/FormBuilder/CountryNames.php')
                                ) ;                               
                                    $result .=$countries[$val] . '.   ';
                                }else{
                                    if($val){
                                        $result .= $val . ', ';
                                    }                                    
                                    
                                }                              
                               
                            }

                            
                        }elseif($field_id == "checkbox"){
                            $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" readonly checked="checked">'.$item.'</li>';
                                }
                            }
                            return $label . ": " ."<ul class='esig-checkbox-tick'>$items</ul>";
                           // return $label . ": " ."<a href=".substr($result, 0, strlen($result) - 2).">".basename(substr($result, 0, strlen($result) - 2))."</a>";
                        
                        }elseif($field_id == "multi_select"){
                        $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" readonly checked="checked">'.$item.'</li>';
                                }
                            }
                            return $label . ": " ."<ul class='esig-checkbox-tick'>$items</ul>";
                        }elseif($field_id == "repeater_field"){
                            $items = '';
                        
                            foreach ($value as $val) {
                                
                                foreach ($val as $item) {
                                if ($item) {
                                    $items .= $item.'<br>';
                                }
                                }
                               
                            } 

                            return $label . ": " . $items;
                        }elseif($field_id == "file-upload" || $field_id == "image-upload"){
                           
                            
                            $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .=  $item;  
                                }
                            }
                           
                            return $label . ": " ."<a href=".$items.">".basename($items)."</a>";
                            
                        }else{
                            foreach ($value as $val) {
                                $result .= $val . ' ';
                            } 
                        }
                        
                        return $label . ": " . substr($result, 0, strlen($result) - 2);
                    }
                    
                    if($field_id == "input_radio"){
                       $value = '<input type="radio" id='.$value.' onclick="return false;" readonly checked="checked"> '.$value.'';
                    }                    
                    
                    if($field_id == "url"){
                        $value = ($submit_type == "underline") ? '<a href="' . $value . '" target="_blank"><u>' . $value . '</u></a>' : '<a href="' . $value . '" target="_blank">' . $value . '</a>';
                    }  
                    
                    if($field_id == "email"){
                        $value = ($submit_type == "underline") ?  '<a href="mailto:' . $value . '" target="_blank"><u>' . $value . '</u></a>' :  '<a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
                      
                    } 
                    if($field_id == "html_codes"){                     
                        
                       $value =  self::getHtmlFieldsValue($formid,'html_codes');
                    }
                    return $label . ": " . $value;

                }
            }
            return false;*/
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
            return esigget("1",$array);
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
