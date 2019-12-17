import constant from '../../constant';
import Register from '../../../common/register';

export default function validationFreTransportAndPayment () {
    $('.js-free-transport-and-payment-price-limit').each(function () {
        const $priceLimitForm = $(this);
        $priceLimitForm.jsFormValidator({
            'groups': function () {

                const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                if ($priceLimitForm.find('.js-free-transport-and-payment-price-limit-enabled').is(':checked')) {
                    groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\TransportAndPayment\\FreeTransportAndPaymentPriceLimitsFormType::VALIDATION_GROUP_PRICE_LIMIT_ENABLED'));
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationFreTransportAndPayment);
