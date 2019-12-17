import constant from '../../constant';
import Register from '../../../common/register';

export default function validationOrder ($container) {
    const $orderForm = $container.filterAllNodes('form[name="order_form"]');
    $orderForm.jsFormValidator({
        'groups': function () {

            var groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            if (!$orderForm.find('#order_form_shippingAddressGroup_deliveryAddressSameAsBillingAddress').is(':checked')) {
                groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Order\\OrderFormType::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS'));
            }

            return groups;
        }
    });

    const $orderItemForms = $container.filterAllNodes('.js-order-item-any');
    $orderItemForms.each(function () {
        const $orderItemForm = $(this);

        $orderItemForm.jsFormValidator({
            'groups': function () {

                const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                if ($orderItemForm.find('.js-set-prices-manually').is(':checked')) {
                    groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Order\\OrderItemFormType::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION'));
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationOrder);
