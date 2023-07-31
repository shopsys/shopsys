import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProductsQueryApi } from 'graphql/generated';

import { GtmProductListNameType } from 'types/gtm/enums';

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData }] = usePromotedProductsQueryApi();

    if (promotedProductsData?.promotedProducts === undefined) {
        return null;
    }

    return (
        <ProductsSlider
            products={promotedProductsData.promotedProducts}
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
        />
    );
};
