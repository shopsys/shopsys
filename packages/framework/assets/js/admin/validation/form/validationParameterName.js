import Register from '../../../common/utils/Register';

function validationParameterName(_$container) {
    window.$('form[name="parameter_form"]').jsFormValidator({
        callbacks: {
            validateUniqueParameterName: () => {
                // JS validation is not necessary
            },
        },
    });
}

new Register().registerCallback(validationParameterName);
