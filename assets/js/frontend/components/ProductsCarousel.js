/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Register from 'framework/common/utils/Register';

export default class SlickCarousel {

    static init () {

        // Product detail gallery carousel
        $('.js-gallery-slick-carousel').find('.js-gallery-wrap').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            dots: true,
            infinite: false,
            swipeToSlide: true,
            variableWidth: true,
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
                    slidesToShow: 2
                }
            }]
        });

        // Common product grid carousel with cols on desktop
        $('.js-products-slick-carousel-cols').find('.js-product-list').slick({
            slidesToShow: 2,
            slidesToScroll: 1,
            dots: true,
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

        // Common product grid carousel
        $('.js-products-slick-carousel').find('.js-product-list').slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            dots: true,
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

(new Register()).registerCallback(SlickCarousel.init, 'SlickCarousel.init');
