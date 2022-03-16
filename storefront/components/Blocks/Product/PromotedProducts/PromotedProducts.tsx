import { FC } from 'react';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProducts } from 'connectors/products/Products';

const PromotedProducts: FC = () => {
    const promotedProducts = usePromotedProducts();

    if (promotedProducts !== undefined) {
        return <ProductsSlider products={promotedProducts} />;
    }

    return null;
};

export default PromotedProducts;
