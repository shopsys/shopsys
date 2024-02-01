import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationCustomer () {
    const $customerDeliveryAddressForm = $('#customer_form_deliveryAddressData');
    $customerDeliveryAddressForm.jsFormValidator({
        'groups': function () {
            const groups = [VALIDATION_GROUP_DEFAULT];
            if ($customerDeliveryAddressForm.find('#customer_form_deliveryAddressData_deliveryAddress_addressFilled').is(':checked')) {
                groups.push('differentDeliveryAddress');
            }

            return groups;
        }
    });
    const $customerBillingAddressForm = $('#customer_form_billingAddressData');
    $customerBillingAddressForm.jsFormValidator({
        'groups': function () {

            var groups = [VALIDATION_GROUP_DEFAULT];
            if ($customerBillingAddressForm.find('#customer_form_billingAddressData_companyData_companyCustomer').is(':checked')) {
                groups.push('companyCustomer');
            }

            return groups;
        }
    });
}

(new Register().registerCallback(validationCustomer, 'validationCustomer'));
