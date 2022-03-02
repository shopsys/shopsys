import Register from 'framework/common/utils/Register';

export default class ExecuteRefund {
    static init ($container) {
        $container.filterAllNodes('.js-send-refund').on('click', function (event) {
            $('.' + $(this).data('execute-class')).val(true);
        });
    }
}

(new Register()).registerCallback(ExecuteRefund.init, 'ExecuteRefund.init');
