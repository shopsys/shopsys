import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationCustomer () {
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
