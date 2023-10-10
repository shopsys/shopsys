import { SkeletonPromotedProducts } from 'components/Blocks/Skeleton/SkeletonPromotedProducts';
import { ProductsSlider } from './ProductsSlider';
import { usePromotedProductsQueryApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';

const TEST_IDENTIFIER = 'blocks-product-slider-promoted-products';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData, fetching }] = usePromotedProductsQueryApi();

    if (fetching) {
        return <SkeletonPromotedProducts />;
    }

    if (!promotedProductsData?.promotedProducts) {
        return null;
    }

    return (
        <ProductsSlider
            dataTestId={TEST_IDENTIFIER}
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
            products={promotedProductsData.promotedProducts}
        />
    );
};
