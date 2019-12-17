import Register from '../register';
import { isFormValid } from './customizeBundle';

export const initClasses = ($container) => {
    $container.filterAllNodes('.js-no-validate-button').click(function () {
        $(this).closest('form').addClass('js-no-validate');
    });

    $container.filterAllNodes('.js-validation-error-close').click(function () {
        $(this).closest('.js-validation-error').hide();
    });

    $container.filterAllNodes('.js-validation-error-toggle').click(function () {
        $(this)
            .closest('.js-validation-errors-list')
            .find('.js-validation-error')
            .toggle();
    });
};

export const findElementsToHighlight = ($formInput) => {
    return $formInput.filter('input, select, textarea, .form-line');
};

export const highlightSubmitButtons = ($form) => {
    const $submitButtons = $form.find('.btn[type="submit"]:not(.js-no-validate-button)');

    if (isFormValid($form)) {
        $submitButtons.removeClass('btn--disabled');
    } else {
        $submitButtons.addClass('btn--disabled');
    }
};

(new Register()).registerCallback(initClasses);
