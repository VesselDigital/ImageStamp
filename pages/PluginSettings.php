<?php
namespace ImageStamp\Pages;
/**
 * Average score control page class
 */

class PluginSettings extends Page
{

    /**
     * The pages title
     * 
     * @var string
     */
    public $title = "Image Stamp Settings";

    /**
     * Menu title
     * 
     * @var string
     */
    public $menu_title = "Image Stamp";

    /**
     * The pages slug
     * 
     * @var string
     */
    public $slug = "image-stamp";


    /**
     * Render the average score page
     * 
     * @return void|string
     */
    public function render()
    {
        global $imagestamp;
        $action = $imagestamp->action("save_settings");
        
        $settings = $imagestamp->get_settings();
        $position = $settings["position"];
        $text = $settings["text"];
        $opacity = $settings["opacity"];
        $angle = $settings["angle"];

        include_once IMAGE_STAMP_PATH . "/templates/pages/settings.php";
    }

}
