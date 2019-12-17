import Register from '../common/register';

export default class OrderTransportAndPaymentPrefiller {

    constructor () {
        this.$transportSelect = $('#order_form_orderItems_orderTransport_transport');
        this.transportPricesWithVatByTransportId = this.$transportSelect.closest('.js-order-transport-row').data('transport-prices-with-vat-by-transport-id');
        this.transportVatPercentsByTransportId = this.$transportSelect.closest('.js-order-transport-row').data('transport-vat-percents-by-transport-id');

        this.$paymentSelect = $('#order_form_orderItems_orderPayment_payment');
        this.paymentPricesWithVatByPaymentId = this.$paymentSelect.closest('.js-order-payment-row').data('payment-prices-with-vat-by-payment-id');
        this.paymentVatPercentsByPaymentId = this.$paymentSelect.closest('.js-order-payment-row').data('payment-vat-percents-by-payment-id');

        this.$transportSelect.on('change', () => this.onOrderTransportChange());
        this.$paymentSelect.on('change', () => this.onOrderPaymentChange());
    }

    onOrderTransportChange () {
        const selectedTransportId = this.$transportSelect.val();
        $('#order_form_orderItems_orderTransport_priceWithVat').val(this.transportPricesWithVatByTransportId[selectedTransportId].amount);
        $('#order_form_orderItems_orderTransport_vatPercent').val(this.transportVatPercentsByTransportId[selectedTransportId]);
        $('#order_form_orderItems_orderTransport_usePriceCalculation').prop('checked', true).change();
        $('#order_form_orderItems_orderTransport_priceWithoutVat').val('');
    };

    onOrderPaymentChange () {
        const selectedPaymentId = this.$paymentSelect.val();
        $('#order_form_orderItems_orderPayment_priceWithVat').val(this.paymentPricesWithVatByPaymentId[selectedPaymentId].amount);
        $('#order_form_orderItems_orderPayment_vatPercent').val(this.paymentVatPercentsByPaymentId[selectedPaymentId]);
        $('#order_form_orderItems_orderPayment_usePriceCalculation').prop('checked', true).change();
        $('#order_form_orderItems_orderPayment_priceWithoutVat').val('');
    };

    static init () {
        // eslint-disable-next-line no-new
        new OrderTransportAndPaymentPrefiller();
    }
}

(new Register()).registerCallback(OrderTransportAndPaymentPrefiller.init);
