import $ from 'jquery';
import 'jquery-ui/slider';

import { parseNumber, formatDecimalNumber } from '../copyFromFw/common/number';
import Register from '../copyFromFw/register';

export default class RangeSlider {

    constructor ($sliderElement) {
        this.$sliderElement = $sliderElement;
        this.$minimumInput = $('#' + this.$sliderElement.data('minimumInputId'));
        this.$maximumInput = $('#' + this.$sliderElement.data('maximumInputId'));
        this.minimalValue = parseNumber(this.$sliderElement.data('minimalValue'));
        this.maximalValue = parseNumber(this.$sliderElement.data('maximalValue'));
        this.steps = 100;
    }

    static updateSliderMinimum (rangeSlider) {
        const value = parseNumber(rangeSlider.$minimumInput.val()) || rangeSlider.minimalValue;
        const step = rangeSlider.getStepFromValue(value);
        rangeSlider.$sliderElement.slider('values', 0, step);
    }

    static updateSliderMaximum (rangeSlider) {
        const value = parseNumber(rangeSlider.$maximumInput.val()) || rangeSlider.maximalValue;
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
                    rangeSlider.$minimumInput.val(minimumSliderValue !== rangeSlider.minimalValue ? formatDecimalNumber(minimumSliderValue, 2) : '');
                    rangeSlider.$maximumInput.val(maximumSliderValue !== rangeSlider.maximalValue ? formatDecimalNumber(maximumSliderValue, 2) : '');
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

(new Register()).registerCallback(RangeSlider.init);
