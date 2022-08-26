<?php

if (!function_exists('ESIG_ESFF_GET')) {

    function ESIG_ESFF_GET($key, $array = false) {

        if ($array) {
            return filter_input(INPUT_GET, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }

        if (filter_input(INPUT_GET, $key)) {
            return filter_input(INPUT_GET, $key);
        }

        return false;
    }

}

if (!function_exists('esig_esff_get')) {

    function esig_esff_get($name, $array = null) {

        if (!isset($array)) {
            // check for wpesign encoding in get method . 
            if(ESIG_ESFF_GET("wpesig"))
            {
                 $esigData =  WP_E_Invite::urlDecode(ESIG_ESFF_GET("wpesig"));
                
                 if(is_array($esigData))
                 {
                    if (isset($esigData[$name])) {
                        return sanitize_text_field(wp_unslash($esigData[$name]));
                    } 
                 }
            }
            return ESIG_ESFF_GET($name);
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

}

?>