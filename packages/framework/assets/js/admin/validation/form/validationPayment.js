import Register from '../../../common/utils/Register';

export default function gopayPaymentMethodValidator(_$container) {
    window.$('form[name="payment_form"]').jsFormValidator({
        callbacks: {
            validateGopayPaymentMethod: () => {
                // JS validation is not necessary
            },
        },
    });
}

new Register().registerCallback(gopayPaymentMethodValidator, 'gopayPaymentMethodValidator');
