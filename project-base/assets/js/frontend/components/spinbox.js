import Repeater from './Repeater';
import Register from 'framework/common/utils/Register';

export default class Spinbox {

    static bindSpinbox () {
        const $input = $(this).find('input.js-spinbox-input');
        const $plus = $(this).find('.js-spinbox-plus');
        const $minus = $(this).find('.js-spinbox-minus');
        const repeater = new Repeater();

        $input
            .on('spinbox.plus', Spinbox.plus)
            .on('spinbox.minus', Spinbox.minus);

        $plus
            .on('mousedown.spinbox', function (e) {
                repeater.startAutorepeat($input, 'spinbox.plus');
            })
            .on('mouseup.spinbox mouseout.spinbox', function (e) {
                repeater.stopAutorepeat();
            });

        $minus
            .on('mousedown.spinbox', function (e) {
                repeater.startAutorepeat($input, 'spinbox.minus');
            })
            .on('mouseup.spinbox mouseout.spinbox', function (e) {
                repeater.stopAutorepeat();
            });

    }

    static plus () {
        Spinbox.changeValue($(this), '+');
    }

    static minus () {
        Spinbox.changeValue($(this), '-');
    }

    static changeValue (input, action) {
        let value = $.trim(input.val());
        const min = input.data('spinbox-min');
        const max = input.data('spinbox-max');

        if (value.match(/^\d+$/)) {
            value = parseInt(value);

            if (action === '+') {
                value += 1;
            } else {
                value -= 1;
            }

            if (min !== undefined && min > value) {
                value = min;
            }

            if (max !== undefined && max < value) {
                value = max;
            }

            input.val(value);
            input.change();
        }
    }

    static init ($container) {
        $container.filterAllNodes('.js-spinbox').each(Spinbox.bindSpinbox);
    }
}

(new Register()).registerCallback(Spinbox.init, 'Spinbox.init');
