<?php

namespace ImageStamp\Helpers;

class Fetcher
{

    /**
     * Get watermarked image url
     * 
     * @param \WP_Post $post
     * @param string $crop
     * 
     * @return string|false
     */
    public function get_attachment_image_watermark_url(\WP_Post $post, $crop = "thumbnail")
    {
        global $imagestamp;

        $watermark_exluded = get_post_meta($post->ID, "_imagestamp_exclude_watermark", true);
        if ($watermark_exluded == "1") {
            return false;
        }

        $sizes = get_intermediate_image_sizes();
        if (!in_array($crop, $sizes)) {
            $crop = $sizes[count($sizes) - 1];
        }

        $url = "";
        foreach ($sizes as $size) {
            if ($size == $crop) {
                $url = get_post_meta($post->ID, "_imagestamp_crop_" . $size, true);
            }
        }

        $path = $imagestamp->stamper->_path_to_url($url);
        // File has been removed but watermark still active, regenerate it.
        if(!file_exists($path)) {
            $imagestamp->stamper->stamp($post);
        }

        return $path;
    }

    /**
     * Image has watermark version available?
     * 
     * @param \WP_Post $post
     * @return boolean
     */
    public function has_watermark_available(\WP_Post $post) {
        global $imagestamp;

        $watermark_exluded = get_post_meta($post->ID, "_imagestamp_exclude_watermark", true);
        if ($watermark_exluded == "1") {
            return false;
        }

        // Get available sizes.
        $sizes = get_intermediate_image_sizes();
        foreach ($sizes as $size) {
            $url = get_post_meta($post->ID, "_imagestamp_crop_" . $size, true);
            // We have a meta for this size?
            if($url) {
                // Convert the url to path
                $path = $imagestamp->stamper->_url_to_path($url);
                // Path exists then we have a crop available!
                if(file_exists($path)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves an image to represent an attachment.
     * @package WordPress
     *
     * @param int          $attachment_id Image attachment ID.
     * @param string|int[] $size          Optional. Image size. Accepts any registered image size name, or an array of
     *                                    width and height values in pixels (in that order). Default 'thumbnail'.
     * @param bool         $icon          Optional. Whether the image should fall back to a mime type icon. Default false.
     * @return array|false {
     *     Array of image data, or boolean false if no image is available.
     *
     *     @type string $0 Image source URL.
     *     @type int    $1 Image width in pixels.
     *     @type int    $2 Image height in pixels.
     *     @type bool   $3 Whether the image is a resized image.
     * }
     */
    function get_nonwatermark_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false)
    {
        // Get a thumbnail or intermediate image if there is one.
        $image = image_downsize($attachment_id, $size);
        if (!$image) {
            $src = false;

            if ($icon) {
                $src = wp_mime_type_icon($attachment_id);

                if ($src) {
                    /** This filter is documented in wp-includes/post.php */
                    $icon_dir = apply_filters('icon_dir', ABSPATH . WPINC . '/images/media');

                    $src_file               = $icon_dir . '/' . wp_basename($src);
                    list($width, $height) = wp_getimagesize($src_file);
                }
            }

            if ($src && $width && $height) {
                $image = array($src, $width, $height, false);
            }
        }

        if(is_bool($image)) {
            return $image;
        }
        return $image[0];
    }
}
