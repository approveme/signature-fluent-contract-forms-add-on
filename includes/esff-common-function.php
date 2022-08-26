<?php

if (!function_exists('ESFF_GET')) {

    function ESFF_GET($key, $array = false) {

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
            return ESFF_GET($name);
        }

        if (is_array($array)) {
            if (isset($array[$name])) {
                return sanitize_text_field(wp_unslash($array[$name]));
            }
            return false;
        }

        if (is_object($array)) {
            if (isset($array->$name)) {
                return sanitize_text_field(wp_unslash($array->$name));
            }
            return false;
        }

        return false;
    }

}

?>