<div class="wrap">
    <h2><?php echo __("Image Stamp Settings", "image-stamp"); ?></h2>

    <div id="poststuff">
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle is-non-sortable"><?php echo __("Watermark Settings", "image-stamp"); ?></h2>
            </div>
            <div class="inside">
                <form action="admin-post.php" method="POST">
                    <input type="hidden" name="action" value="imagestamp_save_settings" />
                    <?php $action->get_form(true); ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>Watermark Text</th>
                                <td>
                                    <input type="text" name="watermark-text" placeholder="<?php echo esc_attr(get_bloginfo('title')); ?>" value="<?php echo esc_attr($text); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th>Watermark Opacity</th>
                                <td>
                                    <input type="range" name="watermark-opacity" min="0" max="100" value="<?php echo esc_attr($opacity * 100); ?>" id="watermark-opacity-slider" />
                                    <input type="text" id="watermark-opacity-value" placeholder="100" value="<?php echo esc_attr($opacity * 100); ?>%" />
                                </td>
                            </tr>
                            <tr>
                                <th>Watermark Angle</th>
                                <td>
                                    <input type="range" name="watermark-angle" min="0" max="360" value="<?php echo $angle; ?>" id="watermark-angle-slider" />
                                    <input type="text" id="watermark-angle-value" placeholder="0°" value="<?php echo $angle; ?>°" />
                                </td>
                            </tr>
                            <tr>
                                <th>Watermark Position</th>
                                <td>
                                    <fieldset>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="top-left" <?php echo $position == "top-left" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="top-center" <?php echo $position == "top-center" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="top-right" <?php echo $position == "top-right" ? "checked" : ""; ?>/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="left" <?php echo $position == "left" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="center" <?php echo $position == "center" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="right" <?php echo $position == "right" ? "checked" : ""; ?>/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="bottom-left" <?php echo $position == "bottom-left" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="bottom-center" <?php echo $position == "bottom-center" ? "checked" : ""; ?>/>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="watermark-position" value="bottom-right" <?php echo $position == "bottom-right" ? "checked" : ""; ?>/>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <button type="submit" class="button">Save Changes</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>