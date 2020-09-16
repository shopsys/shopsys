import Register from 'framework/common/utils/Register';

export default function paymentFormValidation ($container) {
    const $paymentForm = $container.filterAllNodes('form[name="payment_form"]');

    const $externalIdField = $paymentForm.find('#payment_form_basicInformation_externalId');
    $externalIdField.jsFormValidator({
        'callbacks': {
            'validateUniqueExternalId': function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(paymentFormValidation);
