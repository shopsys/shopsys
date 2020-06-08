import Register from 'framework/common/utils/Register';

export default class VariantParameters {

    constructor ($container) {
        const _this = this;
        this.$variantParameterValuesCollection = $container.filterAllNodes('.js-variant-parameter');

        $container.filterAllNodes('.js-variant-parameter-select').click((event) => {
            _this.disableAllParameterValuesLists(_this.$variantParameterValuesCollection);
            $(event.currentTarget).next('.js-variant-parameter').show();
            return false;
        });

        $container.filterAllNodes('.js-variant-parameter-value').click((event) => {
            _this.handleVariantParameterValue($(event.currentTarget));
            _this.disableAllParameterValuesLists(_this.$variantParameterValuesCollection);
            return false;
        });
    }

    handleVariantParameterValue ($currentParameterValue) {
        const parameterValueHtmlContent = $currentParameterValue.html();
        $currentParameterValue
            .closest('.js-variant-parameter')
            .prev('.js-variant-parameter-select')
            .children('.js-variant-parameter-selected')
            .html(parameterValueHtmlContent);
    }

    disableAllParameterValuesLists ($variantParameterValuesList) {
        $variantParameterValuesList.hide();
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new VariantParameters($container);
    }
}

(new Register()).registerCallback(VariantParameters.init, 'VariantParameters.init');
