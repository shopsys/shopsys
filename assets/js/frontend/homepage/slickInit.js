import $ from 'jquery';
import 'slick-carousel';
import Register from 'framework/common/utils/register';

export default function slickInit () {
    $('#js-slider-homepage').not('.slick-initialized').slick({
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 4000
    });
}

(new Register()).registerCallback(slickInit);
