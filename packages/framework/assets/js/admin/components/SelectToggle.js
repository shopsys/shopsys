import Register from '../../common/utils/Register';

export default class SelectToggle {

    constructor ($container) {
        this.optionClassPrefix = 'js-select-toggle-option-';

        const $selects = $container.filterAllNodes('.js-toggle-opt-group');

        if ($selects.length > 0) {
            $selects.each((index, element) => {
                this.toggleOptgroupOnControlChange($(element));
            });
        }
    }

    toggleOptgroupOnControlChange ($select) {
        const $control = $($select.data('js-toggle-opt-group-control'));

        if ($control.length > 0) {
            $control
                .on('change', event => {
                    this.showOptionsBySelector($select, '.' + this.optionClassPrefix + event.target.value);
                    $select.val($select.find('option:not([disabled]):first').val()).change();
                });
        }
    }

    showOptionsBySelector ($select, optionSelector) {
        $select.find('option').each((index, element) => {
            if ($(element).is(optionSelector)) {
                $(element).prop('disabled', false);
            } else {
                $(element).prop('disabled', true);
            }
        });
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new SelectToggle($container);
    }
}

(new Register().registerCallback(SelectToggle.init, 'SelectToggle.init'));
