import Register from 'framework/common/utils/Register';
import Responsive from '../utils/Responsive';

(function ($) {

    /* eslint-disable no-use-before-define */
    const Shopsys = Shopsys || {};
    Shopsys.filterPosition = Shopsys.filterPosition || {};

    const productFilterOpenerSelector = '.js-product-filter-opener';
    const productListPanelSelector = '.js-product-list-panel';
    const windowWidthLimit = Responsive.VL;

    Shopsys.filterPosition.init = function ($container) {
        if ($container.find('.js-product-filter-opener').length > 0) {
            $(productFilterOpenerSelector).click(function (e) {
                e.preventDefault();

                $(productListPanelSelector).toggleClass('active');
                Shopsys.filterPosition.setFilterPosition();
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
