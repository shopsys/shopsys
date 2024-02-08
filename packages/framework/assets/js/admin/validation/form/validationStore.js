import Register from '../../../common/utils/Register';

export default function validationStore ($container) {
    const storeForm = $container.filterAllNodes('form[name="store_form"]');
    const storeFormExternalId = $container.filterAllNodes('#store_form_externalId');
    storeForm.jsFormValidator({
        callbacks: {
            validateOpeningHours: function () {

            }
        }
    });
    storeFormExternalId.jsFormValidator({
        callbacks: {
            sameStoreExternalIdValidation: function () {

            }
        }
    });
}

(new Register()).registerCallback(validationStore, 'validationStore');
