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
            const forceCookieName = 'force-' + cookieName;
            const orderingName = $(this).data('ordering-mode');
            const isReadyCategorySeoMixPage = $(this).data('is-ready-category-seo-mix-page');

            if (isReadyCategorySeoMixPage) {
                $.cookie(forceCookieName, orderingName, {
                    path: location.pathname,
                    expires: 1
                });

            } else {
                $.cookie(cookieName, orderingName, { path: '/' });
            }

            let url = new URL(location.href);
            let params = new URLSearchParams(url.search.slice(1));

            params.delete('page');
            url = getBaseUrl() + '?' + params.toString();
            pushReloadState(url);

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
        Ajax.ajax({
            overlayDelay: 0,
            url: location.href,
            success: function (data) {
                const $wrappedData = $($.parseHTML('<div>' + data + '</div>'));
                productList.showProducts($wrappedData);
            }
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
