import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class PromoCode {
    constructor() {
        this.$discountTypeInputGroup = $('.js-promo-code-discount-type');
        this.$discountTypeInputGroup.on('change', this.handleDiscountTypeChange.bind(this));
        this.handleDiscountTypeChange();
    }

    handleDiscountTypeChange() {
        this.$discountTypeInputGroup.find('input[type=radio]:checked').each(function () {
            $('[data-js-promo-code-limits-group]').toggle($(this).val() !== 'free_transport_payment');
        });
    }

    static init() {
        // eslint-disable-next-line no-new
        new PromoCode();
    }
}

new Register().registerCallback(PromoCode.init, 'PromoCode.init');
