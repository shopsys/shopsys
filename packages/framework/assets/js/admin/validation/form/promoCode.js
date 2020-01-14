import Register from '../../../common/utils/register';

export default function validationPromoCode () {

    const $promoCodeFormField = $('#promo_code_form_code');
    $promoCodeFormField.jsFormValidator({
        callbacks: {
            validateUniquePromoCode: function () {

            }
        }
    });
}

(new Register()).registerCallback(validationPromoCode);
