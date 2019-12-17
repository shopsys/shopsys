import $ from 'jquery';
import Register from '../common/register';

export default class DynamicPlaceholder {

    constructor ($input) {
        this.$input = $input;
        this.$sourceInput = $('#' + $input.data('placeholder-source-input-id'));
        const _this = this;

        this.$sourceInput.change(() => DynamicPlaceholder.updatePlaceholder(_this));
        DynamicPlaceholder.updatePlaceholder(_this);
    }

    static updatePlaceholder (dynamicPlaceholder) {
        dynamicPlaceholder.$input.attr('placeholder', dynamicPlaceholder.$sourceInput.val());
        dynamicPlaceholder.$input.trigger('placeholderChange');
    }

    static init ($container) {
        $container.filterAllNodes('.js-dynamic-placeholder').each(function () {
            // eslint-disable-next-line no-new
            new DynamicPlaceholder($(this));
        });
    }
}

(new Register()).registerCallback(DynamicPlaceholder.init);
