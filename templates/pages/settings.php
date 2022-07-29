<div id="imagestamper">
    <div class="header">
        <h2><?php echo __("Image Stamp Settings", "image-stamp"); ?></h2>
        <button type="button" class="button header-save">Save Changes</button>
    </div>
    <div class="wrap">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <h2><?php echo __("Watermark Settings", "image-stamp"); ?></h2>
                    <form action="admin-post.php" id="imagestamp-settings" method="POST">
                        <input type="hidden" name="action" value="imagestamp_save_settings" />
                        <?php $action->get_form(true); ?>
                        <?php echo $remove_watermark_action->get_nonce_field("_watermark_nonce"); ?>
                        <input type="hidden" name="watermark-image" value="<?php echo $image ? esc_attr($image) : ''; ?>" />
    
                        <?php # Text ?>
                        <div class="form-field">
                            <label for="watermark-text"><?php echo __("Watermark Text", "image-stamp"); ?></label>
                            <input type="text" class="form-control" name="watermark-text" placeholder="<?php echo esc_attr(get_bloginfo('title')); ?>" value="<?php echo esc_attr($text); ?>" />
                        </div>
                        <?php # Image ?>
                        <div class="form-field">
                            <label for="watermark-image"><?php echo __("Watermark Image", "image-stamp"); ?></label>
                            <div>
                                <img src="<?php echo $image ? wp_get_attachment_image_url($image, "original", false) : ''; ?>" class="watermark-preview" style="display: <?php echo $image ? "block" : "none"; ?>;" alt="" />
                                <div class="buttons">
                                    <button type="button" class="button attach-watermark-image">Attach Image</button>
                                    <?php if ($image != false) : ?>
                                        <button type="button" class="button danger remove-watermark-image">Remove Image</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php # Image Opacity ?>
                        <div class="form-field">
                            <label for="watermark-opacity"><?php echo __("Watermark Opacity", "image-stamp"); ?></label>
                            <div class="opacity-slider"></div>
                            <input type="text" id="watermark-opacity-value" placeholder="100" value="<?php echo esc_attr($opacity * 100); ?>%" />
                        </div>
                        <?php # Image Position ?>
                        <div class="form-field">
                            <label for="watermark-position"><?php echo __("Watermark Position", "image-stamp"); ?></label>
                            <select id="watermark-position" name="watermark-position" class="form-control">
                                <option value="top-left"<?php echo $position == "top-left" ? " checked" : ""; ?>><?php echo __("Top Left", "image-stamp"); ?></option>
                                <option value="top-center"<?php echo $position == "top-center" ? " checked" : ""; ?>><?php echo __("Top Center", "image-stamp"); ?></option>
                                <option value="top-right"<?php echo $position == "top-right" ? " checked" : ""; ?>><?php echo __("Top Right", "image-stamp"); ?></option>
                                <option value="left"<?php echo $position == "left" ? " checked" : ""; ?>><?php echo __("Left", "image-stamp"); ?></option>
                                <option value="center"<?php echo $position == "center" ? " checked" : ""; ?>><?php echo __("Center", "image-stamp"); ?></option>
                                <option value="right"<?php echo $position == "right" ? " checked" : ""; ?>><?php echo __("Right", "image-stamp"); ?></option>
                                <option value="bottom-left"<?php echo $position == "bottom-left" ? " checked" : ""; ?>><?php echo __("Bottom Left", "image-stamp"); ?></option>
                                <option value="bottom-center"<?php echo $position == "bottom-center" ? " checked" : ""; ?>><?php echo __("Bottom Center", "image-stamp"); ?></option>
                                <option value="bottom-right"<?php echo $position == "bottom-right" ? " checked" : ""; ?>><?php echo __("Bottom Right", "image-stamp"); ?></option>
                            </select>
                        </div>
    
                        <?php # Save ?>
                        <div class="form-field">
                            <label></label>
                            <button type="submit" class="button save">Save Changes</button>
                        </div>
    
                    </form>
                </div>
                <div id="poststuff" class="col-6">
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="hndle ui-sortable-handle is-non-sortable"><?php echo __("Watermark Preview", "image-stamp"); ?></h2>
                        </div>
                        <div class="inside">
                            <div class="preview-container">
                                <img src="<?php echo esc_url(IMAGE_STAMP_URL . "/assets/img/butterfly.jpg"); ?>" alt="" class="bg-preview" />
                            </div>
                            <small class="legal">Preview image supplied by Pixabay.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>