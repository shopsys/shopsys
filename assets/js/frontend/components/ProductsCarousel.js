/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Register from 'framework/common/utils/Register';

export default class SlickCarousel {

    static init () {

        const $galleryCarousel = $(`.js-gallery-slick-carousel`).find('.js-gallery-wrap');
        const $galleryCarouselCols = $('.js-products-slick-carousel-cols').find('.js-product-list');
        const $productsCarousel = $('.js-products-slick-carousel').find('.js-product-list');

        // Product detail gallery carousel
        if ($galleryCarousel.length) {
            $galleryCarousel.not('.slick-initialized').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                dots: false,
                infinite: false,
                swipeToSlide: true,
                variableWidth: false,
                speed: 300,
                responsive: [{
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 1100,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2
                    }
                }]
            });
        }

        // Common product grid carousel with cols on desktop
        if ($galleryCarouselCols.length) {
            $galleryCarouselCols.not('.slick-initialized').slick({
                slidesToShow: 2,
                slidesToScroll: 1,
                dots: false,
                infinite: false,
                swipeToSlide: true,
                variableWidth: true,
                speed: 300,
                responsive: [{
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 1150,
                    settings: {
                        slidesToShow: 1
                    }
                },
                {
                    breakpoint: 300,
                    settings: {
                        slidesToShow: 1
                    }
                }]
            });
        }

        // Common product grid carousel
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
        }
    }
}

(new Register()).registerCallback(SlickCarousel.init, 'SlickCarousel.init');
