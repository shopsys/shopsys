import Register from '../../../common/utils/Register';

function validationParameterName ($container) {
    window.$('form[name="parameter_form"]').jsFormValidator({
        callbacks: {
            validateUniqueParameterName: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(validationParameterName);
