import { ProductsSlider } from './ProductsSlider';
import { SkeletonModulePromotedProducts } from 'components/Blocks/Skeleton/SkeletonModulePromotedProducts';
import { TIDs } from 'cypress/tids';
import { usePromotedProductsQuery } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { GtmProductListNameType } from 'gtm/types/enums';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData, fetching }] = usePromotedProductsQuery();

    if (fetching) {
        return <SkeletonModulePromotedProducts />;
    }

    if (!promotedProductsData?.promotedProducts) {
        return null;
    }

    return (
        <ProductsSlider
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
            products={promotedProductsData.promotedProducts}
            tid={TIDs.blocks_product_slider_promoted_products}
        />
    );
};
