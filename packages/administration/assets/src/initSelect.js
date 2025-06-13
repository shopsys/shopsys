import Register from 'framework/common/utils/Register';
import TomSelect from 'tom-select';
import Translator from 'bazinga-translator';

export function initSelect ($container) {
    $container.filterAllNodes('select').each((key, el) => {
        const settings = {
            allowEmptyOption: true,
            plugins: {
                dropdown_input: {},
                no_backspace_delete: {},
                no_active_items: {},
            }
        };

        if (el.hasAttribute('multiple')) {
            // @todo not translated
            settings.plugins.remove_button = { title: Translator.trans('Remove') };
        }

        void new TomSelect(el, settings);
    });
}

new Register().registerCallback(initSelect, 'initSelect');
