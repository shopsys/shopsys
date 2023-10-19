import { ProductsSlider } from './ProductsSlider';
import { usePromotedProductsQueryApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';

const TEST_IDENTIFIER = 'blocks-product-slider-promoted-products';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData }] = usePromotedProductsQueryApi();

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
