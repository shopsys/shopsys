import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class Payment {
    constructor($paymentTypeSelect) {
        const onPaymentChange = () => {
            const selectedType = $paymentTypeSelect.val();

            const $goPayPaymentMethodDiv = $('.js-payment-gopay-payment-method');
            const $bankTransferPaymentMethodDivs = $('.js-payment-bank-transfer');

            if (selectedType === 'goPay') {
                $goPayPaymentMethodDiv.show();
                $bankTransferPaymentMethodDivs.hide();
            } else if (selectedType === 'bankTransfer') {
                $goPayPaymentMethodDiv.hide();
                $bankTransferPaymentMethodDivs.show();
            } else {
                $goPayPaymentMethodDiv.hide();
                $bankTransferPaymentMethodDivs.hide();
            }
        };

        $paymentTypeSelect.on('change', onPaymentChange);
        onPaymentChange();
    }

    static init($container) {
        $container.filterAllNodes('.js-payment-type').each(function () {
            // eslint-disable-next-line no-new
            new Payment($(this));
        });
    }
}

new Register().registerCallback(Payment.init, 'Payment.init');
