import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';
import ModalWindow from './modalWindow';

export default class ConfirmWindow {
    constructor($element) {
        $element.on('click', event => {
            event.preventDefault();

            const style = $element.data('confirm-style') || 'danger';
            const content = $element.data('confirm-message') || '';
            const continueUrl = $element.data('confirm-continue-url') || $element.attr('href') || '#';

            ConfirmWindow.show({
                content,
                style,
                continueUrl,
            });
        });
    }

    /**
     * Display a confirmation modal window
     * @param {Object} [options={}] - Configuration options
     * @param {string} [options.title] - Modal title (defaults to a translated "Are you sure?")
     * @param {string} [options.content=''] - Confirmation message content
     * @param {string} [options.style='danger'] - Modal style (danger, warning, info, etc.)
     * @param {string} [options.continueUrl='#'] - URL to navigate to when confirmed
     * @param {string} [options.continueEvent=null] - Optional event to trigger on confirmation
     * @param {string} [options.textContinue] - Text for the continue button (defaults to a translated "Yes")
     * @param {string} [options.textCancel] - Text for the cancel button (defaults to a translated "No")
     */
    static show(options = {}) {
        const resolvedOptions = {
            title: Translator.trans('Are you sure?'),
            content: '',
            style: 'danger',
            continueUrl: '#',
            continueEvent: null,
            textContinue: Translator.trans('Yes'),
            textCancel: Translator.trans('No'),
            ...options,
        };

        if (resolvedOptions.continueEvent !== null && resolvedOptions.continueUrl !== '#') {
            console.warn('Both continueEvent and continueUrl are set. Only continueUrl will be used.');
        }

        new ModalWindow({
            title: resolvedOptions.title,
            content: resolvedOptions.content,
            style: resolvedOptions.style,
            buttons: [
                { text: resolvedOptions.textCancel },
                {
                    text: resolvedOptions.textContinue,
                    style: resolvedOptions.style,
                    href: resolvedOptions.continueUrl,
                    event: resolvedOptions.continueEvent,
                    classAttribute: 'test-window-button-continue',
                },
            ],
        });
    }

    static init($container) {
        $container.filterAllNodes('[data-confirm-window]').each(function () {
            new ConfirmWindow($(this));
        });
    }
}

new Register().registerCallback(ConfirmWindow.init, 'ConfirmWindow.init');
