import $ from 'jquery';
import noUiSlider from 'nouislider';
import Register from '../../common/utils/Register';

export default class NumberSlider {
    constructor($sliderContainer) {
        const sliderElement = $sliderContainer.children('.js-number-slider-slider')[0];
        const inputElement = $sliderContainer.find('.js-number-slider-input')[0];

        const initialValue = parseFloat(inputElement.value.replace(',', '.')) || 0;

        noUiSlider.create(sliderElement, {
            start: [initialValue],
            step: 0.01,
            range: {
                min: 0,
                max: 1,
            },
        });

        sliderElement.noUiSlider.on('update', (values, handle) => {
            inputElement.value = values[handle];
        });

        inputElement.addEventListener('change', () => {
            sliderElement.noUiSlider.set([parseFloat(inputElement.value.replace(',', '.')) || 0]);
        });
    }

    static init($container) {
        $container.filterAllNodes('.js-number-slider').each(function () {
            // eslint-disable-next-line no-new
            new NumberSlider($(this));
        });
    }
}

new Register().registerCallback(NumberSlider.init, 'NumberSlider.init');
