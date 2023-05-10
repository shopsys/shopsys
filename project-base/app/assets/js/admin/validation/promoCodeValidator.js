import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default function promoCodeValidator ($container) {
    window.$('form[name="promo_code_form"]').jsFormValidator({
        callbacks: {
            validateTimeIfIsSet: function () {
                // JS validation is not necessary
            },

            validateUniquePromoCodeByDomain: function () {
                // JS validation is not necessary
            },

            validateDateTimeFrom: function () {
                // JS validation is not necessary
            },

            validateDateTimeTo: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(promoCodeValidator);
