import Register from '../../../common/utils/Register';

export default function validationPromoCode() {
    const $promoCodeFormField = $('#promo_code_form_code');
    $promoCodeFormField.jsFormValidator({
        callbacks: {
            validateUniquePromoCode: () => {},
            validateUniquePromoCodeByDomain: () => {
                // JS validation is not necessary
            },
        },
    });
}

new Register().registerCallback(validationPromoCode, 'validationPromoCode');
