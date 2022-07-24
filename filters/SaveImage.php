<?php
namespace ImageStamp\Filters;

class SaveImage {

    /**
     * Filter called
     * 
     * @param array $post
     * @param array $attachment
     * 
     * @return array
     */
    public function handle($post, $attachment) {
        global $imagestamp;

        if(isset($attachment)) {
            // Exclude watermark checkbox is checked
            if(isset($attachment["exclude_watermark"])) {
                update_post_meta($post["ID"], "_imagestamp_exclude_watermark", "1");
                $imagestamp->stamper->delete(get_post($post["ID"]));
            } else {
                delete_post_meta($post["ID"], "_imagestamp_exclude_watermark");
                $imagestamp->stamper->stamp(get_post($post["ID"]));
            }
        }

        return $post;
    }

}