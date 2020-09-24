import CustomizeBundle from 'framework/common/validation/customizeBundle';
import Window from './Window';
import Translator from 'bazinga-translator';

export default function showFormErrorsWindow (container) {
    const $formattedFormErrors = CustomizeBundle.getFormattedFormErrors(container);
    const $window = $('#js-window');

    const $errorListHtml = '<div class="window-popup__errors">'
        + Translator.trans('<h2 class="window-popup__heading">Prosím zkontrolujte zadané údaje.</h2>')
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
