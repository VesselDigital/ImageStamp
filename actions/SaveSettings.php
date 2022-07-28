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

        $opacity = 1;
        if(isset($this->data["watermark-opacity"]) && is_numeric($this->data["watermark-opacity"])) {
            $opacity_data = (float) $this->data["watermark-opacity"];
            if( $opacity_data >= 0 && $opacity_data <= 100) {
                $opacity = $opacity_data / 100;
            }
        }

        $angle = 0;
        if(isset($this->data["watermark-angle"]) && is_numeric($this->data["watermark-angle"])) {
            $angle_data = $this->data["watermark-angle"];
            if( $angle_data >= -360 && $opacity_data <= 360) {
                $angle = $angle_data;
            }
        }
        update_option("imagestamp_watermark_position", $position);
        update_option("imagestamp_watermark_text", $watermark);
        update_option("imagestamp_watermark_opacity", $opacity);
        update_option("imagestamp_watermark_angle", $angle);

        return $this->redirect( $this->base_url . '&message=success' );
    }
}
