import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProducts } from 'connectors/products/Products';
import { FC } from 'react';

const PromotedProducts: FC = () => {
    const promotedProducts = usePromotedProducts();

    if (promotedProducts !== undefined) {
        return <ProductsSlider products={promotedProducts} />;
    }

    return null;
};

export default PromotedProducts;
