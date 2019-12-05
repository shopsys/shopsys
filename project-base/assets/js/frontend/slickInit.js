import $ from 'jquery';
import Register from '../copyFromFw/register';

export default function slickInit () {
    $('#js-slider-homepage').slick({
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 4000
    });
};

(new Register()).registerCallback(slickInit);
