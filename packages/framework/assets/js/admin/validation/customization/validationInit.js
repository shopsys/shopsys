import Register from '../../../common/utils/Register';
import CustomizeBundle from '../../../common/validation/customizeBundle';

export function validationInit() {
    const $formattedFormErrors = CustomizeBundle.getFormattedFormErrors(document);
    $('.js-flash-message.in-message--danger').append($formattedFormErrors);
}

new Register().registerCallback(validationInit);
