import Register from 'framework/common/utils/Register';
import Coloris from '@melloware/coloris';
import Translator from 'bazinga-translator';

export function initColorPicker ($container) {
    const colorInputs = $container.filterAllNodes('[data-coloris]');

    if (colorInputs.length > 0) {
        Coloris.init();
        Coloris({
            el: '[data-coloris]',
            theme: 'large',
            themeMode: 'auto',
            alpha: false,
            clearButton: true,
            clearLabel: Translator.trans('Clear'),
        });
    }
}

new Register().registerCallback(initColorPicker, 'initColorPicker');
