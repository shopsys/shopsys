import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationProduct () {
    const $productForm = $('form[name="product_form"]');
    $productForm.jsFormValidator({
        'groups': function () {

            const groups = [VALIDATION_GROUP_DEFAULT];

            if ($('input[name="product_form[displayAvailabilityGroup][usingStock]"]:checked').val() === '1') {
                groups.push('usingStock');
                if ($('select[name="product_form[displayAvailabilityGroup][stockGroup][outOfStockAction]"]').val() === 'setAlternateAvailability') {
                    groups.push('usingStockAndAlternateAvailability');
                }
            } else {
                groups.push('notUsingStock');
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(validationProduct, 'validationProduct');
