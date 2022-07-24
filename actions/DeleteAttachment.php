<?php
namespace ImageStamp\Actions;

class DeleteAttachment extends Action
{

    /**
     * The action name
     * 
     * @var string
     */
    public $action = "delete_attachment";

    /**
     * Is a native wordpress action?
     * 
     * @var boolean
     */
    public $is_native = true;

    /**
     * Handle the action
     * 
     * @param int $post_id
     * @return void
     */
    public function handle() {
        $post_id = func_get_arg(0);
        global $imagestamp;
        $excluded = get_post_meta($post_id, "_imagestamp_exclude_watermark", true);

        // Image had no crops, so just remove meta data.
        if($excluded && $excluded == "1") {
            delete_post_meta($post_id, "_imagestamp_exclude_watermark");
            return;
        }

        // Image had watermarked version!
        $imagestamp->stamper->delete(get_post($post_id));

    }
}
