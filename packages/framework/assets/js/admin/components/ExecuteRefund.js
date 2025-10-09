import Register from '../../common/utils/Register';

class ExecuteRefund {
    static init($container) {
        $container.filterAllNodes('.js-send-refund').on('click', function (_event) {
            $(`.${$(this).data('execute-class')}`).val(true);
        });

        $container.filterAllNodes('.js-refunded-amount-edit').on('click', _event => {
            $container.filterAllNodes('.js-refunded-amount').toggleClass('d-none');
        });
    }
}

new Register().registerCallback(ExecuteRefund.init, 'ExecuteRefund.init');
