<?php
namespace ImageStamp\Filters;

class GetImageUrl {

    /**
     * Filter called
     * 
     * @param array $image
     * @param int $attachment_id
     * @param string|int[] $size
     * @param bool $icon
     * 
     * @return array
     */
    public function handle($image, $attachment_id, $size, $icon) {
        global $imagestamp;
        if($imagestamp->get_settings()["image"] != $attachment_id) {
            $watermark = $imagestamp->fetcher->get_attachment_image_watermark_url(get_post($attachment_id), $size);
    
            if($watermark) {
                $image[0] = $watermark;
            }
        }
        
        return $image;
    }

}