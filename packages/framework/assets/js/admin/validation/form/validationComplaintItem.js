import Register from '../../../common/utils/Register';

function validationComplaintItem ($container) {
    window.$('form[name="complaint_form"]').jsFormValidator({
        callbacks: {
            validateQuantityIsLessOrEqualThanOrdered: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(validationComplaintItem);
