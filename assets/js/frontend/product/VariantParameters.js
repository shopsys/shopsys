import Register from 'framework/common/utils/Register';

export default class VariantParameters {

    constructor ($container) {
        this.$container = $container;
        this.$variantParameterValuesCollection = $container.filterAllNodes('.js-variant-parameter');

        $container.filterAllNodes('.js-stop-propagation').click((event) => {
            return false;
        });

        $container.filterAllNodes('.js-variant-parameter-select').click((event) => {
            if ($(event.currentTarget).next('.js-variant-parameter').is(':hidden')) {
                this.disableAllParameterValuesLists();
                $(event.currentTarget).next('.js-variant-parameter').slideDown('fast');
                $(event.currentTarget).addClass('opened');
                return false;
            } else {
                $(event.currentTarget).next('.js-variant-parameter').slideUp('fast');
                $(event.currentTarget).addClass('opened');
                this.opened = false;
                return false;
            }
        });

        $container.filterAllNodes('.js-variant-parameter-value').click((event) => {
            this.handleVariantParameterValue($(event.currentTarget));
        });

        $(document).click((event) => {
            if (!$(event.target).closest(this.$variantParameterValuesCollection).length) {
                this.disableAllParameterValuesLists();
            }
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

    disableAllParameterValuesLists () {
        this.$variantParameterValuesCollection.slideUp('fast');
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new VariantParameters($container);
    }
}

(new Register()).registerCallback(VariantParameters.init, 'VariantParameters.init');
