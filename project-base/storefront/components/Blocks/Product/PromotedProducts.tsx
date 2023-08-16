import { usePromotedProductsQueryApi } from 'graphql/generated';
import { ProductsSlider } from './ProductsSlider';
import { GtmProductListNameType } from 'gtm/types/enums';

const TEST_IDENTIFIER = 'blocks-product-slider-promoted-products';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData }] = usePromotedProductsQueryApi();

    if (!promotedProductsData?.promotedProducts) {
        return null;
    }

    return (
        <ProductsSlider
            products={promotedProductsData.promotedProducts}
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
            dataTestId={TEST_IDENTIFIER}
        />
    );
};
