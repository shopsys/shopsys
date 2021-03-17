import 'framework/common/components';
import constant from '../../utils/constant';
import Register from 'framework/common/utils/Register';

export default function registrationValidator ($container) {

    const $registrationForm = $container.filterAllNodes('form[name="registration_form"]');
    $registrationForm.jsFormValidator({
        'groups': function () {
            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];

            if ($registrationForm.find('#registration_form_companyCustomer').is(':checked')) {
                groups.push(constant('\\App\\Form\\Front\\Registration\\RegistrationFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
            }

            const registrationForm = $registrationForm.data('registration-form-instance');
            if (!registrationForm.customerInfo.exists) {
                groups.push(constant('\\App\\Form\\Front\\Registration\\RegistrationFormType::VALIDATION_GROUP_REGULAR_REGISTRATION'));
            }

            return groups;
        }
    });

    $container.find('#registration_form_companyCustomer')
        .change(function () {
            // Run validation for this field
            if ($(this).is(':checked')) {
                $registrationForm.find('*[data-tab-id="common-customer"] input').jsFormValidator('validate');
            } else {
                $registrationForm.find('*[data-tab-id="company-customer"] input').jsFormValidator('validate');
            }
        });

}

(new Register()).registerCallback(registrationValidator);
