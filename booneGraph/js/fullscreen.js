function launchFullScreen(element) {
    if (element.requestFullScreen) {
        element.requestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
    } else if (element.webkitRequestFullScreen) {
        element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
    }
}

function cancelFullScreen() {
    if (document.cancelFullScreen) {
        document.cancelFullScreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitCancelFullScreen) {
        document.webkitCancelFullScreen();
    }
}

function isFullscreen() {
    return !!(document.fullscreenElement || document.mozFullScreenElement
            || document.webkitFullscreenElement);
}

(function($) {
    $.fn.fullscreen = function(target_element, callback) {
        $(this).click(function() {
            if (isFullscreen())
                cancelFullScreen();
            else
                launchFullScreen(target_element[0]);
        });
        
        if (callback != undefined)
            $(window).resize(callback);
    };
})(jQuery);
