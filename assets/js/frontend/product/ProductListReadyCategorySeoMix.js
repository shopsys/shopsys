import Register from 'framework/common/utils/Register';
import pushReloadState from '../components/pushReloadState';

export default class ProductListReadyCategorySeoMix {

    static init ($container) {

        $container.filterAllNodes('.js-ready-category-seo-mix-values').each(function () {
            const $elementWithValues = $(this);

            ProductListReadyCategorySeoMix.setSeoPropertiesToProperElements(
                $elementWithValues.attr('data-seo-h1'),
                $elementWithValues.attr('data-seo-description'),
                $elementWithValues.attr('data-seo-short-description'),
                $elementWithValues.attr('data-seo-title'),
                $elementWithValues.attr('data-seo-meta-description'),
                $elementWithValues.data('url')
            );
        });
    }

    static setSeoPropertiesToProperElements (h1, description, shortDescription, seoTitle, seoMetaDescription, url) {
        $('.js-ready-category-seo-mix-product-list-h1').text(h1);
        $('.js-ready-category-seo-mix-product-list-description').html(description);
        const $shortDescription = $('.js-ready-category-seo-mix-product-list-short-description');
        $shortDescription.html(shortDescription);
        if (shortDescription == '') {
            $shortDescription.siblings('.btn--next').hide();
        } else {
            $shortDescription.siblings('.btn--next').show();
        }
        $(document).attr('title', seoTitle);
        $('meta[name=description]').attr('content', seoMetaDescription);
        pushReloadState(url);
    }
}

(new Register()).registerCallback(ProductListReadyCategorySeoMix.init, 'ProductListReadyCategorySeoMix.init');
