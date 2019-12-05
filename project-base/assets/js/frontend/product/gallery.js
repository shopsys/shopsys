import $ from 'jquery';
import 'magnific-popup';
import 'slick-carousel';
import Responsive from '../responsive';
import Register from '../../copyFromFw/register';

class ProductDetail {

    static init () {
        $('.js-gallery-main-image').click(function (event) {
            var $slides = $('.js-gallery .slick-slide:not(.slick-cloned) .js-gallery-slide-link');
            $slides.filter(':first').trigger('click', event);

            return false;
        });

        const $gallery = $('.js-gallery');

        if ($gallery.length === 0) {
            return;
        }

        $gallery.magnificPopup({
            type: 'image',
            delegate: '.js-gallery-slide-link',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            }
        });

        $gallery.filterAllNodes('.js-gallery-slides').slick({
            dots: false,
            arrows: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            lazyLoad: 'ondemand',
            mobileFirst: true,
            infinite: false,
            prevArrow: $gallery.filterAllNodes('.js-gallery-prev'),
            nextArrow: $gallery.filterAllNodes('.js-gallery-next'),
            responsive: [
                {
                    breakpoint: Responsive.XS,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: Responsive.MD,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: Responsive.LG,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: Responsive.VL,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 3
                    }
                }
            ]
        });
    }
}

new Register().registerCallback(ProductDetail.init);
