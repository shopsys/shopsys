/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Register from 'framework/common/utils/Register';
import Ajax from 'framework/common/utils/Ajax';
import Gtm from '../../gtm';

export default class SimilarProductsCarousel {

    static init ($container) {
        const config = {};

        const $similarProductsCarouselWrapper = $container.filterAllNodes('.js-similar-products-slick-carousel');
        const $productsCarousel = $similarProductsCarouselWrapper.find('.js-product-list');

        config.itemsCount = $productsCarousel.find('.js-list-products-item').length;
        config.currentPage = 1;
        config.productId = $similarProductsCarouselWrapper.data('product-id');
        config.similarProductsUrl = $similarProductsCarouselWrapper.data('similar-products-url');

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

                    config.itemsCount = config.itemsCount + 8;// Controller/Front/ProductController::SIMILAR_PRODUCTS_PER_PAGE
                    config.currentPage++;
                    SimilarProductsCarousel.loadNextContent(config.productId, config.currentPage, config.similarProductsUrl, $productsCarousel);
                }
            });
        }
    }

    static loadNextContent (productId, page, url, $productsCarousel) {
        const requestData = {};
        requestData['id'] = productId;
        requestData['page'] = page;

        Ajax.ajax({
            type: 'GET',
            url: url,
            data: requestData,
            success: function (data) {
                const $wrappedData = $($.parseHTML('<div>' + data + '</div>'));
                $wrappedData.find('.js-list-products-item').each((index, element) => {
                    (new Register()).registerNewContent($(element));
                    $productsCarousel.slick('slickAdd', element);
                });
                Gtm.pushEvent($wrappedData.find('.gtm-scroll').data('gtm-event'));
            }
        });
    }
}

(new Register()).registerCallback(SimilarProductsCarousel.init, 'SimilarProductsCarousel.init');
