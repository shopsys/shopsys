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
            if ($orderPersonalInfoForm.find('#order_personal_info_form_register').is(':checked')) {
                groups.push(constant('\\App\\Form\\Front\\Order\\PersonalInfoFormType::VALIDATION_REGISTRATION'));
                const orderForm = $orderPersonalInfoForm.data('order-form-instance');
                if (orderForm.customerInfo.exists === false) {
                    groups.push(constant('\\App\\Form\\Front\\Order\\PersonalInfoFormType::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD'));
                }
            }

            return groups;
        }
    });

    $orderPersonalInfoForm.find('.js-order-delivery-address')
        .change(function () {
            // Run validation for this field
            if ($(this).is(':checked') === false) {
                $orderPersonalInfoForm.find('.js-order-delivery-address-fields input').jsFormValidator('validate');
            }
        });
    $container.find('#order_personal_info_form_companyCustomer')
        .change(function () {
            // Run validation for this field
            if ($(this).is(':checked')) {
                $orderPersonalInfoForm.find('*[data-tab-id="common-customer"] input').jsFormValidator('validate');
            } else {
                $orderPersonalInfoForm.find('*[data-tab-id="company-customer"] input').jsFormValidator('validate');
            }
        });

}

(new Register()).registerCallback(orderValidator);
