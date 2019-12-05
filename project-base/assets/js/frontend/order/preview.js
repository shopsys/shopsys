import $ from 'jquery';
import Ajax from '../../copyFromFw/ajax';
import Register from '../../copyFromFw/register';

export default class OrderPreview {

    static loadOrderPreview () {
        const $orderPreview = $('#js-order-preview');
        const $checkedTransport = $('.js-order-transport-input:checked');
        const $checkedPayment = $('.js-order-payment-input:checked');
        const data = {};

        if ($checkedTransport.length > 0) {
            data['transportId'] = $checkedTransport.data('id');
        }
        if ($checkedPayment.length > 0) {
            data['paymentId'] = $checkedPayment.data('id');
        }

        Ajax.ajaxPendingCall('Shopsys.orderPreview.loadOrderPreview', {
            loaderElement: '#js-order-preview',
            url: $orderPreview.data('url'),
            type: 'get',
            data: data,
            success: function (data) {
                $orderPreview.html(data);
                (new Register()).registerNewContent($orderPreview);
            }
        });
    };

    static init ($container) {
        $container
            .filterAllNodes('.js-order-transport-input, .js-order-payment-input')
            .change(OrderPreview.loadOrderPreview);
    };
}

(new Register()).registerCallback(OrderPreview.init);
