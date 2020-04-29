import Register from 'framework/common/utils/Register';
// import PaymentTransportChooser from './PaymentTransportChooser.js';

export default class GoPaySelection {

    static init ($container) {
        const $goPayBankSwiftInputs = $container.filterAllNodes('.js-order-gopay-bank-swift-input');
        $goPayBankSwiftInputs.on('change', GoPaySelection.onGoPayBankSwiftChange);
        // $goPayBankSwiftInputs.on('change', GoPaySelection.updateContinueButton);
    };

    static onGoPayBankSwiftChange (event) {
        const isChecked = $(event.currentTarget).prop('checked');
        const checkedSwift = $(event.currentTarget).data('swift');

        if (isChecked) {
            $('.js-order-gopay-bank-swift-input:checked').each(function (i, checkbox) {
                const $goPayBankSwift = $(this);
                const $checkbox = $(checkbox);
                const swift = $checkbox.data('swift');
                if (swift !== checkedSwift) {
                    $checkbox.prop('checked', false);
                    $goPayBankSwift.closest('label.box-chooser__item').removeClass('box-chooser__item--active');
                }
            });

            const $goPayInput = $('.js-gopay-bank-transfer-input');
            $goPayInput.prop('checked', true);
            $goPayInput.change();

            $('.js-order-payment-input:not(.js-gopay-bank-transfer-input)').each(function () {
                $(this).prop('checked', false);
            });

            $(event.currentTarget).closest('label.box-chooser__item').addClass('box-chooser__item--active');
        } else {
            $(event.currentTarget).closest('label.box-chooser__item').removeClass('box-chooser__item--active');
        }

        // PaymentTransportChooser.updateTransports();
    };

}

(new Register()).registerCallback(GoPaySelection.init);
