<?php
namespace esigFluentIntegration;

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
                    $fieldsArray = [];
                    foreach ($value as $name) {                       
                        
                        
                        if (array_key_exists("label",$name['settings']))
                        {
                        $labelname = $name['settings']['label'];
                        } else{
                            $labelname = $name['settings']['admin_field_label'];
                        }                   
                        
                        $fieldsArray[$labelname]= $name['attributes']['name'];
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

            /**
         * Generate fields option using form id
         * @param type $form_id
         * @return string
         */
        public static function get_value($data,$label,$field_id, $display, $option) {
            

            $label = $label;

            // print_r($data);
            if (is_array($data)) {

                if ($display == "value") {                   
                    $value = isset($data[$field_id]) ? $data[$field_id] : false;
                   // return $data;
                    $result = '';
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $result .= $val . " ,";
                        } 

                        if($field_id == "checkbox"){
                            $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" checked >'.$item.'</li>';
                                }
                            }
                            return  "<ul class='esig-checkbox-tick'>$items</ul>";
                           // return $label . ": " ."<a href=".substr($result, 0, strlen($result) - 2).">".basename(substr($result, 0, strlen($result) - 2))."</a>";
                        
                        }
                        
                        if($field_id == "multi_select"){
                        $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" checked >'.$item.'</li>';
                                }
                            }
                            return "<ul class='esig-checkbox-tick'>$items</ul>";
                        }


                     
                        
                        if($field_id == "file-upload" || $field_id == "image-upload"){
                            return "<a href=".substr($result, 0, strlen($result) - 2).">".basename(substr($result, 0, strlen($result) - 2))."</a>";
                        
                        }
                        
                        return substr($result, 0, strlen($result) - 2);
                    }
                    
                    if($field_id == "input_radio"){
                       $value = '<input type="radio" id='.$value.' onclick="return false;" checked> '.$value.'';
                    }
                    
                    
                    if($field_id == "url"){
                        $value = "<a href='$value'>".$value."</a>";
                    }                 
                    return $value;

                


                } elseif ($display == "label_value") {                   

                    $value = isset($data[$field_id]) ? $data[$field_id] : false;
                    $result = '';
                    
                   
                    
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $result .= $val . " ,";
                        } 

                        
                        if($field_id == "checkbox"){
                            $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" checked >'.$item.'</li>';
                                }
                            }
                            return $label . ": " ."<ul class='esig-checkbox-tick'>$items</ul>";
                           // return $label . ": " ."<a href=".substr($result, 0, strlen($result) - 2).">".basename(substr($result, 0, strlen($result) - 2))."</a>";
                        
                        }
                        
                        if($field_id == "multi_select"){
                        $items = '';
                            foreach ($value as $item) {
                                if ($item) {
                                    $items .= '<li><input type="checkbox" onclick="return false;" checked >'.$item.'</li>';
                                }
                            }
                            return $label . ": " ."<ul class='esig-checkbox-tick'>$items</ul>";
                        }


                     
                        
                        if($field_id == "file-upload" || $field_id == "image-upload"){
                            return $label . ": " ."<a href=".substr($result, 0, strlen($result) - 2).">".basename(substr($result, 0, strlen($result) - 2))."</a>";
                        
                        }
                        
                        return $label . ": " . substr($result, 0, strlen($result) - 2);
                    }
                    
                    if($field_id == "input_radio"){
                       $value = '<input type="radio" id='.$value.' onclick="return false;" checked> '.$value.'';
                    }
                    
                    
                    if($field_id == "url"){
                        $value = "<a href='$value'>".$value."</a>";
                    }                 
                    return $label . ": " . $value;

                }
            }
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
