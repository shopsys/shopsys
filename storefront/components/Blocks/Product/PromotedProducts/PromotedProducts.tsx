import { FC } from 'react';
import { getPromotedProducts } from 'connectors/products/Products';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';

const PromotedProducts: FC = () => {
    const promotedProducts = getPromotedProducts();

    if (promotedProducts !== undefined) {
        return <ProductsSlider products={promotedProducts} />;
    }

    return null;
};

export default PromotedProducts;
