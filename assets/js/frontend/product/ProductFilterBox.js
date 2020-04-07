import Register from 'framework/common/utils/Register';

export default class ProductFilterBox {

    constructor ($container) {
        $container.filterAllNodes('.js-product-filter-open-button').click(event => {
            $(event.target).toggleClass('active');
            $container.filterAllNodes('.js-product-filter').toggleClass('active');
        });

        $container.filterAllNodes('.js-product-sort-open-button').click(event => {
            $(event.target).toggleClass('active');
            $container.filterAllNodes('.js-product-sort').toggleClass('active');
        });

        const _this = this;
        $container.filterAllNodes('.js-product-filter-box-arrow').on('click', event => {
            _this.toggleFilterBox($(event.target).closest('.js-product-filter-box'));
        });

        $container.filterAllNodes('.js-product-filter-show-more-less').on('click', event => {
            event.preventDefault();
            _this.initFilterParamsToogle($(event.target));
        });
    }

    initFilterParamsToogle ($toggleButton) {
        const toggleText = $toggleButton.hasClass('is-active')
            ? $toggleButton.data('text-more')
            : $toggleButton.data('text-less');

        $toggleButton
            .text(toggleText)
            .toggleClass('is-active');

        $toggleButton
            .closest('.js-product-filter-box')
            .find('.js-form-choice--collapsing')
            .toggleClass('display-none');
    }

    toggleFilterBox ($parameterContainer) {
        const $productFilterParameterLabel = $parameterContainer.find('.js-product-filter-box-label');
        $productFilterParameterLabel.toggleClass('active');

        const parameterFilterFormId = $parameterContainer.data('product-filter-box-id');

        if ($productFilterParameterLabel.hasClass('active')) {
            $parameterContainer.find('#' + parameterFilterFormId).slideDown('fast');
        } else {
            $parameterContainer.find('#' + parameterFilterFormId).slideUp('fast');
        }
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new ProductFilterBox($container);
    }
}

(new Register()).registerCallback(ProductFilterBox.init, 'ProductFilterBox.init');
