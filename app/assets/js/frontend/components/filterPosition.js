import Register from 'framework/common/utils/Register';
import Responsive from '../utils/Responsive';
import Overlay from '../utils/overlay';

(function ($) {

    /* eslint-disable no-use-before-define */
    const Shopsys = Shopsys || {};
    Shopsys.filterPosition = Shopsys.filterPosition || {};

    const productFilterOpenerSelector = '.js-product-filter-open-button';
    const productListPanelSelector = '.js-product-list-panel';
    const windowWidthLimit = Responsive.VL;

    Shopsys.filterPosition.init = function ($container) {
        const overlay = new Overlay(true);

        if ($container.find('.js-product-filter-open-button').length > 0) {
            $(productFilterOpenerSelector).click(function (e) {
                e.preventDefault();

                $(productListPanelSelector).toggleClass('active');
                $('.js-product-list-with-filter').toggleClass('active');
                Shopsys.filterPosition.setFilterPosition();

                if ($('#js-web-overlay').length > 0) {
                    overlay.hideOverlay();
                } else {
                    overlay.showOverlay();
                }
            });

            $(window).resize(function () {
                Shopsys.filterPosition.setFilterPosition();
            });
        }
    };

    Shopsys.filterPosition.setFilterPosition = function () {
        let newPosition = 0;
        const position = $('.js-product-list').position();

        if ($(window).width() < windowWidthLimit && position !== undefined && position.top !== undefined) {
            newPosition = position.top;
        }

        $(productListPanelSelector).css({ 'top': newPosition });
    };

    new Register().registerCallback(Shopsys.filterPosition.init);

})(jQuery);
