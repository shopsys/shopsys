import Register from '../utils/Register';
import '../../common/bootstrap/tooltip';
import Translator from 'bazinga-translator';

export default class CopyToClipboard {

    constructor ($container) {
        const $copyNodes = $container.filterAllNodes('.js-copy-to-clipboard[data-content]');

        $copyNodes.tooltip();
        $copyNodes.click((event) => _this.onClick(event));

        const _this = this;
    }

    onClick (event) {
        const content = $(event.currentTarget).data('content');
        navigator.clipboard.writeText(content).then(() => {
            $(event.currentTarget).attr('title', Translator.trans('Copied to clipboard!'));

            $(event.currentTarget).tooltip('destroy');
            $(event.currentTarget).tooltip('show');

            $(event.currentTarget).attr('title', content);
            $(event.currentTarget).attr('data-original-title', content);
        });
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new CopyToClipboard($container);
    }
}

(new Register()).registerCallback(CopyToClipboard.init, 'CopyToClipboard.init');
