import 'jquery-ui/ui/widgets/slider';
import Register from '../../common/utils/Register';

export default class NumberSlider {
    constructor ($sliderContainer) {
        $sliderContainer.children('.js-number-slider__slider').slider({
            min: 0,
            max: 1,
            step: 0.01,
            slide: function (event, ui) {
                $(this).next('.js-number-slider__input').val(ui.value);
            },
            create: function (event, ui) {
                $(this).slider(
                    'value',
                    $(this).next('.js-number-slider__input').val().replace(',', '.')
                );
            }
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-number-slider').each(function () {
            // eslint-disable-next-line no-new
            new NumberSlider($(this));
        });
    }
}

(new Register()).registerCallback(NumberSlider.init, 'NumberSlider.init');
