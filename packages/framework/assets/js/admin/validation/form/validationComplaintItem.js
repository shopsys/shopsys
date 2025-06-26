import Register from '../../../common/utils/Register';

function validationComplaintItem(_$container) {
    window.$('form[name="complaint_form"]').jsFormValidator({
        callbacks: {
            validateQuantityIsLessOrEqualThanOrdered: () => {
                // JS validation is not necessary
            },
        },
    });
}

new Register().registerCallback(validationComplaintItem);
