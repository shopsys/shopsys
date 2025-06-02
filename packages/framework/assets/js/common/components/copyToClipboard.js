import Register from '../utils/Register';
import Translator from 'bazinga-translator';

export default class CopyToClipboard {
    constructor () {
        const copyNodes = document.getElementsByClassName('js-copy-to-clipboard');

        for (let node of copyNodes) {
            let tooltip = new bootstrap.Tooltip(node, {
                placement: 'right'
            });

            node.addEventListener('click', (event) => {
                const content = node.getAttribute('data-bs-original-title');

                navigator.clipboard.writeText(content).then(() => {
                    node.setAttribute('title', Translator.trans('Copied to clipboard!'));
                    node.setAttribute('data-bs-original-title', Translator.trans('Copied to clipboard!'));

                    tooltip.dispose();
                    tooltip = new bootstrap.Tooltip(node, {
                        placement: 'right'
                    });
                    tooltip.show();

                    node.setAttribute('data-bs-original-title', content);
                    node.setAttribute('title', content);
                });
            });
        }
    }

    static init () {
        // eslint-disable-next-line no-new
        new CopyToClipboard();
    }
}

new Register().registerCallback(CopyToClipboard.init, 'CopyToClipboard.init');
