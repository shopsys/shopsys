import CustomizeBundle from 'framework/common/validation/customizeBundle';
import Window from './Window';
import Translator from 'bazinga-translator';

export default function showFormErrorsWindow (container) {
    const $formattedFormErrors = CustomizeBundle.getFormattedFormErrors(container);
    const $window = $('#js-window');

    const $errorListHtml = '<div class="text-left">'
        + Translator.trans('Please check the entered values.<br>')
        + $formattedFormErrors[0].outerHTML
        + '</div>';

    if ($window.length === 0) {
        // eslint-disable-next-line no-new
        new Window({
            errors: $errorListHtml
        });
    } else {
        $window.filterAllNodes('.js-window-validation-errors')
            .html($errorListHtml)
            .removeClass('display-none');
    }
}
