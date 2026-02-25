import { ProductsSliderPlaceholder } from './ProductsSliderPlaceholder';
import { SkeletonModulePromotedProducts } from 'components/Blocks/Skeleton/SkeletonModulePromotedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { usePromotedProductsQuery } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductsSlider = dynamic(() => import('./ProductsSlider').then((component) => component.ProductsSlider), {
    ssr: false,
});

export const DeferredPromotedProducts: FC = () => {
    const { t } = useTranslation();
    const [{ data: promotedProductsData, fetching: arePromotedProductsFetching }] = usePromotedProductsQuery();
    const shouldRender = useDeferredRender('promoted_products');

    if (arePromotedProductsFetching) {
        return (
            <Webline>
                <SkeletonModulePromotedProducts />
            </Webline>
        );
    }

    if (!promotedProductsData?.promotedProducts.length) {
        return null;
    }

    return (
        <Webline>
            <h2 className="h3 mb-3">{t('News on offer')}</h2>

            {shouldRender ? (
                <ProductsSlider
                    ariaAnchorName="product-slider-promoted"
                    gtmProductListName={GtmProductListNameType.homepage_promo_products}
                    products={promotedProductsData.promotedProducts}
                    tid={TIDs.blocks_product_slider_promoted_products}
                />
            ) : (
                <ProductsSliderPlaceholder products={promotedProductsData.promotedProducts} />
            )}
        </Webline>
    );
};
