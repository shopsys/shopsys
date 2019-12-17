import Register from '../common/register';

export default class FreeTransportAndPayment {

    constructor ($container) {
        this.$checkbox = $container.find('.js-free-transport-and-payment-price-limit-enabled');
        this.$input = $container.find('.js-free-transport-and-payment-price-limit-input');

        this.$checkbox.click(() => this.updateInputDisabledAttribute());
        this.updateInputDisabledAttribute();
    }

    updateInputDisabledAttribute () {
        const _this = this;
        _this.$input.attr('disabled', _this.$checkbox.is(':checked') ? null : 'disabled');
    }

    static init ($container) {
        $container.filterAllNodes('.js-free-transport-and-payment-price-limit').each(function () {
            // eslint-disable-next-line no-new
            new FreeTransportAndPayment($(this));
        });
    }
}

(new Register()).registerCallback(FreeTransportAndPayment.init);
