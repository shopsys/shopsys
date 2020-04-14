import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class OrderFillInformationForm {

    static setCustomerTypeFormFiled () {
        const customerType = $(this).data('tab-id');
        const hiddenCustomerTypeFormField = $('#order_personal_info_form_companyCustomer');
        if (customerType == 'company-customer') {
            hiddenCustomerTypeFormField.prop('checked', true);
        } else {
            hiddenCustomerTypeFormField.prop('checked', false);
        }
    }

    static init ($container) {
        $container
            .filterAllNodes('.js-tabs-button')
            .bind('click', OrderFillInformationForm.setCustomerTypeFormFiled);
    }
}

(new Register()).registerCallback(OrderFillInformationForm.init, 'OrderFillInformationForm.init');
