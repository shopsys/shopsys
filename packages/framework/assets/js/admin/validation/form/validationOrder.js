import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationOrder ($container) {
    const $orderForm = $container.filterAllNodes('form[name="order_form"]');
    $orderForm.jsFormValidator({
        'groups': function () {

            var groups = [VALIDATION_GROUP_DEFAULT];
            if (!$orderForm.find('#order_form_shippingAddressGroup_deliveryAddressSameAsBillingAddress').is(':checked')) {
                groups.push('deliveryAddressSameAsBillingAddress');
            }

            return groups;
        }
    });

    const $orderItemForms = $container.filterAllNodes('.js-order-item-any');
    $orderItemForms.each(function () {
        const $orderItemForm = $(this);

        $orderItemForm.jsFormValidator({
            'groups': function () {

                const groups = [VALIDATION_GROUP_DEFAULT];
                if ($orderItemForm.find('.js-set-prices-manually').is(':checked')) {
                    groups.push('notUsingPriceCalculation');
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationOrder, 'validationOrder');
