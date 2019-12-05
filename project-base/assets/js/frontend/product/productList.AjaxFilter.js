import $ from 'jquery';
import { lazyLoadCall } from '../lazyLoadInit';
import Register from '../../copyFromFw/register';
import Ajax from '../../copyFromFw/ajax';
import getBaseUrl from '../url';
import pushReloadState from '../history';

export default class ProductListAjaxFilter {
    constructor () {
        this.$productsWithControls = $('.js-product-list-ajax-filter-products-with-controls');
        this.$productFilterForm = $('form[name="product_filter_form"]');
        this.$showResultsButton = $('.js-product-filter-show-result-button');
        this.$resetFilterButton = $('.js-product-filter-reset-button');
        this.requestTimer = null;
        this.requestDelay = 1000;

        const _this = this;
        this.$productFilterForm.change(function () {
            clearTimeout(this.requestTimer);
            _this.requestTimer = setTimeout(() => _this.submitFormWithAjax(_this), _this.requestDelay);
            pushReloadState(getBaseUrl() + '?' + _this.$productFilterForm.serialize());
        });

        this.$showResultsButton.click(function () {
            const $productList = $('.js-product-list');
            if ($productList && $productList.offset()) {
                $('html, body').animate({ scrollTop: $productList.offset().top }, 'slow');
            }
            return false;
        });

        this.$resetFilterButton.click(function () {
            _this.$productFilterForm
                .find(':radio, :checkbox').removeAttr('checked').end()
                .find('textarea, :text, select').val('');
            _this.$productFilterForm.find('.js-product-filter-call-change-after-reset').change();
            clearTimeout(this.requestTimer);
            const resetUrl = $(this).attr('href');
            pushReloadState(resetUrl);
            _this.submitFormWithAjax(_this);
            return false;
        });

        this.updateFiltersDisabled();
    }

    showProducts ($wrappedData) {
        const $productsHtml = $wrappedData.find('.js-product-list-ajax-filter-products-with-controls');
        this.$productsWithControls.html($productsHtml.html());
        this.$productsWithControls.show();

        lazyLoadCall(this.$productsWithControls);
        (new Register()).registerNewContent(this.$productsWithControls);
    };

    updateFiltersCounts ($wrappedData) {
        const $existingCountElements = $('.js-product-filter-count');
        const $newCountElements = $wrappedData.find('.js-product-filter-count');

        $newCountElements.each(function () {
            const $newCountElement = $(this);

            const $existingCountElement = $existingCountElements
                .filter('[data-form-id="' + $newCountElement.data('form-id') + '"]');

            $existingCountElement.html($newCountElement.html());
        });
    };

    updateFiltersDisabled () {
        $('.js-product-filter-count').each(function () {
            const $countElement = $(this);

            const $label = $countElement.closest('label');
            const $formElement = $('#' + $countElement.data('form-id'));

            if (ProductListAjaxFilter.willFilterZeroProducts($countElement)) {
                if (!$formElement.is(':checked')) {
                    $label.addClass('in-disable');
                    $formElement.prop('disabled', true);
                }
            } else {
                $label.removeClass('in-disable');
                $formElement.prop('disabled', false);
            }
        });
    };

    submitFormWithAjax (productListAjaxFilter) {
        Ajax.ajax({
            overlayDelay: 0,
            url: getBaseUrl(),
            data: productListAjaxFilter.$productFilterForm.serialize(),
            success: function (data) {
                const $wrappedData = $($.parseHTML('<div>' + data + '</div>'));

                productListAjaxFilter.showProducts($wrappedData);
                productListAjaxFilter.updateFiltersCounts($wrappedData);
                productListAjaxFilter.updateFiltersDisabled();
            }
        });
    };

    static willFilterZeroProducts ($countElement) {
        return $countElement.html().indexOf('(0)') !== -1;
    };

    static init () {
        // eslint-disable-next-line no-new
        new ProductListAjaxFilter();
    };
}

new Register().registerCallback(ProductListAjaxFilter.init);
