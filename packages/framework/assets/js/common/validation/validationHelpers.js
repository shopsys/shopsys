import CustomizeBundle from './customizeBundle';

export const findElementsToHighlight = ($formInput) => {
    return $formInput.filter('input, select, textarea, .form-line');
};

export const highlightSubmitButtons = ($form) => {
    const $submitButtons = $form.find('.btn[type="submit"]:not(.js-no-validate-button)');

    if (CustomizeBundle.isFormValid($form)) {
        $submitButtons.removeClass('btn--disabled');
    } else {
        $submitButtons.addClass('btn--disabled');
    }
};

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
