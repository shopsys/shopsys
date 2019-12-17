import { getFormattedFormErrors, isFormValid } from '../../common/validation/customizeBundle';
import Register from '../../common/register';

export function forceValidateElement ($element) {
    $element.jsFormValidator('validate');

    if ($element.jsFormValidator) {
        let parent = $element.jsFormValidator.parent;
        while (parent) {
            parent.validate();
            parent = parent.parent;
        }
    }
}

export function findElementsToHighlight ($formInput) {
    return $formInput.filter('input, select, textarea, .form-line, .table-form');
}

export function highlightSubmitButtons ($form) {
    const $submitButtons = $form.find('.btn[type="submit"]');

    if (isFormValid($form)) {
        $submitButtons.removeClass('btn--disabled');
    } else {
        $submitButtons.addClass('btn--disabled');
    }
}

export function init () {
    const $formattedFormErrors = getFormattedFormErrors(document);
    $('.js-flash-message.in-message--danger').append($formattedFormErrors);

    $('.js-no-validate-button').click((event) => {
        $(event.currentTarget).closest('form').addClass('js-no-validate');
    });
}

(new Register()).registerCallback(init);
