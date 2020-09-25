import constant from '../../utils/constant';
import Register from 'framework/common/utils/Register';

export default function customerValidator () {

    const $customerDeliveryAddressForm = window.$('.js-delivery-address-form');
    $customerDeliveryAddressForm.jsFormValidator({
        'groups': function () {

            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            if ($customerDeliveryAddressForm.find('.js-delivery-address-address-filled').is(':checked')) {
                groups.push(constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
            }

            return groups;
        }
    });

    $('#customer_user_update_form_deliveryAddressData_addressFilled').change(function () {
        if ($(this).is(':checked') === false) {
            $customerDeliveryAddressForm.find('input').jsFormValidator('validate');
        }
    });

    const $customerBillingAddressForm = window.$('.js-billing-address-form');
    $customerBillingAddressForm.jsFormValidator({
        'groups': function () {

            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            if ($customerBillingAddressForm.find('.js-billing-address-company-customer').is(':checked')) {
                groups.push(constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(customerValidator, 'customerValidator');
