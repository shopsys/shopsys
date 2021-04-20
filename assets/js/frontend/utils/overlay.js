import Responsive from '../utils/Responsive';

export default class Overlay {
    constructor (onlyMobile = false) {
        this.onlyMobile = onlyMobile;
    }

    getOverlay () {
        let $overlay = $('.js-web-overlay');
        if ($overlay.length === 0) {
            $overlay = $('<div class="web__overlay js-web-overlay"></div>');
        }
        return $overlay;
    }

    showOverlay () {
        const _this = this;
        if (this.onlyMobile) {
            if (Responsive.isTabletVersion()) {
                _this.showOverlayInit();
            }
        } else {
            _this.showOverlayInit();
        };
    }

    hideOverlay () {
        const _this = this;
        if (this.onlyMobile) {
            if (Responsive.isTabletVersion()) {
                _this.hideOverlayInit();
            }
        } else {
            _this.hideOverlayInit();
        };
    }

    showOverlayInit () {
        const _this = this;
        let $overlay = _this.getOverlay();
        $('body').append($overlay);

        // timeout 0 to asynchronous run to fix css animation fade
        setTimeout(function () {
            $overlay.addClass('web__overlay--active');
        }, 0);
    }

    hideOverlayInit () {
        let $overlay = $('.js-web-overlay');
        $overlay.removeClass('web__overlay--active');

        if ($overlay.length !== 0) {
            setTimeout(function () {
                $overlay.remove();
            }, 400);
        }
    }
}
