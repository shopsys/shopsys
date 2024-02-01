import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationFreeTransportAndPayment () {
    $('.js-free-transport-and-payment-price-limit').each(function () {
        const $priceLimitForm = $(this);
        $priceLimitForm.jsFormValidator({
            'groups': function () {

                const groups = [VALIDATION_GROUP_DEFAULT];
                if ($priceLimitForm.find('.js-free-transport-and-payment-price-limit-enabled').is(':checked')) {
                    groups.push('priceLimitEnabled');
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationFreeTransportAndPayment, 'validationFreeTransportAndPayment');
