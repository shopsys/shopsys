import Register from '../../common/utils/Register';
import Window from '../utils/Window';

export default class StaticConfirmWindow {

    constructor (element) {
        $(element).on('click', (event) => this.showWindow(event));
    }

    showWindow (event) {
        event.preventDefault();

        // eslint-disable-next-line no-new
        new Window({
            content: $(event.target).data('confirm-message'),
            buttonCancel: true,
            buttonContinue: true,
            urlContinue: $(event.target).data('confirm-contiue-url')
        });
    }

    static init ($container) {
        $container.filterAllNodes('a[data-confirm-window]').each((idx, element) => {
            // eslint-disable-next-line no-new
            new StaticConfirmWindow(element);
        });
    }
}

(new Register()).registerCallback(StaticConfirmWindow.init);
