jQuery(document).ready(function() {

    var opacitySlider = jQuery("#watermark-opacity-slider");
    var opacityValue = jQuery("#watermark-opacity-value");


    opacitySlider.on("change", function(e) {
        if(!isNaN(parseFloat(e.target.value))) {
            var opacity = parseFloat(e.target.value);
            if(opacity >= 0 && opacity <= 100) {
                opacityValue.val(opacity.toString() + "%");
            }
        }
    });
    opacityValue.on("change", function(e) {
        var value = e.target.value.replace(/\D/g,'');
        if(!isNaN(parseFloat(value))) {
            var opacity = parseFloat(value);
            if(opacity >= 0 && opacity <= 100) {
                opacitySlider.val(opacity.toString());
            }
        }
    });

    var watermarkPreview = jQuery(".watermark-preview");
    if(watermarkPreview.length > 0) {
        watermarkPreview.on("load", function() {
            jQuery(this).show();
        })
    }
    jQuery(".attach-watermark-image").click(function(e) {

        var imagePopup = wp.media({ 
            title: 'Upload Image',
            button: {
              text: 'Use as watermark'
            },
            multiple: false
        }).open()
        .on('select', function(){
            var image = imagePopup.state().get('selection').first().toJSON();
            watermarkPreview.attr("src", image.url).attr("alt", image.alt);
            jQuery("input[name='watermark-image']").val(image.id);
            jQuery(".attach-watermark-image").text("Replace Image");
        });

        e.preventDefault();
    })

    jQuery(".remove-watermark-image").click(function(e) {

        jQuery(".button").prop("disabled", true);
        jQuery(e.target).text("Removing...");

        jQuery.ajax({
            url: "admin-ajax.php",
            method: "post",
            data: {
                action: "imagestamp_remove_overlay_image",
                _wpnonce: jQuery("input[name='_watermark_nonce']").val()
            }
        }).done(function(data) {
            jQuery(".button").prop("disabled", false);
            jQuery(e.target).remove();
            jQuery(".watermark-preview").attr("src", "").hide();
        })
        .error(function() {
            jQuery(".button").prop("disabled", false);
            jQuery(e.target).text("Remove Image");
        })

        e.preventDefault();
    })

});