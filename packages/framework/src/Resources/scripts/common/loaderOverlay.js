(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.loaderOverlay = Shopsys.loaderOverlay || {};

    Shopsys.loaderOverlay.createLoaderOverlay = function (loaderElement, loaderMessage) {
        if (loaderElement === undefined) {
            loaderElement = 'body';
        }
        if (loaderMessage === undefined) {
            loaderMessage = '';
        }

        var $loaderOverlay = $($.parseHTML(
            '<div class="in-overlay__in">'
                + '<div class="in-overlay__spinner">'
                    + '<span class="in-overlay__spinner__icon"></span>'
                    + '<span class="in-overlay__spinner__message">' + loaderMessage + '</span>'
                + '</div>'
            + '</div>'));

        if (loaderElement !== 'body') {
            $loaderOverlay.addClass('in-overlay__in--absolute');
            $loaderOverlay.find('.in-overlay__spinner').addClass('in-overlay__spinner--absolute');
        }

        $loaderOverlay.data('loaderElement', loaderElement);

        return $loaderOverlay;
    };

    Shopsys.loaderOverlay.showLoaderOverlay = function ($loaderOverlay) {
        var $loaderElement = $($loaderOverlay.data('loaderElement'));

        $loaderElement
            .addClass('in-overlay')
            .append($loaderOverlay);
    };

    Shopsys.loaderOverlay.removeLoaderOverlay = function ($loaderOverlay) {
        var $loaderElement = $($loaderOverlay.data('loaderElement'));

        $loaderOverlay.remove();

        // If multiple overlays are shown over the same element class should be removed only when no overlay is shown anymore
        if ($loaderElement.children('.in-overlay__in').length === 0) {
            $loaderElement.removeClass('in-overlay');
        }
    };

})(jQuery);
