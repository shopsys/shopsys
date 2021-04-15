import Responsive from '../utils/Responsive';

export default class Overlay {
    static getOverlay () {
        let $overlay = $('.js-web-overlay');
        if ($overlay.length === 0) {
            $overlay = $('<div class="web__overlay js-web-overlay"></div>');
        }
        return $overlay;
    }

    static showOverlay () {
        let $overlay = Overlay.getOverlay();
        $('body').append($overlay);

        // timeout 0 to asynchronous run to fix css animation fade
        setTimeout(function () {
            $overlay.addClass('web__overlay--active');
        }, 0);
    }

    static hideOverlay () {
        let $overlay = $('.js-web-overlay');
        $overlay.removeClass('web__overlay--active');

        if ($overlay.length !== 0) {
            setTimeout(function () {
                $overlay.remove();
            }, 400);
        }
    }
}
