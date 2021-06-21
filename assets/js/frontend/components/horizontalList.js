import Register from 'framework/common/utils/Register';
import Responsive from '../utils/Responsive';
import Timeout from 'framework/common/utils/Timeout';

(function ($) {

    const Shopsys = window.Shopsys || {};
    Shopsys.horizontalList = Shopsys.horizontalList || {};

    Shopsys.horizontalList.init = function ($container) {
        $container.filterAllNodes('.js-horizontal-list').each(function () {
            const $currentGallery = $(this);
            const galleryType = $currentGallery.data('type');
            const prevArrow = $currentGallery.filterAllNodes('.js-horizontal-list-action-prev');
            const nextArrow = $currentGallery.filterAllNodes('.js-horizontal-list-action-next');

            const types = {
                'top-products': [1, 2, 3, 4, 4],
                'in-sales': [1, 2, 3, 4, 4],
                'similar-products': [1, 2, 3, 4, 4]
            };

            const selectedType = types[galleryType];

            $currentGallery.find('.js-horizontal-list-slides').slick({
                dots: false,
                arrows: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                lazyLoad: 'ondemand',
                mobileFirst: true,
                variableWidth: true,
                infinite: false,
                prevArrow: prevArrow,
                nextArrow: nextArrow,
                responsive: [
                    {
                        breakpoint: Responsive.XS,
                        settings: {
                            slidesToShow: selectedType[0],
                            slidesToScroll: selectedType[0],
                            arrows: false,
                            variableWidth: true
                        }
                    },
                    {
                        breakpoint: Responsive.SM,
                        settings: {
                            slidesToShow: selectedType[1],
                            slidesToScroll: selectedType[1],
                            arrows: false,
                            variableWidth: true
                        }
                    },
                    {
                        breakpoint: Responsive.LG,
                        settings: {
                            slidesToShow: selectedType[2],
                            slidesToScroll: selectedType[2],
                            arrows: true,
                            variableWidth: false
                        }
                    },
                    {
                        breakpoint: Responsive.VL,
                        settings: {
                            slidesToShow: selectedType[3],
                            slidesToScroll: selectedType[3],
                            arrows: true,
                            variableWidth: false
                        }
                    },
                    {
                        breakpoint: Responsive.XL,
                        settings: {
                            slidesToShow: selectedType[4],
                            slidesToScroll: selectedType[4],
                            arrows: true,
                            variableWidth: false
                        }
                    }
                ]
            });

            $currentGallery.find('.js-horizontal-list-slides').on('breakpoint', function (event, slick, breakpoint) {
                recalculateSlickArrowVisibility(breakpoint);
            });
            recalculateSlickArrowVisibility(window.innerWidth || $(window).width());

            function recalculateSlickArrowVisibility (breakpoint) {
                switch (true) {
                    case breakpoint <= Responsive.LG:
                        break;
                    case breakpoint < Responsive.VL:
                        toggleSlickArrow(selectedType[2]);
                        break;
                    case breakpoint < Responsive.XL:
                        toggleSlickArrow(selectedType[3]);
                        break;
                    case breakpoint >= Responsive.XL:
                        toggleSlickArrow(selectedType[4]);
                        break;
                    default:
                        break;
                }
            }

            function toggleSlickArrow (minCountForShowArrows) {
                const countOfProduct = $currentGallery.data('count-of-product');

                if (countOfProduct > minCountForShowArrows) {
                    prevArrow.show();
                    nextArrow.show();
                } else {
                    prevArrow.hide();
                    nextArrow.hide();
                }
            }
        });
    };

    new Register().registerCallback(Shopsys.horizontalList.init);

    $(window).resize(function (event) {
        Timeout.setTimeoutAndClearPrevious('Shopsys.horizontalList.init', Shopsys.horizontalList.init($(event.currentTarget)), 200);
    });

})(jQuery);
