import { ProductsSlider } from './ProductsSlider';
import { TIDs } from 'cypress/tids';
import { usePromotedProductsQuery } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
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
    const [{ data: promotedProductsData, fetching: arePromotedProductsFetching }] = usePromotedProductsQuery();
    const shouldRender = useDeferredRender('promoted_products');

    if (arePromotedProductsFetching) {
        return <SkeletonModulePromotedProducts />;
    }

    if (!promotedProductsData?.promotedProducts) {
        return null;
    }

    return shouldRender ? (
        <ProductsSlider
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
            products={promotedProductsData.promotedProducts}
            tid={TIDs.blocks_product_slider_promoted_products}
        />
    ) : (
        <ProductsSliderPlaceholder products={promotedProductsData.promotedProducts} />
    );
};
