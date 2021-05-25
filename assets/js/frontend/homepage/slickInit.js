import 'slick-carousel';
import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

export default function slickInit () {
    const $hpSlider = $('#js-slider-homepage');
    const $hpSliderThumbnails = $('#js-slider-homepage-thumbnails');

    $hpSlider.not('.slick-initialized').slick({
        dots: false,
        arrows: false,
        fade: false,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        asNavFor: $hpSliderThumbnails,
        responsive: [
            {
                breakpoint: Responsive.VL,
                settings: {
                    dots: true
                }
            },
            {
                breakpoint: Responsive.LG,
                settings: {
                    variableWidth: true,
                    dots: true,
                    infinite: true,
                    centerMode: true
                }
            }
        ]
    });

    $hpSliderThumbnails.not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        asNavFor: $hpSlider,
        dots: true,
        focusOnSelect: true
    });

    function mobileSlider () {
        const sliderClass = $('.js-slider-drawable-list-mobile');
        if (window.innerWidth <= Responsive.VL) {
            sliderClass.not('.slick-initialized').slick({
                dots: false,
                arrows: false,
                infinite: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                variableWidth: true
            });
        } else {
            if (sliderClass.hasClass('slick-initialized')) {
                sliderClass.slick('unslick');
            }
        }
    }

    mobileSlider();

    $(window).resize(function (e) {
        mobileSlider();
    });

    // slick-active-current - slick carousel always gives slick-active to asNavFor element,
    // so we must give our special active class.
    $hpSliderThumbnails.find('.slick-slide').removeClass('slick-active-current');
    $hpSliderThumbnails.find('.slick-slide').eq(0).addClass('slick-active-current');

    // On before slide change match active thumbnail to current slide
    $hpSlider.on('afterChange', function (event, slick, currentSlide, nextSlide) {
        $hpSliderThumbnails.find('.slick-slide').removeClass('slick-active-current');
        $hpSliderThumbnails.find('.slick-slide').eq(currentSlide).addClass('slick-active-current');
    });
}

(new Register()).registerCallback(slickInit, 'slickInit');
