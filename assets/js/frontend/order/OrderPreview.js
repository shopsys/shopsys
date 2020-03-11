import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Timeout from 'framework/common/utils/Timeout';

export default class OrderPreview {

    static loadOrderPreview () {
        const $orderPreview = $('#js-order-preview');
        const $checkedTransports = $('.js-order-transport-input:checked');
        const $checkedPayment = $('.js-order-payment-input:checked');
        const data = {};

        data['transportIdsByProductTypeId'] = {};
        $checkedTransports.each(function () {
            const productTypeId = $(this).closest('.js-payment-transport-group').data('product-type-id');
            data['transportIdsByProductTypeId'][productTypeId] = $(this).data('id');
        });
        if ($checkedPayment.length > 0) {
            data['paymentId'] = $checkedPayment.data('id');
        }

        Ajax.ajaxPendingCall('Shopsys.orderPreview.loadOrderPreview', {
            loaderElement: '#js-order-preview',
            url: $orderPreview.data('url'),
            type: 'get',
            data: data,
            success: function (successData) {
                $orderPreview.html(successData);
                (new Register()).registerNewContent($orderPreview);
            }
        });
    }

    static littleDelayedLoadOrderPreview () {
        Timeout.setTimeoutAndClearPrevious(
            'OrderPreviewLoad',
            () => {
                OrderPreview.loadOrderPreview();
            },
            20
        );
    }

    static init ($container) {
        $container
            .filterAllNodes('.js-order-transport-input, .js-order-payment-input')
            .bind('change', OrderPreview.loadOrderPreview)
            .bind('orderPreview.littleDelayedLoadOrderPreview', OrderPreview.loadOrderPreview);
    }
}

(new Register()).registerCallback(OrderPreview.init, 'OrderPreview.init');
