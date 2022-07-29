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
     * Image Mime Function Maps
     * 
     * @var array
     */
    protected $mimes = [
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
                    $post->post_mime_type
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
     * @param integer $image_id
     * @param string $position
     * @param $opacity
     * @param float $width
     * @param float $height
     * @return \GDImage
     */
    private function _overlay_image($image, $image_id, string $position, $opacity, $width, $height)
    {
        $post = get_post($image_id);
        $overlay_mime_type = $post->post_mime_type;
        if(!isset($this->mimes[$overlay_mime_type])) {
            return false;
        }
        $overlay_image_path = $this->_url_to_path($post->guid);
        $overlay_image = $this->mimes[$overlay_mime_type]["create"]($overlay_image_path);

        if($overlay_image === false) {
            throw new \Error("Failed to open overlay image, please check file permissions");
        }
        
        // Resize the watermark to fit the image
        $overlay_width = $width / 2;
        $overlay_height = $height / 2;
        $overlay_image_data = $this->_resize_image($overlay_image, $overlay_width, $overlay_height);
        
        $overlay_image = $overlay_image_data["image"];
        $overlay_width = $overlay_image_data["width"];
        $overlay_height = $overlay_image_data["height"];


        if(is_numeric($opacity)) { // Opacity is numeric?
            // Get the value from the percentage
            $val = 1 - (float) $opacity;
            // Because 127 is transparent we need the inverse value so we take 127 from the value to inverse it.
            $alpha = $val * 127;
        } else { 
            // Make it fully visible
            $alpha = 0;
        }

        // Opacity of the overlay / watermark
        imagefilter($overlay_image, IMG_FILTER_COLORIZE, 0, 0, 0, $alpha);


        // Add old image to a new true color image
        $new_image = imagecreatetruecolor($width, $height);
        imagecopy($new_image, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        // Put the watermark on the image
        $overlay_pos = $this->_image_xy($overlay_width, $overlay_height, $width, $height, $position);
        imagecopy($new_image, $overlay_image, $overlay_pos["x"], $overlay_pos["y"], 0, 0, $overlay_width, $overlay_height);
        imagedestroy($overlay_image);

        return $new_image;
    }

    /**
     * Resize an image
     * 
     * @param \GDImage $image
     * @param int $new_width
     * @param int $new_height
     * @param boolean $maintain_aspect
     * @return array
     */
    private function _resize_image($image, $new_width, $new_height, $maintain_aspect = true) {
        $old_width = imagesx($image);
        $old_height = imagesy($image);

        // Maintaining aspect ratio
        if($maintain_aspect) {
            // Calculate existing ratio
            $ratio = $old_width / $old_height;
            if($new_width / $new_height > $ratio) {
                $new_width = $new_height * $ratio;
            } else {
                $new_height = $new_width / $ratio;
            }
        }

        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagealphablending( $new_image, false );
        imagesavealpha( $new_image, true );
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
        imagedestroy($image);

        return [
            "image" => $new_image,
            "width" => $new_width,
            "height" => $new_height
        ];
    }

    /**
     * Calculate the x and y position
     * 
     * @param float $overlay_width
     * @param float $overlay_height
     * @param float $image_width
     * @param float $image_height
     * @param string $position
     * 
     * @return array
     */
    private function _image_xy($overlay_width, $overlay_height, $image_width, $image_height, $position)
    {
        $x = 0;

        $left = 0;
        $bottom = $overlay_height;
        $right = $overlay_width + $overlay_height;
        $top = $overlay_width;

        $xCenter = $image_width / 2;
        $yCenter = $image_height / 2;

        $xOffset = ($right - $left) / 2;
        $yOffset = ($bottom - $top) / 2;

        $size = 24;

        switch ($position) {

            case "top-left":
                $x = $size;
                $y = $size + 10;
                break;
            case "top-center":
                $x = $xCenter - $xOffset;
                $y = $size + 10;
                break;
            case "top-right":
                $x = ($image_width - $right) - 10;
                $y = $size + 10;
                break;

            case "left":
                $x = $size;
                $y = ($image_height / 2) - $size;
                break;
            case "center":
                $x = $xCenter - $xOffset;
                $y = $yCenter + $yOffset;
                break;
            case "right":
                $x = ($image_width - $right) - 10;
                $y = ($image_height / 2) - $size;
                break;

            case "bottom-left":
                $x = $size;
                $y = ($image_height - 10) - ($overlay_height + $size);
                break;
            case "bottom-center":
                $x = $xCenter - $xOffset;
                $y = ($image_height - 10) - ($overlay_height + $size);
                break;
            case "bottom-right":
                $x = ($image_width - $right) - 10;
                $y = ($image_height - 10) - ($overlay_height + $size);
                break;
        }

        return ["x" => $x, "y" => $y];
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
            $val = 1 - (float) $opacity;
            // Because 127 is transparent we need the inverse value so we take 127 from the value to inverse it.
            $alpha = $val * 127;
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
        $xy = $this->_text_xy($text, $font_size, $width, $height, $position);
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
    private function _text_xy($text, $font_size, $width, $height, $position)
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
     * 
     * @return boolean
     */
    private function _generate_image(string $input_path, string $output_path, string $mime_type)
    {
        global $imagestamp;
        $settings = $imagestamp->get_settings();

        if(!isset($this->mimes[$mime_type])) {
            return false;
        }

        $img = $this->mimes[$mime_type]["create"]($input_path);

        if($img === false) {
            throw new \Error("Failed to open base image, please check file permissions");
        }

        $text = $settings["text"];
        $opacity = $settings["opacity"];

        if($settings["image"] != false) {
            $img = $this->_overlay_image($img, $settings["image"], $settings["position"], $opacity, imagesx($img), imagesy($img));
        } else {
            $this->_text($img, $text, $settings["position"], $opacity, imagesx($img), imagesy($img));
        }


        if ($this->mimes[$mime_type]["save"]($img, $output_path)) {
            imagedestroy($img);
            return true;
        }
        return false;  
    }
}
