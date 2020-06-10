import Register from 'framework/common/utils/Register';

export default class VariantParameters {

    constructor ($container) {
        this.$container = $container;
        this.$variantParameterValuesCollection = $container.filterAllNodes('.js-variant-parameter');
        this.$variantParameterSelectedElementCollection = $container.filterAllNodes('.js-variant-parameter-selected');

        $container.filterAllNodes('.js-stop-propagation').click((event) => {
            return false;
        });

        $container.filterAllNodes('.js-variant-parameter-select').click((event) => {
            this.disableAllParameterValuesLists();
            $(event.currentTarget).next('.js-variant-parameter').show();
            return false;
        });

        $container.filterAllNodes('.js-variant-parameter-value').click((event) => {
            this.handleVariantParameterValue($(event.currentTarget));
        });
    }

    handleVariantParameterValue ($currentParameterValue) {
        const parameterValueHtmlContent = $currentParameterValue.html();
        const $variantParameterSelectedElement = $currentParameterValue
            .closest('.js-variant-parameter')
            .prev('.js-variant-parameter-select')
            .children('.js-variant-parameter-selected');

        $variantParameterSelectedElement
            .html(parameterValueHtmlContent);

        this.disableAllParameterValuesLists();
    }

    redirectToVariantByParameterValues () {
        console.log(this.$variantParameterSelectedElementCollection);
    }

    disableAllParameterValuesLists () {
        this.$variantParameterValuesCollection.hide();
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new VariantParameters($container);
    }
}

(new Register()).registerCallback(VariantParameters.init, 'VariantParameters.init');
