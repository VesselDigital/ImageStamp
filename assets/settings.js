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
                opacityValue.val(opacity.toString() + "%");
            }
        }
    });
    

    var angleSlider = jQuery("#watermark-angle-slider");
    var angleValue = jQuery("#watermark-angle-value");

    angleSlider.on("change", function(e) {
        if(!isNaN(parseInt(e.target.value))) {
            var angle = parseInt(e.target.value);
            if(angle >= 0 && angle <= 360) {
                angleValue.val(angle.toString() + "°");
            }
        }
    });
    angleValue.on("change", function(e) {
        var value = e.target.value.replace(/\D/g,'');
        if(!isNaN(parseInt(value))) {
            var angle = parseInt(value);
            if(angle >= 0 && angle <= 360) {
                angleSlider.val(angle.toString());
                angleValue.val(angle.toString() + "°");
            }
        }
    });

});