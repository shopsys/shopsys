(function ($) {

    new MiniLazyload({
        rootMargin: '500px',
        threshold: 0.5,
        placeholder: '/assets/frontend/images/noimage.png'
    }, '.lazy');

    Shopsys = window.Shopsys || {};
    Shopsys.lazyLoadCall = Shopsys.lazyLoadCall || {};

    Shopsys.lazyLoadCall.inContainer = function (container) {
        $(container).find('.lazy').each(function () {
            $(this).attr('src', $(this).data('src')).addClass('loaded');
        });
    };

})(jQuery);
