import Register from '../../../common/utils/Register';

function validationTransportPrices(_$container) {
    window.$('form[name="transport_form"]').jsFormValidator({
        callbacks: {
            validateTransportPricesOnDomain: () => {
                // JS validation is not necessary
            },
        },
    });
}

new Register().registerCallback(validationTransportPrices);
