import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class RegistrationFillInformationForm {

    static setCustomerTypeFormFiled () {
        const customerType = $(this).data('tab-id');
        $('#registration_form_companyCustomer').prop('checked', customerType === 'company-customer').change();
    }

    static init ($container) {
        $container
            .filterAllNodes('form[name="registration_form"] .js-tabs-button')
            .bind('click', RegistrationFillInformationForm.setCustomerTypeFormFiled);
    }
}

(new Register()).registerCallback(RegistrationFillInformationForm.init, 'RegistrationFillInformationForm.init');
