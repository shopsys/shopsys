(function ($) {
    /* eslint-disable no-new */
    new MiniLazyload({
        rootMargin: '500px',
        threshold: 0.5,
        placeholder: '/assets/frontend/images/noimage.png'
    }, '', MiniLazyload.IGNORE_NATIVE_LAZYLOAD);

    Shopsys = window.Shopsys || {};
    Shopsys.lazyLoadCall = Shopsys.lazyLoadCall || {};

    Shopsys.lazyLoadCall.inContainer = function (container) {
        $(container).find('[loading=lazy]').each(function () {
            $(this).attr('src', $(this).data('src')).addClass('loaded');
        });
    };

    Shopsys.register.registerCallback(Shopsys.lazyLoadCall.inContainer);
})(jQuery);
