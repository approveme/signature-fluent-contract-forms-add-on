<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!class_exists('esigFluentFilters')):

    class esigFluentFilters {

        protected static $instance = null;

        /**
         * Initialize Fluent Forms document filters.
         *
         * @since 2.0.3
         * @access private
         *
         * @return void
         */
        private function __construct() {
            
            // render gravity shortcode to replace with value. 
           add_filter("esig_document_title_filter", array($this, "fluent_document_title_filter"), 10, 2);
           add_filter("esig_document_clone_render_content", array($this, "document_content_render"), 10, 4);

        }

        /**
         *  It replaces the shortcode content with agreement content. After copying an agreement it will be
         *  replaced with new new content with shortcode content. 
         *  @since 1.5.6.8
         *  @param string $content | Content of document to replace
         *  @param array  $args    Document render arguments.
         *  @return string $content 
         */

        public function replace_shortcode($content,$args) {

            if (false === strpos($content, '[')) {
                return $content;
            }

            
            $tagnames = array("esigfluent");
           
            $content = do_shortcodes_in_html_tags($content, true, $tagnames);
            
            $pattern = get_shortcode_regex($tagnames);

            //ESIG_GF_VALUE::setEntryID(esigget("entryId", $args));

            // phpcs:ignore PHPCS_SecurityAudit.BadFunctions.CallbackFunctions.WarnCallbackFunctions -- WordPress shortcode regex must dispatch through the core do_shortcode_tag callback.
            $content = preg_replace_callback("/$pattern/", 'do_shortcode_tag', $content);
            
            // Always restore square braces so we don't break things like <!--[if IE ]>
            $content = unescape_invalid_shortcodes($content);

            return $content;
        }

        /**
         * Replace Fluent Forms field placeholders in the document title.
         *
         * Supports the {{fluent-field-id-FIELD_NAME}} pattern where FIELD_NAME
         * is the Fluent Forms field element name.
         *
         * @since 2.0.3
         *
         * @param string $doc_title Document title that may contain placeholders.
         * @param int    $doc_id    Document ID.
         *
         * @return string Document title with Fluent Forms field values.
         */
        public function fluent_document_title_filter($doc_title, $doc_id) {

            $form_integration = WP_E_Sig()->document->getFormIntegration($doc_id);
            if ("fluentform" !== $form_integration) {
                return $doc_title;
            }

            preg_match_all('/{{fluent-field-id-([^}]+)}}/', $doc_title, $matches_all, PREG_SET_ORDER);

            if (empty($matches_all)) {
                return $doc_title;
            }

            $fluent_value = $this->get_submission_value($doc_id);
            if (!is_array($fluent_value)) {
                return $doc_title;
            }

            foreach ($matches_all as $match) {
                $placeholder = $match[0];
                $field_id    = !empty($match[1]) ? sanitize_text_field($match[1]) : false;

                if (!$field_id || !array_key_exists($field_id, $fluent_value)) {
                    continue;
                }

                $field_value = $this->normalize_title_value($fluent_value[$field_id]);
                if ('' === $field_value) {
                    continue;
                }

                // Security: Escape value to prevent XSS in document title.
                $doc_title = str_replace($placeholder, esc_html($field_value), $doc_title);
            }

            return $doc_title;
        }

        /**
         * Get saved Fluent Forms submission values for a document.
         *
         * @since 2.0.3
         * @access private
         *
         * @param int $doc_id Document ID.
         *
         * @return array|bool Submission values, or false when unavailable.
         */
        private function get_submission_value($doc_id) {

            $submission_value = WP_E_Sig()->meta->get($doc_id, "esig_fluent_forms_submission_value");

            if (empty($submission_value)) {
                return false;
            }

            if (is_array($submission_value)) {
                return $submission_value;
            }

            $decoded_value = json_decode($submission_value, true);

            if (!is_array($decoded_value)) {
                return false;
            }

            return $decoded_value;
        }

        /**
         * Convert a submitted field value into title-safe plain text.
         *
         * @since 2.0.3
         * @access private
         *
         * @param mixed $value Submitted field value.
         *
         * @return string Plain text field value.
         */
        private function normalize_title_value($value) {

            if (is_scalar($value)) {
                return sanitize_text_field((string) $value);
            }

            if (is_array($value)) {
                $items = array();

                foreach ($value as $item) {
                    $item = $this->normalize_title_value($item);
                    if ('' !== $item) {
                        $items[] = $item;
                    }
                }

                return implode(' ', $items);
            }

            return '';
        }

        /**
         *  Render document to replace shortcodes 
         *  @since 1.5.6.8
         *  @param string $content | content of document which will be replaced 
         *  @param int $new_doc_id | new document after cloning existing agreement. 
         *  @param string $documentType | Type of document  
         *  @param array $args | Different types of argument pass 
         *  @return {string}  | Return replace content of shortcodes.
         */

        public function document_content_render($content, $new_doc_id, $documentType, $args) {

            if ($documentType != 'stand_alone') {
                return $content;
            }

             update_option('esig_global_document_id', $new_doc_id, false);

            $isIntregration = esigget("integrationType", $args);

            if ($isIntregration != "esigfluent") {
               
                return $content;
            }
            $content = $this->replace_shortcode($content, $args);   

            delete_option('esig_global_document_id');

            return $content;
        }
        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    

    

    

    

    
endif;
