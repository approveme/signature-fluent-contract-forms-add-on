<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!class_exists('esigFluentFilters')):

    class esigFluentFilters {

        protected static $instance = null;

        private function __construct() {
            
            // render gravity shortcode to replace with value. 
           add_filter("esig_document_clone_render_content", array($this, "document_content_render"), 10, 4);

        }

        /**
         *  It replaces the shortcode content with agreement content. After copying an agreement it will be
         *  replaced with new new content with shortcode content. 
         *  @since 1.5.6.8
         *  @param string $content | Content of document to replace
         *  @return string $content 
         */

        public function replace_shortcode($content,$args) {

            if (false === strpos($content, '[')) {
                return $content;
            }

            
            $tagnames = array("esigfluent");
           
            $content = do_shortcodes_in_html_tags($content, true, $tagnames);
            
            $pattern = get_shortcode_regex($tagnames);
            ESIG_GF_VALUE::setEntryID(esigget("entryId", $args));
            $content = preg_replace_callback("/$pattern/", 'do_shortcode_tag', $content);
            
            // Always restore square braces so we don't break things like <!--[if IE ]>
            $content = unescape_invalid_shortcodes($content);

            return $content;
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
