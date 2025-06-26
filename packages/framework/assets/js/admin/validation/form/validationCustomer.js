import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationCustomer() {
    const $customerUserUpdateFormType = $('#customer_user_update_form_customerUserData_personalData');
    const $emailField = $customerUserUpdateFormType.find(
        '#customer_user_update_form_customerUserData_personalData_email',
    );
    $emailField.jsFormValidator({
        callbacks: {
            validateUniqueEmail: () => {},
        },
    });

    const $customerBillingAddressForm = $('#customer_user_update_form_billingAddressData');
    $customerBillingAddressForm.jsFormValidator({
        groups: () => {
            var groups = [VALIDATION_GROUP_DEFAULT];
            if (
                $customerBillingAddressForm
                    .find('#customer_user_update_form_billingAddressData_companyData_companyCustomer')
                    .is(':checked')
            ) {
                groups.push('companyCustomer');
            }

            return groups;
        },
    });
}

new Register().registerCallback(validationCustomer, 'validationCustomer');
