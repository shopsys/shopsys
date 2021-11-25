(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.blogList = Shopsys.blogList || {};

    Shopsys.register.registerCallback(function ($container) {
        $container.filterAllNodes('.js-blog-list-with-paginator').each(function () {
            var ajaxMoreLoader = new Shopsys.AjaxMoreLoader($(this), {
                buttonTextCallback: function (loadNextCount) {
                    return Shopsys.translator.transChoice(
                        '{1}Načíst další %loadNextCount% článek|[2,4]Načíst další %loadNextCount% články|[5,Inf]Načíst dalších %loadNextCount% článků',
                        loadNextCount,
                        { '%loadNextCount%': loadNextCount }
                    );
                }
            });
            ajaxMoreLoader.init();
        });
    });

    $(document).ready(function () {
        $('.js-blog-list-with-paginator').each(function () {
            var ajaxFilter = new Shopsys.blogList.AjaxFilter();
            ajaxFilter.init();
        });
    });

})(jQuery);
