import ToggleOption from './toggleOption';
import Register from '../../common/register';

export default class SelectToggle{

    constructor($container) {
        this.optionClassPrefix = 'js-select-toggle-option-';

        const $selects = $container.filterAllNodes('.js-toggle-opt-group');

        if ($selects.length > 0) {
            const _this = this;
            $selects.each((index, element) => {
                _this.toggleOptgroupOnControlChange($(element));
            });
        }
    }

    toggleOptgroupOnControlChange ($select) {
        this.setOptgroupClassByLabel($select, this.optionClassPrefix);

        const $control = $($select.data('js-toggle-opt-group-control'));

        if ($control.length > 0) {
            const _this = this;
            $control
                .bind('change.selectToggle', function () {
                    _this.showOptionsBySelector($select, '.' + _this.optionClassPrefix + $control.val());
                })
                .trigger('change.selectToggle');
        }
    }

    setOptgroupClassByLabel ($select, classPrefix) {
        $select.find('optgroup').each((index, element) => {
            const $optgroup = $(element);
            const optgroupLabel = $optgroup.attr('label');
            $optgroup.find('option').each((index, element) => {
                $(element)
                    .addClass(classPrefix + optgroupLabel)
                    .appendTo($select);
            });

            $optgroup.remove();
        });
    }

    showOptionsBySelector ($select, optionSelector) {
        $select.find('option').each((index, element) => {
            if ($(element).is(optionSelector)) {
                ToggleOption.show($(element));
            } else {
                ToggleOption.hide($(element));
                $(element).removeAttr('selected');
            }
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new SelectToggle($container);
    }
}

(new Register().registerCallback(SelectToggle.init));
