import Register from '../../common/utils/Register';

class ExecuteRefund {
    static init ($container) {
        $container.filterAllNodes('.js-send-refund').on('click', function (event) {
            $('.' + $(this).data('execute-class')).val(true);
        });

        $container.filterAllNodes('.js-refunded-amount-edit').on('click', function (event) {
            $container.filterAllNodes('.js-refunded-amount').toggleClass('hide');
        });

    }
}

(new Register()).registerCallback(ExecuteRefund.init, 'ExecuteRefund.init');
