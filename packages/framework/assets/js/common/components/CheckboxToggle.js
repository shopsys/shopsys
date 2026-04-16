import Register from '../utils/Register';

export default class CheckboxToggle {
    constructor($container) {
        const $checkboxToggles = $container.filterAllNodes('.js-checkbox-toggle');

        $checkboxToggles.on('change', event => this.onChange(event));
        $checkboxToggles.each((_idx, elements) => {
            const $checkboxToggle = $(elements);
            const $checkboxContainer = this.findContainer($checkboxToggle);
            this.applyState($checkboxToggle, $checkboxContainer, false);
        });
    }

    onChange(event) {
        const $checkboxToggle = $(event.currentTarget);
        const $container = this.findContainer($checkboxToggle);
        this.applyState($checkboxToggle, $container, true);
    }

    applyState($checkboxToggle, $container, withAnimation) {
        let show = $checkboxToggle.is(':checked');
        if ($checkboxToggle.hasClass('js-checkbox-toggle--inverted')) {
            show = !show;
        }

        if ($checkboxToggle.hasClass('js-checkbox-toggle--disable-container')) {
            this.toggleContainerEnabled($container, show);
            $container.show();

            return;
        }

        if (withAnimation) {
            if (show) {
                $container.slideDown('fast');
            } else {
                $container.slideUp('fast');
            }

            return;
        }

        if (show) {
            $container.show();
        } else {
            $container.hide();
        }
    }

    toggleContainerEnabled($container, enabled) {
        $container.find(':input').each((_index, element) => {
            const $element = $(element);
            $element.prop('disabled', !enabled);

            if (element.tomselect) {
                if (enabled) {
                    element.tomselect.enable();
                } else {
                    element.tomselect.close();
                    element.tomselect.disable();
                }
            }
        });
    }

    findContainer($checkboxToggle) {
        if ($checkboxToggle.data('checkbox-toggle-container-id')) {
            return $(`#${$checkboxToggle.data('checkbox-toggle-container-id')}`);
        }

        return $(`.${$checkboxToggle.data('checkbox-toggle-container-class')}`);
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new CheckboxToggle($container);
    }
}

new Register().registerCallback(CheckboxToggle.init, 'CheckboxToggle.init');
