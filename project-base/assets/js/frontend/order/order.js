import $ from 'jquery';
import Register from '../../copyFromFw/register';

export default class PaymentTransportRelations {

    constructor () {
        this.paymentTransportRelations = {};

        const paymentTransportRelations = $('.js-payment-transport-relations');
        if (paymentTransportRelations.length > 0) {
            paymentTransportRelations.data('relations').forEach(item => {
                this.addPaymentTransportRelation(item.paymentId, item.transportId);
            });
        }
    }

    addPaymentTransportRelation (paymentId, transportId) {
        if (this.paymentTransportRelations[paymentId] === undefined) {
            this.paymentTransportRelations[paymentId] = {};
        }
        this.paymentTransportRelations[paymentId][transportId] = true;
    };

    paymentTransportRelationExists (paymentId, transportId) {
        if (this.paymentTransportRelations[paymentId] !== undefined) {
            if (this.paymentTransportRelations[paymentId][transportId] !== undefined) {
                return this.paymentTransportRelations[paymentId][transportId];
            }
        }

        return false;
    };

    updateTransports () {
        const $checkedPayment = $('.js-order-payment-input:checked');
        if ($checkedPayment.length > 0) {
            const checkedPaymentId = $checkedPayment.data('id');
            const _this = this;
            $('.js-order-transport-input').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                const id = $checkbox.data('id');
                if (_this.paymentTransportRelationExists(checkedPaymentId, id)) {
                    $checkbox.prop('disabled', false);
                    $checkbox.closest('label.box-chooser__item').removeClass('box-chooser__item--inactive');
                } else {
                    $checkbox.prop('disabled', true);
                    $checkbox.prop('checked', false);
                    $checkbox.closest('label.box-chooser__item').addClass('box-chooser__item--inactive');
                }
            });
        } else {
            $('.js-order-transport-input').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                $checkbox.prop('disabled', false);
                $checkbox.closest('label.box-chooser__item').removeClass('box-chooser__item--active').removeClass('box-chooser__item--inactive');
            });
        }

        const $checkedTransport = $('.js-order-transport-input:checked');
        if ($checkedTransport.length > 0) {
            $checkedTransport.closest('label.box-chooser__item').removeClass('box-chooser__item--inactive').addClass('box-chooser__item--active');
        }
    };

    updatePayments () {
        const $checkedTransport = $('.js-order-transport-input:checked');
        if ($checkedTransport.length > 0) {
            const checkedTransportId = $checkedTransport.data('id');
            const _this = this;
            $('.js-order-payment-input').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                const id = $checkbox.data('id');
                if (_this.paymentTransportRelationExists(id, checkedTransportId)) {
                    $checkbox.prop('disabled', false);
                    $checkbox.closest('label.box-chooser__item').removeClass('box-chooser__item--inactive');
                } else {
                    $checkbox.prop('disabled', true);
                    $checkbox.prop('checked', false);
                    $checkbox.closest('label.box-chooser__item').addClass('box-chooser__item--inactive');
                }
            });
        } else {
            $('.js-order-payment-input').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                $checkbox.prop('disabled', false);
                $checkbox.closest('label.box-chooser__item').removeClass('box-chooser__item--active').removeClass('box-chooser__item--inactive');
            });
        }

        const $checkedPayment = $('.js-order-payment-input:checked');
        if ($checkedPayment.length > 0) {
            $checkedPayment.closest('label.box-chooser__item').removeClass('box-chooser__item--inactive').addClass('box-chooser__item--active');
        }
    };

    onTransportChange (event, paymentTransportRelations) {
        const $this = $(event.currentTarget);
        const checked = $this.prop('checked');
        const checkedId = $this.data('id');

        if (checked) {
            // uncheckOtherTransports
            $('.js-order-transport-input:checked').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                const id = $checkbox.data('id');
                if (id !== checkedId) {
                    $checkbox.prop('checked', false);
                    $(this).closest('label.box-chooser__item').removeClass('box-chooser__item--active');
                }
            });

            $this.closest('label.box-chooser__item').addClass('box-chooser__item--active');
        } else {
            $this.closest('label.box-chooser__item').removeClass('box-chooser__item--active');
        }

        paymentTransportRelations.updatePayments();
    };

    onPaymentChange (event, paymentTransportRelations) {
        const $this = $(event.currentTarget);
        const checked = $this.prop('checked');
        const checkedId = $this.data('id');

        if (checked) {
            // uncheckOtherPayments
            $('.js-order-payment-input:checked').each(function (i, checkbox) {
                const $checkbox = $(checkbox);
                const id = $checkbox.data('id');
                if (id !== checkedId) {
                    $checkbox.prop('checked', false);
                    $(this).closest('label.box-chooser__item').removeClass('box-chooser__item--active');
                }
            });

            $this.closest('label.box-chooser__item').addClass('box-chooser__item--active');
        } else {
            $this.closest('label.box-chooser__item').removeClass('box-chooser__item--active');
        }

        paymentTransportRelations.updateTransports();
    };

    updateContinueButton () {
        const checkedTransport = $('.js-order-transport-input:checked');
        const checkedPayment = $('.js-order-payment-input:checked');

        if (checkedTransport.length === 1 && checkedPayment.length === 1) {
            $('#transport_and_payment_form_save').removeClass('btn--disabled');
        } else {
            $('#transport_and_payment_form_save').addClass('btn--disabled');
        }
    };

    static init ($container) {
        const $transportInputs = $container.filterAllNodes('.js-order-transport-input');
        const $paymentInputs = $container.filterAllNodes('.js-order-payment-input');
        const paymentTransportRelations = new PaymentTransportRelations();

        $transportInputs.change((event) => paymentTransportRelations.onTransportChange(event, paymentTransportRelations));
        $paymentInputs.change((event) => paymentTransportRelations.onPaymentChange(event, paymentTransportRelations));
        paymentTransportRelations.updateTransports();
        paymentTransportRelations.updatePayments();

        $transportInputs.change(paymentTransportRelations.updateContinueButton);
        $paymentInputs.change(paymentTransportRelations.updateContinueButton);
        paymentTransportRelations.updateContinueButton();
    };
}

(new Register()).registerCallback(PaymentTransportRelations.init);
