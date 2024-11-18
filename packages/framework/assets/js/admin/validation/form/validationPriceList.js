import Register from '../../../common/utils/Register';

export default function priceListValidator ($container) {
    $container.filterAllNodes('form[name="price_list_form"]').jsFormValidator({
        callbacks: {
            checkDateValidity: function () {
                // JS validation is not necessary
            },
            checkPricesValidity: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(priceListValidator, 'priceListValidator');
