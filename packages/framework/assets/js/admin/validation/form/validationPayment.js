import Register from '../../../common/utils/Register';

export default function gopayPaymentMethodValidator ($container) {
    window.$('form[name="payment_form"]').jsFormValidator({
        callbacks: {
            validateGopayPaymentMethod: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(gopayPaymentMethodValidator, 'gopayPaymentMethodValidator');
