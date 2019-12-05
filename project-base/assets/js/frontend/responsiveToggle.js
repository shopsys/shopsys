import $ from 'jquery';
import Responsive from './responsive';
import Timeout from '../copyFromFw/components/timeout';
import Register from '../copyFromFw/register';

/*
 1. add to button class 'js-responsive-toggle'
 2. set data-element to ID of target element to show/hide only on non desktop version
 3. set hide-on-click-out to true, if it should hide after click on page or different element
 4. switch to desktop version reset visibility of data-element from 2.
*/

const activeButtonClass = 'active';

export default class ResponsiveToggle {

    constructor ($button, $elementToHide, hideOnClickOut) {
        this.$button = $button;
        this.hideOnClickOut = hideOnClickOut;
        this.$elementToHide = $elementToHide;
        this.defaultActive = this.isActive();

        ResponsiveToggle.instanceCoutner = (ResponsiveToggle.instanceCoutner || 0) + 1;

        const _this = this;
        this.$button.click(function () {
            _this.toggle(!_this.isActive());
            return false;
        });

        if (this.hideOnClickOut) {
            $(document).click((event) => this.onClickOut(event, _this));
        }

        $(window).resize(function () {
            Timeout.setTimeoutAndClearPrevious('ResponsiveToggle.window.resize.' + ResponsiveToggle.instanceCoutner, () => _this.onWindowResize(_this), 200);
        });
    }

    isActive () {
        return this.$button.hasClass(activeButtonClass);
    }

    toggle (show) {
        this.$button.toggleClass(activeButtonClass, show);
        this.$elementToHide.slideToggle(show);
    }

    onClickOut (event, responsiveToggle) {
        if (
            responsiveToggle.isActive()
            && $(event.target).closest(responsiveToggle.$button).length === 0
            && $(event.target).closest(responsiveToggle.$elementToHide).length === 0
        ) {
            responsiveToggle.toggle(false);
        }
    }

    onWindowResize (responsiveToggle) {
        if (Responsive.isDesktopVersion()) {
            if (responsiveToggle.$elementToHide.is(':animated')) {
                responsiveToggle.$elementToHide.stop(true, true);
            }
            responsiveToggle.$button.toggleClass(activeButtonClass, responsiveToggle.defaultActive);
            responsiveToggle.$elementToHide.css('display', '');
        }
    }

    static init () {
        $('.js-responsive-toggle').each(function () {
            const $button = $(this);
            const $elementToHide = $('#' + $button.data('element'));
            const hideOnClickOut = $button.data('hide-on-click-out');

            // eslint-disable-next-line no-new
            new ResponsiveToggle($button, $elementToHide, hideOnClickOut);
        });
    };
}

(new Register()).registerCallback(ResponsiveToggle.init);
