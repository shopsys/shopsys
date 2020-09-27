import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class OrderFillInformationForm {

    static setCustomerTypeFormFiled () {
        const customerType = $(this).data('tab-id');
        const hiddenCustomerTypeFormField = $('#order_personal_info_form_companyCustomer');
        if (customerType == 'company-customer') {
            hiddenCustomerTypeFormField.prop('checked', true).change();
        } else {
            hiddenCustomerTypeFormField.prop('checked', false).change();
        }
    }

    static init ($container) {
        $container
            .filterAllNodes('.js-tabs-button')
            .bind('click', OrderFillInformationForm.setCustomerTypeFormFiled);

        $container.keypress(event => {
            if (event.which == '13') {
                event.preventDefault();
            }
        });
    }
}

(new Register()).registerCallback(OrderFillInformationForm.init, 'OrderFillInformationForm.init');
