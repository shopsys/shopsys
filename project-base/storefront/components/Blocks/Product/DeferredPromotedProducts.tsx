import { ProductsSlider } from './ProductsSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { usePromotedProductsQuery } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const SkeletonModulePromotedProducts = dynamic(() =>
    import('components/Blocks/Skeleton/SkeletonModulePromotedProducts').then(
        (component) => component.SkeletonModulePromotedProducts,
    ),
);

const ProductsSliderPlaceholder = dynamic(() =>
    import('./ProductsSliderPlaceholder').then((component) => component.ProductsSliderPlaceholder),
);

export const DeferredPromotedProducts: FC = () => {
    const { t } = useTranslation();
    const [{ data: promotedProductsData, fetching: arePromotedProductsFetching }] = usePromotedProductsQuery();
    const shouldRender = useDeferredRender('promoted_products');

    if (!promotedProductsData?.promotedProducts.length) {
        return null;
    }

    return (
        <Webline className="mb-10">
            {arePromotedProductsFetching ? (
                <SkeletonModulePromotedProducts />
            ) : (
                <>
                    <h3 className="mb-4">{t('News on offer')}</h3>
                    {shouldRender ? (
                        <ProductsSlider
                            gtmProductListName={GtmProductListNameType.homepage_promo_products}
                            products={promotedProductsData.promotedProducts}
                            tid={TIDs.blocks_product_slider_promoted_products}
                        />
                    ) : (
                        <ProductsSliderPlaceholder products={promotedProductsData.promotedProducts} />
                    )}
                </>
            )}
        </Webline>
    );
};
