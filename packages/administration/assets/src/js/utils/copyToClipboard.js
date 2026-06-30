import { Tooltip } from '@tabler/core';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

export default class CopyToClipboard {
    constructor($container) {
        const content = $container.attr('data-js-clipboard-copy') || '';
        const customTitle = $container.attr('data-js-clipboard-copy-title');

        const originalTitle = customTitle || Translator.trans('Copy to clipboard');

        let tooltip = new Tooltip($container, {
            title: originalTitle,
        });
        let copied = false;

        $container.click(event => {
            if (!navigator.clipboard) {
                console.warn('Clipboard API not supported. You may be on unsecure context (http).');
                return;
            }

            navigator.clipboard
                .writeText(content)
                .then(() => {
                    tooltip.dispose();
                    tooltip = new Tooltip($container, {
                        title: Translator.trans('Copied!'),
                    });

                    tooltip.show();
                    copied = true;
                })
                .catch(err => {
                    console.error('Failed to copy to clipboard:', err);
                });

            event.preventDefault();
        });

        $container.on('mouseleave', () => {
            if (tooltip && copied) {
                tooltip.dispose();
                tooltip = new Tooltip($container, {
                    title: originalTitle,
                });

                copied = false;
            }
        });
    }

    static init($container) {
        $container.filterAllNodes('[data-js-clipboard-copy]').each(function () {
            new CopyToClipboard($(this));
        });
    }
}

new Register().registerCallback(CopyToClipboard.init, 'CopyToClipboard.init');
