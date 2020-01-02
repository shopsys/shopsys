import $ from 'jquery';

export default function slickInit () {
    $('#js-slider-homepage').slick({
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 4000
    });
};
