<?php
/**
 * Image Stamp
 *
 * Plugin Name: Image Stamp
 * Plugin URI:  https://vesseldigital.co.uk/plugins/image-stamp/
 * Description: Add a watermark to your all or some of your images without overriding the image.
 * Version:     1.0
 * Author:      VesselDigital
 * Author URI:  https://vesseldigital.co.uk/plugins
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: image-stamp
 * Requires at least: 5.8
 * Requires PHP: 7.4.1
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */


if(!class_exists("ImageStamp")) {

    define("IMAGE_STAMP_PATH", plugin_dir_path(__FILE__));
    define("IMAGE_STAMP_URL", plugin_dir_url(__FILE__));
    define("IMAGE_STAMP_UPLOADS_URL", wp_upload_dir()["baseurl"] . "/imagestamp");
    define("IMAGE_STAMP_UPLOADS", wp_upload_dir()["basedir"] . "/imagestamp");
    define("IMAGE_STAMP_UPLOADS_CACHE_URL", wp_upload_dir()["baseurl"] . "/imagestamp/cache");
    define("IMAGE_STAMP_UPLOADS_CACHE", wp_upload_dir()["basedir"] . "/imagestamp/cache");

    class ImageStamp {

        /**
         * List of all registered actions
         * 
         * @var array
         */
        public $actions = [];

        /**
         * List of all registered filters
         * 
         * @var array
         */
        public $filters = [];

        /**
         * Stamper
         * 
         * @var \ImageStamp\Helpers\Stamper
         */
        public $stamper;

        /**
         * Fetcher
         * 
         * @var \ImageStamp\Helpers\Fetcher
         */
        public $fetcher;

        /**
         * On loading of the plugin
         * 
         * @return \ImageStamp
         */
        public function init() {
            $this->autoload();

            $this->register_pages()
                ->register_actions()
                ->register_filters();

            $this->stamper = new \ImageStamp\Helpers\Stamper;
            $this->fetcher = new \ImageStamp\Helpers\Fetcher;

            add_action("admin_enqueue_scripts", [ $this, "enqueue_admin_scripts" ]);
        }


        /**
         * Autoload all classes
         * 
         * @return void
         */
        private function autoload() {
            include_once "pages/Page.php";
            include_once "pages/PluginSettings.php";

            include_once "actions/Action.php";
            include_once "actions/SaveSettings.php";
            include_once "actions/DeleteAttachment.php";

            include_once "filters/EditImage.php";
            include_once "filters/SaveImage.php";
            include_once "filters/UploadImage.php";
            include_once "filters/GetImageUrl.php";

            include_once "helpers/Stamper.php";
            include_once "helpers/Fetcher.php";
        }

        /**
         * Load and register the pages
         * 
         * @return \ImageStamp
         */
        private function register_pages() {
            new \ImageStamp\Pages\PluginSettings;

            return $this;
        }

        /**
         * Load and register the actions
         * 
         * @return \ImageStamp
         */
        private function register_actions() {
            $this->actions["save_settings"] = new \ImageStamp\Actions\SaveSettings;
            $this->actions["delete_image"] = new \ImageStamp\Actions\DeleteAttachment;

            return $this;
        }

        /**
         * Load and register the filters
         * 
         * @return \ImageStamp
         */
        private function register_filters() {

            $this->filters["edit_image"] = new \ImageStamp\Filters\EditImage;
            $this->filters["save_image"] = new \ImageStamp\Filters\SaveImage;
            $this->filters["wp_get_attachment_image_src"] = new \ImageStamp\Filters\GetImageUrl;
            $this->filters["upload_image"] = new \ImageStamp\Filters\UploadImage;

            add_filter('attachment_fields_to_edit', [ $this->filters["edit_image"], 'handle' ], null, 2);
            add_filter('attachment_fields_to_save', [ $this->filters["save_image"], 'handle' ], null, 2);
            add_filter('wp_generate_attachment_metadata', [ $this->filters["upload_image"], 'handle' ], null, 3);
            add_filter('wp_get_attachment_image_src', [ $this->filters["wp_get_attachment_image_src"], 'handle' ], null, 4);
        }

        /**
         * Get an action class
         * 
         * @return \ImageStamp\Actions\Action
         */
        public function action($name) {
            if(isset($this->actions[$name])) {
                return $this->actions[$name];
            } else {
                return false;
            }
        }

        /**
         * Get stamping settings
         * 
         * @return array
         */
        public function get_settings() {
            $position = get_option("imagestamp_watermark_position", "center");
            $opacity = get_option("imagestamp_watermark_opacity", "1");
            $image = get_option("imagestamp_watermark_image", false);
            $text = get_option("imagestamp_watermark_text", get_bloginfo("title"));

        
            return [
                "position" => $position,
                "text" => $text,
                "image" => $image,
                "opacity" => $opacity
            ];
        }

        /**
         * Load admin scripts
         * 
         * @return void
         */
        public function enqueue_admin_scripts() {
            wp_enqueue_script("imagestamp-settings", IMAGE_STAMP_URL . "/assets/settings.js", [ "jquery" ], 1);
        }

        /**
         * On plugin install
         * 
         * @return \ImageStamp
         */
        public function on_install() {
            if(!is_dir(IMAGE_STAMP_UPLOADS) && !file_exists(IMAGE_STAMP_UPLOADS) && !is_dir(IMAGE_STAMP_UPLOADS_CACHE) && !file_exists(IMAGE_STAMP_UPLOADS_CACHE)) {
                $success = wp_mkdir_p(IMAGE_STAMP_UPLOADS);
                $successtwo = wp_mkdir_p(IMAGE_STAMP_UPLOADS_CACHE);
                if(!$success && $successtwo) {
                    throw new Error("Failed to create cache directory, please check your permissions");
                }
            } elseif(file_exists(IMAGE_STAMP_UPLOADS) || file_exists(IMAGE_STAMP_UPLOADS_CACHE)) {
                throw new Error("Cache directory already exists as a file, please remove it and retry");
            }
        }

        /**
         * On plugin uninstall
         * 
         * @return \ImageStamp
         */
        public function on_uninstall() {
            if(is_dir(IMAGE_STAMP_UPLOADS_CACHE)) {
                rmdir(IMAGE_STAMP_UPLOADS_CACHE);
            }

            if(is_dir(IMAGE_STAMP_UPLOADS)) {
                rmdir(IMAGE_STAMP_UPLOADS);
            }
        }

    }


    // Init image stamp plugin
    $imagestamp = new ImageStamp();

    // Call init hooks
    add_action('init', [$imagestamp, 'init']);

    /**
     * Get watermarked crop image url
     * 
     * @param \WP_Post|int|null $post
     * @param string $crop
     * 
     * @return string
     */
    function get_attachment_image_watermark_url($post, $crop = "thumbnail") {
        global $imagestamp;
        $post = get_post($post);
        return $imagestamp->fetcher->get_attachment_image_watermark_url($post, $crop);
    }

    register_activation_hook(__FILE__, [ $imagestamp, "on_install" ]);
    register_deactivation_hook(__FILE__, [ $imagestamp, "on_uninstall" ]);
}