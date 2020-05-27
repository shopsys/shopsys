(function ($) {

    Shopsys = window.Shopsys || {};

    Shopsys.doubleFormSubmitProtection = function (event) {
        var $form = $(event.target);

        if ($form.attr('submit-protection') === 'true') {
            event.stopImmediatePropagation();
            event.preventDefault();
            return;
        }

        $form.attr('submit-protection', true);

        setTimeout(function () {
            $form.attr('submit-protection', false);
        }, 1500);
    };

})(jQuery);
