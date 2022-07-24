<?php
namespace ImageStamp\Actions;

class SaveSettings extends Action
{

    /**
     * The action name
     * 
     * @var string
     */
    public $action = "save_settings";

    /**
     * The base page url
     * 
     * @var string
     */
    public $base_url = "/wp-admin/options-general.php?page=image-stamp";

    /**
     * Handle the action
     * 
     * @return void
     */
    public function handle() {
        global $imagestamp;

        $position = "center";
        if(isset($this->data["watermark-position"]) && !in_array($this->data["watermark-position"], $imagestamp->stamper->valid_positions)) {
            return $this->redirect( $this->base_url . "&message=error&reason=Invalid watermark position" );
        } else {
            $position = esc_html($this->data["watermark-position"]);
        }

        $watermark = esc_html( get_bloginfo("name") );
        if(isset($this->data["watermark-text"]) && $this->data["watermark-text"] != "") {
            $watermark = esc_html( $this->data["watermark-text"] );
        }

        update_option("imagestamp_watermark_position", $position);
        update_option("imagestamp_watermark_text", $watermark);

        return $this->redirect( $this->base_url . '&message=success' );
    }
}
