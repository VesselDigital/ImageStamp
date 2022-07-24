<?php
namespace ImageStamp\Filters;

class EditImage {

    /**
     * Exluded mimes
     * 
     * @var array
     */
    protected $exclusions = [ "audio", "video" ];

    /**
     * Filter called
     * 
     * @param array $form_fields
     * @param $post
     * 
     * @return array
     */
    public function handle($form_fields, $post = null) {
        global $imagestamp;

        if(!in_array($post->post_mime_type, $this->exclusions)) {
            $checked = (get_post_meta($post->ID, "_imagestamp_exclude_watermark", true) == "1");

            $exclude_watermark_field = [
                "input" => "html",
                "label" => __("No watermark?", "image-stamp"),
                "html" => "<input type='checkbox' name='attachments[". $post->ID ."][exclude_watermark]' id='attachments-". $post->ID ."-exclude_watermark' ". ($checked ? "checked='checked'" : "") ."/>"
            ];
            
            $form_fields[] = $exclude_watermark_field;

            if($imagestamp->fetcher->has_watermark_available($post)) {
                $form_fields[] = [
                    "input" => "html",
                    "label" => __("Watermarked File", "image-stamp"),
                    "html" => "<input type='text' value='" . esc_url($imagestamp->fetcher->get_attachment_image_watermark_url($post, "original")) . "' readonly/>"
                ];
            }

        }

        return $form_fields;
    }

}