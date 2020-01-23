import Register from 'framework/common/utils/register';

export default class ProductFilterBox {

    constructor ($container) {
        $container.filterAllNodes('.js-product-filter-open-button').click(event => {
            $(event.target).toggleClass('active');
            $container.filterAllNodes('.js-product-filter').toggleClass('active');
        });

        const _this = this;
        $container.filterAllNodes('.js-product-filter-box-arrow').on('click', event => {
            _this.toggleFilterBox($(event.target).closest('.js-product-filter-box'));
        });
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

(new Register()).registerCallback(ProductFilterBox.init);
