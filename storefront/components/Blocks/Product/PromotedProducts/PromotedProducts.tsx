import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProducts } from 'connectors/products/Products';
import { FC } from 'react';

const GTM_LIST_NAME = 'homepage promo products' as const;

export const PromotedProducts: FC = () => {
    const promotedProducts = usePromotedProducts();

    if (promotedProducts === undefined) {
        return null;
    }

    return <ProductsSlider products={promotedProducts} gtmListName={GTM_LIST_NAME} />;
};
