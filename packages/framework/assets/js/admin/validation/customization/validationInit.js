import CustomizeBundle from '../../../common/validation/customizeBundle';
import Register from '../../../common/utils/Register';

export function validationInit () {
    const $formattedFormErrors = CustomizeBundle.getFormattedFormErrors(document);
    $('.js-flash-message.in-message--danger').append($formattedFormErrors);
}

(new Register()).registerCallback(validationInit);
