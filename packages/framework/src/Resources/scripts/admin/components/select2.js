(function ($) {
    Shopsys = window.Shopsys || {};
    Shopsys.select2 = Shopsys.flashMessage || {};

    Shopsys.select2.init = function ($container) {
        $container.filterAllNodes('select:not(.no-init)').select2({
            minimumResultsForSearch: 5,
            width: 'computedstyle'
        });
    };

    Shopsys.register.registerCallback(Shopsys.select2.init);

})(jQuery);
