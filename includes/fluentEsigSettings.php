<?php
namespace esigFluentIntegration;

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

        $choices[] = array(
            'name' => "Please select a stand alone document",
            'value' => "",
        );


        foreach ($sad_pages as $page) {
            $document_status = $api->document->getStatus($page->document_id);

            if ($document_status != 'trash') {
                if ('publish' === get_post_status($page->page_id)) {
                    $choices[] = array(
                        'name' => get_the_title($page->page_id),
                        'value' => $page->page_id,
                    );
                }
            }
        }

        return $choices;
    }
}