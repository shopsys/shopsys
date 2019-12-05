import $ from 'jquery';
import Repeater from './repeater';
import Register from '../copyFromFw/register';

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
        let value = $.trim($(this).val());
        let max = $(this).data('spinbox-max');

        if (value.match(/^\d+$/)) {
            value = parseInt(value) + 1;
            if (max !== undefined && max < value) {
                value = max;
            }
            $(this).val(value);
            $(this).change();
        }
    };

    static minus () {
        let value = $.trim($(this).val());
        let min = $(this).data('spinbox-min');

        if (value.match(/^\d+$/)) {
            value = parseInt(value) - 1;
            if (min !== undefined && min > value) {
                value = min;
            }
            $(this).val(value);
            $(this).change();
        }
    };

    static init ($container) {
        $container.filterAllNodes('.js-spinbox').each(Spinbox.bindSpinbox);
    }
}

(new Register()).registerCallback(Spinbox.init);
