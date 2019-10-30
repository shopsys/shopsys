useNativeLazyload(new MiniLazyload({
    rootMargin: "500px",
    threshold: .5,
    placeholder: "placeholder.png"
}, ".lazy"));

(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.lazyLoadCall = Shopsys.lazyLoadCall || {};

    Shopsys.lazyLoadCall.inContainer = function (container) {
        $(container).find(".lazyx").each( function(){
            $(this).attr("src", $(this).data("src"));
        });
    };

})(jQuery);
