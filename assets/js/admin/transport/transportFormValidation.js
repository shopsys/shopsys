import Register from 'framework/common/utils/Register';

export default function transportFormValidation ($container) {
    const $transportForm = $container.filterAllNodes('form[name="transport_form"]');

    $transportForm.jsFormValidator({
        'groups': function () {
            const groups = ['Default'];

            if ($transportForm.find('#transport_form_basicInformation_type input:checked').val() === 'package') { // App\Model\Transport\Transport::TYPE_PACKAGE
                groups.push('type_package'); // App\Form\Admin\TransportFormTypeExtension::VALIDATION_GROUP_TYPE_PACKAGE
            }

            return groups;
        },
        'callbacks': {
            'validateLeastOnePackageOnEachAllowedDomain': function () {
                // JS validation is not implemented yet. Too expensive, not much time to lunch production...
            }
        }
    });

    const $externalIdField = $transportForm.find('#transport_form_basicInformation_externalId');
    console.log($externalIdField);
    $externalIdField.jsFormValidator({
        'callbacks': {
            'validateUniqueExternalId': function () {
                // JS validation is not necesssary
            }
        }
    });
}

(new Register()).registerCallback(transportFormValidation);
