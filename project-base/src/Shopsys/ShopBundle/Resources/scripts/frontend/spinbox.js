(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.spinbox = Shopsys.spinbox || {};

    Shopsys.spinbox.init = function ($container) {
        $container.filterAllNodes('.js-spinbox').each(Shopsys.spinbox.bindSpinbox);
    };

    Shopsys.spinbox.bindSpinbox = function () {
        var $input = $(this).find('input.js-spinbox-input');
        var $plus = $(this).find('.js-spinbox-plus');
        var $minus = $(this).find('.js-spinbox-minus');

        $input
            .bind('spinbox.plus', Shopsys.spinbox.plus)
            .bind('spinbox.minus', Shopsys.spinbox.minus);

        $plus
            .bind('mousedown.spinbox', function (e) {
                repeater.startAutorepeat($input, 'spinbox.plus');
            })
            .bind('mouseup.spinbox mouseout.spinbox', function (e) {
                repeater.stopAutorepeat();
            });

        $minus
            .bind('mousedown.spinbox', function (e) {
                repeater.startAutorepeat($input, 'spinbox.minus');
            })
            .bind('mouseup.spinbox mouseout.spinbox', function (e) {
                repeater.stopAutorepeat();
            });

    };

    Shopsys.spinbox.plus = function () {
        Shopsys.spinbox.changeValue($(this), '+');
    };

    Shopsys.spinbox.minus = function () {
        Shopsys.spinbox.changeValue($(this), '-');
    };

    Shopsys.spinbox.changeValue = function (input, action) {
        var value = $.trim(input.val());
        var min = input.data('spinbox-min');
        var max = input.data('spinbox-max');

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
    };

    var repeater = {
        timerDelay: null,
        timerRepeat: null,

        startAutorepeat: function ($input, eventString) {
            $input.trigger(eventString);
            repeater.stopAutorepeat();
            repeater.timerDelay = setTimeout(function () {
                $input.trigger(eventString);
                repeater.timerRepeat = setInterval(function () {
                    $input.trigger(eventString);
                }, 100);
            }, 500);
        },

        stopAutorepeat: function () {
            clearTimeout(repeater.timerDelay);
            clearInterval(repeater.timerRepeat);
        }
    };

    Shopsys.register.registerCallback(Shopsys.spinbox.init);

})(jQuery);
