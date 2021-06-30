/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

export default class SlickCarousel {
    static init () {
        function mobileSlider () {
            const $mainImageCarousel = $('.js-image-carousel');

            if ($mainImageCarousel.length && window.innerWidth <= Responsive.LG) {
                $mainImageCarousel.not('.slick-initialized').slick({
                    fade: false,
                    autoplay: false,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: false,
                    arrows: true,
                    dots: false,
                    prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                    nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>'
                });
            } else {
                if ($mainImageCarousel.hasClass('slick-initialized')) {
                    $mainImageCarousel.slick('unslick');
                }
            }
        }

        mobileSlider();

        $(window).resize(function () {
            mobileSlider();
        });
    }
}

(new Register()).registerCallback(SlickCarousel.init, 'SlickCarousel.init');
