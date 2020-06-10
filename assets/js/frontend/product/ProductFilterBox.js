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

        $container.filterAllNodes('.js-product-filter-close-parameter').each(function () {
            let parameterValuesCheckedCount = $(this).find('input[type="checkbox"]:checked').length;
            if (parameterValuesCheckedCount === 0) {
                _this.toggleFilterBox($(this).closest('.js-product-filter-box'));
            }
        });

        $container.filterAllNodes('.js-product-filter-show-more-less').on('click', event => {
            event.preventDefault();
            _this.initFilterParamsToogle($(event.target));
        });

        $container.filterAllNodes('.js-form-choice-color').each(function () {
            _this.checkFilterParamColorBrightness($(this));
        });
    }

    checkFilterParamColorBrightness ($param) {
        let bgColor = $param.css('background-color');
        let r = 0;
        let g = 0;
        let b = 0;
        let hsp = 0;

        bgColor = bgColor.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
        r = bgColor[1];
        g = bgColor[2];
        b = bgColor[3];

        hsp = Math.sqrt(
            0.299 * (r * r)
            + 0.587 * (g * g)
            + 0.114 * (b * b)
        );

        (hsp > 200)
            ? $param.addClass('is-light-bg')
            : $param.addClass('is-dark-bg');
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
