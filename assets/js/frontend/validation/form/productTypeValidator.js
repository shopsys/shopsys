import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default function productTypeValidator ($container) {
    window.$('form[name="product_type_form"]').jsFormValidator({
        callbacks: {
            validateFreeTransportMinimalPriceByDomain: function () {
                // JS validation is not necessary
            }
        }
    });
}

(new Register()).registerCallback(productTypeValidator);
