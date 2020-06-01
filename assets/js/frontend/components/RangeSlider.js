import 'jquery-ui/slider';

import { formatDecimalNumber } from 'framework/common/utils/number';
import parseNumberFixed from '../utils/numbers';
import Register from 'framework/common/utils/Register';

export default class RangeSlider {

    constructor ($sliderElement) {
        this.$sliderElement = $sliderElement;
        this.$minimumInput = $('#' + this.$sliderElement.data('minimumInputId'));
        this.$maximumInput = $('#' + this.$sliderElement.data('maximumInputId'));
        this.minimalValue = parseNumberFixed(this.$sliderElement.data('minimalValue'));
        this.maximalValue = parseNumberFixed(this.$sliderElement.data('maximalValue'));
        this.decimals = typeof this.$sliderElement.data('decimals') !== 'undefined' ? this.$sliderElement.data('decimals') : 2;
        this.steps = 100;
    }

    static updateSliderMinimum (rangeSlider) {
        let value = parseNumberFixed(rangeSlider.$minimumInput.val());
        const currentMaxValue = parseNumberFixed(rangeSlider.$maximumInput.val());

        if (value == null) {
            value = rangeSlider.minimalValue;
        }

        if (value > rangeSlider.maximalValue) {
            value = rangeSlider.maximalValue;
            rangeSlider.$minimumInput.val(value);
        }
        if (value < rangeSlider.minimalValue) {
            value = rangeSlider.minimalValue;
            rangeSlider.$minimumInput.val(value);
        }
        if (currentMaxValue != null && value > currentMaxValue) {
            value = currentMaxValue;
            rangeSlider.$minimumInput.val(value);
        }

        const step = rangeSlider.getStepFromValue(value);
        rangeSlider.$sliderElement.slider('values', 0, step);
    }

    static updateSliderMaximum (rangeSlider) {
        let value = parseNumberFixed(rangeSlider.$maximumInput.val());
        const currentMinValue = parseNumberFixed(rangeSlider.$minimumInput.val());

        if (value == null) {
            value = rangeSlider.maximalValue;
        }

        if (value > rangeSlider.maximalValue) {
            value = rangeSlider.maximalValue;
            rangeSlider.$maximumInput.val(value);
        }
        if (value < rangeSlider.minimalValue) {
            value = rangeSlider.minimalValue;
            rangeSlider.$maximumInput.val(value);
        }
        if (currentMinValue != null && value < currentMinValue) {
            value = currentMinValue;
            rangeSlider.$maximumInput.val(value);
        }

        const step = rangeSlider.getStepFromValue(value);
        rangeSlider.$sliderElement.slider('values', 1, step);
    }

    getStepFromValue (value) {
        return Math.round((value - this.minimalValue) / (this.maximalValue - this.minimalValue) * this.steps);
    }

    getValueFromStep (step) {
        return this.minimalValue + (this.maximalValue - this.minimalValue) * step / this.steps;
    }

    static init ($container) {
        $container.filterAllNodes('.js-range-slider').each(function () {
            let lastMinimumInputValue;
            let lastMaximumInputValue;

            const rangeSlider = new RangeSlider($(this));

            rangeSlider.$sliderElement.slider({
                range: true,
                min: 0,
                max: rangeSlider.steps,
                start: function () {
                    lastMinimumInputValue = rangeSlider.$minimumInput.val();
                    lastMaximumInputValue = rangeSlider.$maximumInput.val();
                },
                slide: function (event, ui) {
                    const minimumSliderValue = rangeSlider.getValueFromStep(ui.values[0]);
                    const maximumSliderValue = rangeSlider.getValueFromStep(ui.values[1]);
                    rangeSlider.$minimumInput.val(minimumSliderValue !== rangeSlider.minimalValue ? formatDecimalNumber(minimumSliderValue, rangeSlider.decimals) : '');
                    rangeSlider.$maximumInput.val(maximumSliderValue !== rangeSlider.maximalValue ? formatDecimalNumber(maximumSliderValue, rangeSlider.decimals) : '');
                },
                stop: function () {
                    if (lastMinimumInputValue !== rangeSlider.$minimumInput.val()) {
                        rangeSlider.$minimumInput.change();
                    }
                    if (lastMaximumInputValue !== rangeSlider.$maximumInput.val()) {
                        rangeSlider.$maximumInput.change();
                    }
                }
            });

            rangeSlider.$minimumInput.change(() => RangeSlider.updateSliderMinimum(rangeSlider));
            RangeSlider.updateSliderMinimum(rangeSlider);

            rangeSlider.$maximumInput.change(() => RangeSlider.updateSliderMaximum(rangeSlider));
            RangeSlider.updateSliderMaximum(rangeSlider);
        });
    }
}

(new Register()).registerCallback(RangeSlider.init, 'RangeSlider.init');
