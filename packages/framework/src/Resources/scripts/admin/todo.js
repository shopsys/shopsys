$(document).ready(function () {

    $('.js-web-header-menu-toggle-button').on('click', function (e) {
        $('body').toggleClass('js-side-menu-open');
        $(this).toggleClass('active');
        e.preventDefault();
    });

    $('.js-products-picker-button-add').click( function($container) {
        setTimeout( function() {
            $('.mfp-iframe-scaler').addClass('mfp-iframe-scaler--responsive');
        }, 2000);
    });

    $('#js-order-item-add-product').click( function($container) {
        setTimeout( function() {
            $('.mfp-iframe-scaler').addClass('mfp-iframe-scaler--responsive');
        }, 2000);
    });

});
