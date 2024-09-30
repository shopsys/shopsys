import Register from '../../../common/utils/Register';

function validationTransportPrices ($container) {
    window.$('form[name="transport_form"]').jsFormValidator({
        callbacks: {
            validateTransportPricesOnDomain: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(validationTransportPrices);
