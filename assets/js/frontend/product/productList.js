import 'jquery.cookie';
import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import AjaxMoreLoader from '../components/AjaxMoreLoader';
import Register from 'framework/common/utils/Register';
import Translator from 'bazinga-translator';
import getBaseUrl from '../utils/getBaseUrl';
import pushReloadState from '../components/pushReloadState';

export default class ProductList {
    constructor ($container) {
        const _this = this;
        this.$productsWithControls = $container.filterAllNodes('.js-product-list-ajax-filter-products-with-controls');

        $container.filterAllNodes('.js-product-list-ordering-mode').click(function () {
            const cookieName = $(this).data('cookie-name');
            var orderingName = $(this).data('ordering-mode');

            if (orderingName == 'priority') {
                orderingName = null;
            }

            $.cookie(cookieName, orderingName, { path: '/' });

            _this.reloadWithAjax(_this);

            return false;
        });

        $container.filterAllNodes('.js-product-list-with-paginator').each(function () {
            // eslint-disable-next-line no-new
            new AjaxMoreLoader($(this), {
                buttonTextCallback: function (loadNextCount) {
                    return Translator.transChoice(
                        '{1}Load next %loadNextCount% product|[2,Inf]Load next %loadNextCount% products',
                        loadNextCount,
                        { 'loadNextCount': loadNextCount }
                    );
                }
            });
        });
    }

    reloadWithAjax (productList) {
        let url = null;
        let queryData = '';

        const $productFilterForm = $('form[name="product_filter_form"]');
        if ($productFilterForm.length > 0) {
            url = $('.js-ready-category-seo-mix-values').data('category-url');
            queryData = $productFilterForm.serialize()
                .replace(/(&|^)product_filter_form%5BminimalPrice%5D=(&|$)/g, '$2')
                .replace(/(&|^)product_filter_form%5BmaximalPrice%5D=(&|$)/g, '$2');
        } else {
            let urlObject = new URL(location.href);
            let params = new URLSearchParams(urlObject.search.slice(1));

            params.delete('page');
            queryData = params.toString();
        }

        url = url || getBaseUrl();

        Ajax.ajax({
            overlayDelay: 0,
            url: url,
            data: queryData,
            success: function (data) {
                const $wrappedData = $($.parseHTML('<div>' + data + '</div>'));
                productList.showProducts($wrappedData);
                productList.updateFilterLinks($wrappedData);
                if ($wrappedData.filterAllNodes('.js-ready-category-seo-mix-values').length === 0) {
                    pushReloadState(url + (queryData ? '?' : '') + queryData);
                }
            }
        });
    }

    updateFilterLinks ($wrappedData) {
        const $existingLinksElements = $('.js-product-filter-links');
        const $newLinksElements = $wrappedData.find('.js-product-filter-links');

        $newLinksElements.each((index, element) => {
            const $newLinkElement = $(element);
            const $existingLinkElement = $existingLinksElements
                .filter('[data-link-id="' + $newLinkElement.data('link-id') + '"]');

            $existingLinkElement.attr('href', $newLinkElement.attr('href'));
        });
    }

    showProducts ($wrappedData) {
        const $productsHtml = $wrappedData.find('.js-product-list-ajax-filter-products-with-controls');
        this.$productsWithControls.html($productsHtml.html());
        this.$productsWithControls.show();

        (new Register()).registerNewContent(this.$productsWithControls);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new ProductList($container);
    }
}

(new Register()).registerCallback(ProductList.init);
