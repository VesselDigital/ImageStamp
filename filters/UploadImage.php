<?php
namespace ImageStamp\Filters;

class UploadImage {

    /**
     * Filter called
     * 
     * @param array $metadata
     * @param int $attachment_id
     * @param string $context
     * 
     * @return void
     */
    public function handle($metadata, $attachment_id, $context) {
        global $imagestamp;
        $imagestamp->stamper->stamp(get_post($attachment_id));

        return $metadata;
    }

}