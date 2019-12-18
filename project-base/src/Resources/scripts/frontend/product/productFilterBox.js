(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.productFilterBox = Shopsys.productFilterBox || {};

    Shopsys.productFilterBox.init = function () {
        $('.js-product-filter-open-button').click(function () {
            $(this).toggleClass('active');
            $('.js-product-filter').toggleClass('active');
        });

        $('.js-product-filter-box-arrow').on('click', function () {
            Shopsys.productFilterBox.toggleFilterBox($(this).closest('.js-product-filter-box'));
        });
    };

    Shopsys.productFilterBox.toggleFilterBox = function ($parameterContainer) {
        var $productFilterParameterLabel = $parameterContainer.find('.js-product-filter-box-label');
        $productFilterParameterLabel.toggleClass('active');

        var parameterFilterFormId = $parameterContainer.data('product-filter-box-id');

        if ($productFilterParameterLabel.hasClass('active')) {
            $parameterContainer.find('#' + parameterFilterFormId).slideDown('fast');
        } else {
            $parameterContainer.find('#' + parameterFilterFormId).slideUp('fast');
        }
    };

    $(document).ready(function () {
        Shopsys.productFilterBox.init();
    });
})(jQuery);
