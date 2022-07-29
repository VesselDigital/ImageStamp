<?php
namespace ImageStamp\Actions;

class RemoveOverlayImage extends Action
{

    /**
     * The action name
     * 
     * @var string
     */
    public $action = "remove_overlay_image";

    /**
     * The base page url
     * 
     * @var string
     */
    public $base_url = "/wp-admin/options-general.php?page=image-stamp";

    /**
     * Is an ajax action?
     * 
     * @var boolean
     */
    public $is_ajax = true;

    /**
     * Handle the action
     * 
     * @return void
     */
    public function handle() {
        delete_option("imagestamp_watermark_image");
        wp_send_json_success([ "message" => "Watermark image removed"]);
        wp_die();
    }
}
