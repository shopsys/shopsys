import 'framework/common/components';
import constant from '../../utils/constant';
import Register from 'framework/common/utils/Register';

export default function orderValidator ($container) {
    window.$('form[name="transport_and_payment_form"]').jsFormValidator({
        callbacks: {
            validateTransportPaymentRelation: function () {
                // JS validation is not necessary as it is not possible to select
                // an invalid combination of transport and payment.
            }
        }
    });

    const $orderPersonalInfoForm = window.$('form[name="order_personal_info_form"]');
    $orderPersonalInfoForm.jsFormValidator({
        'groups': function () {
            const selectedDeliveryAddressValue = $orderPersonalInfoForm.find('.js-delivery-address-input:checked').val();
            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];

            if ($orderPersonalInfoForm.find('#order_personal_info_form_deliveryAddressFilled').is(':checked') && (selectedDeliveryAddressValue === '' || selectedDeliveryAddressValue === undefined)) {
                groups.push(constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
            }
            if ($orderPersonalInfoForm.find('#order_personal_info_form_companyCustomer').is(':checked')) {
                groups.push(constant('\\App\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
            }

            return groups;
        }
    });

    $container.filterAllNodes('input[name="order_personal_info_form[companyCustomer]"]').on('change', event => {
        const $companyCustomerElement = $(event.target);
        if ($companyCustomerElement.is(':checked')) {
            return;
        }

        $('#js-company-fields').filterAllNodes('input, textarea').each((index, element) => {
            $(element).jsFormValidator().forEach(element => element.validate());
        });
    });
}

(new Register()).registerCallback(orderValidator);
