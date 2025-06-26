($ => {
    const Shopsys = window.Shopsys || {};
    Shopsys.payment = Shopsys.payment || {};

    Shopsys.payment.init = () => {
        const $paymentType = $('.js-payment-type');

        const onPaymentChange = () => {
            const selectedType = $paymentType.val();
            const $goPayPaymentMethodDiv = $('.js-payment-gopay-payment-method');

            if (selectedType === 'goPay') {
                $goPayPaymentMethodDiv.show();
            } else {
                $goPayPaymentMethodDiv.hide();
            }
        };

        $paymentType.on('change', onPaymentChange);
        $paymentType.change();
    };

    $(document).ready(() => {
        Shopsys.payment.init();
    });
})(jQuery);
