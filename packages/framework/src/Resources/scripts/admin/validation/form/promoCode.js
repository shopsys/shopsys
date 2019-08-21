(function ($) {
    $(document).ready(function () {

        var $promoCodeFormField = $('#promo_code_form_code');
        $promoCodeFormField.jsFormValidator({
            callbacks: {
                validateUniquePromoCode: function () {

                }
            }
        });

    });
})(jQuery);
