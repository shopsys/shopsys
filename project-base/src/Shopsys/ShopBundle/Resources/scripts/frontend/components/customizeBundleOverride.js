(function ($) {
    Shopsys = window.Shopsys || {};
    Shopsys.validation = Shopsys.validation || {};

    Shopsys.validation.showFormErrorsWindow = function (container) {
        var $formattedFormErrors = Shopsys.validation.getFormattedFormErrors(container);
        var $window = $('#js-window');

        var $errorListHtml = '<div class="text-left">'
            + Shopsys.translator.trans('Please check the entered values.<br>')
            + $formattedFormErrors[0].outerHTML
            + '</div>';

        if ($window.length === 0) {
            Shopsys.window({
                errors: $errorListHtml

            });
        } else {
            $window.filterAllNodes('.js-window-validation-errors')
                .html($errorListHtml)
                .removeClass('display-none');
        }

    };
})(jQuery);
