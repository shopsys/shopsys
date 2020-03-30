import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';

class MaximumOrderQuantity {
    static init () {
        const input = $('input.js-spinbox-input');

        input.on('keyup', function (e) {
            const spinboxMax = $(this).data('spinbox-max');

            if ($(this).val() > spinboxMax) {
                // eslint-disable-next-line no-new
                new Window({
                    content: 'Více zboží nemáme na skladě',
                    buttonContinue: false
                });
                if (spinboxMax == 0) {
                    $(this).val(1);
                } else {
                    $(this).val(spinboxMax);
                }
            }
        });
    }
}

(new Register()).registerCallback(MaximumOrderQuantity.init, 'MaximumOrderQuantity.init');
