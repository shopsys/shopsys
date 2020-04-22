import 'jquery.cookie';
import 'framework/common/components';
import AjaxMoreLoader from '../components/AjaxMoreLoader';
import Register from 'framework/common/utils/Register';
import Translator from 'bazinga-translator';

export default class ProductList {
    static init ($container) {
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

            location.reload(true);

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
}

(new Register()).registerCallback(ProductList.init);
