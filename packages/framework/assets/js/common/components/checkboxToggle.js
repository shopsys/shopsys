import Register from '../utils/register';

export default class CheckboxToggle {

    constructor ($container) {
        const $checkboxToggles = $container.filterAllNodes('.js-checkbox-toggle');

        $checkboxToggles.on('change', (event) => _this.onChange(event));

        const _this = this;
        $checkboxToggles.each(function (idx, elements) {
            const $checkboxToggle = $(elements);
            const $checkboxContainer = _this.findContainer($checkboxToggle);

            let show = $checkboxToggle.is(':checked');
            if ($checkboxToggle.hasClass('js-checkbox-toggle--inverted')) {
                show = !show;
            }

            if (show) {
                $checkboxContainer.show();
            } else {
                $checkboxContainer.hide();
            }
        });
    }

    onChange (event) {
        const $checkboxToggle = $(event.currentTarget);
        const $container = this.findContainer($checkboxToggle);

        let show = $checkboxToggle.is(':checked');
        if ($checkboxToggle.hasClass('js-checkbox-toggle--inverted')) {
            show = !show;
        }

        if (show) {
            $container.slideDown('fast');
        } else {
            $container.slideUp('fast');
        }
    }

    findContainer ($checkboxToggle) {
        if ($checkboxToggle.data('checkbox-toggle-container-id')) {
            return $('#' + $checkboxToggle.data('checkbox-toggle-container-id'));
        }

        return $('.' + $checkboxToggle.data('checkbox-toggle-container-class'));
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new CheckboxToggle($container);
    }
}

(new Register()).registerCallback(CheckboxToggle.init);
