import Register from '../utils/Register';

export default function validationListeners ($container) {
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

(new Register()).registerCallback(validationListeners);
