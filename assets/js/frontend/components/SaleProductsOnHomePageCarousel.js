/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Register from 'framework/common/utils/Register';
import Ajax from 'framework/common/utils/Ajax';

export default class SaleProductsCarousel {

    static init ($container) {
        const config = {};

        const $saleProductsCarouselWrapper = $container.filterAllNodes('.js-sale-products-slick-carousel');
        const $productsCarousel = $saleProductsCarouselWrapper.find('.js-product-list');

        config.itemsCount = $productsCarousel.find('.js-list-products-item').length;
        config.currentPage = 1;
        config.saleProductsUrl = $saleProductsCarouselWrapper.data('sale-products-url');
        config.pageItemsCount = $saleProductsCarouselWrapper.data('page-items-count');

        if ($productsCarousel.length) {
            $productsCarousel.not('.slick-initialized').slick({
                slidesToShow: 5,
                slidesToScroll: 1,
                dots: false,
                infinite: false,
                swipeToSlide: true,
                variableWidth: true,
                speed: 300,
                responsive: [{
                    breakpoint: 1500,
                    settings: {
                        slidesToShow: 5
                    }
                },
                {
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 1100,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1
                    }
                }]
            });

            $productsCarousel.on('afterChange', function (event, slick, currentSlide) {

                const currentSlidesToShow = slick.slickGetOption('slidesToShow');
                const loadLimit = config.itemsCount - currentSlidesToShow;

                if (loadLimit === currentSlide) {

                    config.itemsCount = config.itemsCount + config.pageItemsCount;
                    config.currentPage++;
                    SaleProductsCarousel.loadNextContent(config.currentPage, config.saleProductsUrl, $productsCarousel);
                }
            });
        }
    }

    static loadNextContent (page, url, $productsCarousel) {
        const requestData = {};
        requestData['page'] = page;

        Ajax.ajax({
            type: 'GET',
            url: url,
            data: requestData,
            success: function (data) {
                const $response = $($.parseHTML(data));
                $response.find('.js-list-products-item').each((index, element) => {
                    (new Register()).registerNewContent($(element));
                    $productsCarousel.slick('slickAdd', element);
                });
            }
        });
    }
}

(new Register()).registerCallback(SaleProductsCarousel.init, 'SaleProductsCarousel.init');
