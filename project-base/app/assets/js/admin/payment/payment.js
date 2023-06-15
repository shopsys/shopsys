import constant from '../utils/constant';

(function ($) {

    const Shopsys = window.Shopsys || {};
    Shopsys.payment = Shopsys.payment || {};

    Shopsys.payment.init = function () {
        const $paymentType = $('.js-payment-type');

        const onPaymentChange = function () {
            const selectedType = $paymentType.val();
            const $goPayPaymentMethodFormLine = $('.js-payment-gopay-payment-method').closest('.form-line');

            if (selectedType === constant('\\App\\Model\\Payment\\Payment::TYPE_GOPAY')) {
                $goPayPaymentMethodFormLine.show();
            } else {
                $goPayPaymentMethodFormLine.hide();
            }
        };

        $paymentType.on('change', onPaymentChange);
        $paymentType.change();
    };

    $(document).ready(function () {
        Shopsys.payment.init();
    });

})(jQuery);
