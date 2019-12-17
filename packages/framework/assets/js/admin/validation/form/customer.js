import constant from '../../constant';
import Register from '../../../common/register';

export default function validationCustomer () {
    const $customerDeliveryAddressForm = $('#customer_form_deliveryAddressData');
    $customerDeliveryAddressForm.jsFormValidator({
        'groups': function () {
            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            if ($customerDeliveryAddressForm.find('#customer_form_deliveryAddressData_deliveryAddress_addressFilled').is(':checked')) {
                groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
            }

            console.log(groups);
            return groups;
        }
    });
    const $customerBillingAddressForm = $('#customer_form_billingAddressData');
    $customerBillingAddressForm.jsFormValidator({
        'groups': function () {

            var groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            if ($customerBillingAddressForm.find('#customer_form_billingAddressData_companyData_companyCustomer').is(':checked')) {
                groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
            }

            return groups;
        }
    });
}

(new Register().registerCallback(validationCustomer));
