import { ProductsSlider } from './ProductsSlider';
import { SkeletonModulePromotedProducts } from 'components/Blocks/Skeleton/SkeletonModulePromotedProducts';
import { TIDs } from 'cypress/tids';
import { usePromotedProductsQueryApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData, fetching }] = usePromotedProductsQueryApi();

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
