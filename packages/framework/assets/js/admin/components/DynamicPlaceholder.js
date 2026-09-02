import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class DynamicPlaceholder {
    constructor($input) {
        this.$input = $input;
        this.$sourceInput = $(`#${$input.data('js-placeholder-source-input-id')}`);

        this.$sourceInput.change(() => DynamicPlaceholder.updatePlaceholder(this));
        DynamicPlaceholder.updatePlaceholder(this);
    }

    static updatePlaceholder(dynamicPlaceholder) {
        dynamicPlaceholder.$input.attr('placeholder', dynamicPlaceholder.$sourceInput.val());
        dynamicPlaceholder.$input.trigger('placeholderChange');
    }

    static init($container) {
        $container.filterAllNodes('[data-js-placeholder-source-input-id]').each(function () {
            // eslint-disable-next-line no-new
            new DynamicPlaceholder($(this));
        });
    }
}

new Register().registerCallback(DynamicPlaceholder.init, 'DynamicPlaceholder.init');
