<?php

namespace ImageStamp\Helpers;

class Stamper
{

    /**
     * Absolute path to font
     * 
     * @var string
     */
    private $font_path;

    /**
     * Valid positions
     * 
     * @var array
     */
    public $valid_positions = [
        "top-left",
        "top-center",
        "top-right",
        "left",
        "center",
        "right",
        "bottom-left",
        "bottom-center",
        "bottom-right",
    ];


    /**
     * New stamper instance
     * 
     * @return void
     */
    public function __construct()
    {
        $this->font_path = IMAGE_STAMP_PATH . "/assets/font.ttf";
    }

    /**
     * Delete an image
     * 
     * @param \WP_Post $post
     * 
     * @return boolean
     */
    public function delete(\WP_Post $post)
    {
        $sizes = get_intermediate_image_sizes();
        $img_urls = [];
        foreach ($sizes as $size) {
            $path = get_post_meta($post->ID, "_imagestamp_crop_" . $size, true);
            $path = $this->_url_to_path($path);
            if ($path && file_exists($path)) {
                if (!unlink($path)) {
                    return false;
                }
            }
            delete_post_meta($post->ID, "_imagestamp_crop_" . $size);
        }
        if (count($img_urls) < 1) {
            return true;
        }

        return true;
    }

    /**
     * Watermark an image
     * 
     * @param \WP_Post $post
     * 
     * @return boolean
     */
    public function stamp(\WP_Post $post)
    {
        global $imagestamp;

        $sizes = get_intermediate_image_sizes();
        $paths = [];
        foreach ($sizes as $size) {
            $path = $this->_url_to_path($imagestamp->fetcher->get_nonwatermark_attachment_image_src($post->ID, $size));
            $paths[$size] = $path;
        }
        // No sizes, so cancel out
        if (count($paths) < 1) {
            return false;
        }

        global $imagestamp;
        $settings = $imagestamp->get_settings();


        $ext = pathinfo($paths[$sizes[0]], PATHINFO_EXTENSION);

        foreach ($paths as $size => $path) {
            $cache_fname = md5($path);
            $output_path = IMAGE_STAMP_UPLOADS_CACHE . "/" . $cache_fname . "." .  $ext;
            $status = true;

            // Check if this crop isn't already set under another?
            if (!file_exists($output_path)) {
                // Check mime type to generate image.
                $status = $this->_generate_image(
                    $path,
                    $output_path,
                    $post->post_mime_type,
                    $settings
                );
            }

            if (!$status) {
                return false;
            }

            // Add crop meta to attachment with url and not path
            update_post_meta($post->ID, "_imagestamp_crop_" . $size, $this->_path_to_url($output_path));
        }

        return true;
    }

    /**
     * Convert image url to image path
     * 
     * @param string $url
     * @return string
     */
    public function _url_to_path($url)
    {
        return str_replace(wp_upload_dir()["baseurl"], wp_upload_dir()["basedir"], $url);
    }

    /**
     * Convert image url to image path
     * 
     * @param string $url
     * @return string
     */
    public function _path_to_url($url)
    {
        return str_replace(wp_upload_dir()["basedir"], wp_upload_dir()["baseurl"], $url);
    }

    /**
     * Calculate images text position and size
     * 
     * @param resouce|\GDImage $image
     * @param string $text
     * @param string $position
     * @param $opacity
     * @param float $width
     * @param float $height
     * @return void
     */
    private function _text($image, string $text, string $position, $opacity, $width, $height)
    {
        if (!in_array($position, $this->valid_positions)) {
            return;
        }

        if(is_numeric($opacity)) { // Opacity is numeric?
            // Get the value from the percentage
            $val = (float) $opacity * 127;
            // Because 127 is transparent we need the inverse value so we take 127 from the value to inverse it.
            $alpha = 127 - $val;
        } else { 
            // Make it fully visible
            $alpha = 0;
        }

        $white = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        // Landscape
        if ($width != 0 && $height != 0) {
            if ($width > $height) {
                $mutliplier = $width / $height;
            } else { // Portrait
                $mutliplier = $height / $width;
            }
        } else {
            $mutliplier = 1;
        }

        // 16 is the base font height.
        $font_size = 16 * $mutliplier;
        $xy = $this->_xy($text, $font_size, $width, $height, $position);
        $x = $xy["x"];
        $y = $xy["y"];

        imagettftext($image, $font_size, 0, $x, $y, $white, $this->font_path, $text);
    }

    /**
     * Calculate the x and y position
     * 
     * @param string $text
     * @param float $font_size
     * @param float $width
     * @param float $height
     * @param string $position
     * 
     * @return array
     */
    private function _xy($text, $font_size, $width, $height, $position)
    {
        $x = 0;
        $y = $font_size;
        list($left, $bottom, $right,,, $top)  = imageftbbox($font_size, 0, $this->font_path, $text);

        $xCenter = $width / 2;
        $yCenter = $height / 2;

        $xOffset = ($right - $left) / 2;
        $yOffset = ($bottom - $top) / 2;

        switch ($position) {

            case "top-left":
                $x = $font_size;
                $y = $font_size + 10;
                break;
            case "top-center":
                $x = $xCenter - $xOffset;
                $y = $font_size + 10;
                break;
            case "top-right":
                $x = ($width - $right) - $font_size - 10;
                $y = $font_size + 10;
                break;

            case "left":
                $x = $font_size;
                $y = ($height / 2) - $font_size;
                break;
            case "center":
                $x = $xCenter - $xOffset;
                $y = $yCenter + $yOffset;
                break;
            case "right":
                $x = ($width - $right) - $font_size - 10;
                $y = ($height / 2) - $font_size;
                break;

            case "bottom-left":
                $x = $font_size;
                $y = ($height - 10) - $font_size;
                break;
            case "bottom-center":
                $x = $xCenter - $xOffset;
                $y = ($height - 10) - $font_size;
                break;
            case "bottom-right":
                $x = ($width - $right) - $font_size - 10;
                $y = ($height - 10) - $font_size;
                break;
        }

        return ["x" => $x, "y" => $y];
    }

    /**
     * Create watermarked image
     * 
     * @param string $input_path
     * @param string $output_path
     * @param string $mime_type
     * @param array $settings
     * 
     * @return boolean
     */
    private function _generate_image(string $input_path, string $output_path, string $mime_type, array $settings = ['position' => 'center', 'text' => 'watermark'])
    {
        $mimes = [
            "image/jpeg" => [
                "create" => "imagecreatefromjpeg",
                "save" => "imagejpeg"
            ],
            "image/png" => [
                "create" => "imagecreatefrompng",
                "save" => "imagepng"
            ],
            "image/webp" => [
                "create" => "imagecreatefromwebp",
                "save" => "imagewebp"
            ]
        ];
        if(!isset($mimes[$mime_type])) {
            return false;
        }

        $img = $mimes[$mime_type]["create"]($input_path);
        $text = $settings["text"];
        $opacity = $settings["opacity"];
        $this->_text($img, $text, $settings["position"], $opacity, imagesx($img), imagesy($img));
        if ($mimes[$mime_type]["save"]($img, $output_path)) {
            imagedestroy($img);
            return true;
        }
        return false;  
    }
}
