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
        const $furnitureCatsCarousel = $('.js-furniture-cats-carousel');

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
                prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>',
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
                        slidesToShow: 3
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
                prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>',
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

            preventClickWhenSliding($galleryCarouselCols);
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
                prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>',
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

            preventClickWhenSliding($productsCarousel);
        }

        // Common product grid carousel
        if ($furnitureCatsCarousel.length) {
            $furnitureCatsCarousel.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: true,
                infinite: true,
                swipeToSlide: true,
                variableWidth: true,
                speed: 500,
                touchMove: false,
                prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>',
            });
        }

        function preventClickWhenSliding (carousel) {
            $(carousel).on('beforeChange', function () {
                $(carousel).addClass('is-not-clickable');
            });

            $(carousel).on('afterChange', function () {
                $(carousel).removeClass('is-not-clickable');
            });
        }
    }
}

(new Register()).registerCallback(SlickCarousel.init, 'SlickCarousel.init');
