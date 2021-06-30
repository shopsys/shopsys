/**
 * Slick Carousel Component
 */

import 'slick-carousel';
import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

export default class SlickCarousel {
    static init () {
        const $mainImageCarousel = $('.js-main-image-carousel');

        if ($mainImageCarousel.length) {
            $mainImageCarousel.not('.slick-initialized').slick({
                fade: false,
                autoplay: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: false,
                arrows: false,
                dots: false,
                prevArrow: '<button type="button" data-role="none" class="slick-prev slick-arrow" aria-label="Předchozí" role="button"></button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next slick-arrow" aria-label="Další" role="button"></button>',
                responsive: [{
                    breakpoint: Responsive.LG,
                    settings: {
                        arrows: true
                    }
                }]
            });
        }
    }
}

(new Register()).registerCallback(SlickCarousel.init, 'SlickCarousel.init');
