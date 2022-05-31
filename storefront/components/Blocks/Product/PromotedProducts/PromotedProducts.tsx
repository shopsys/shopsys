import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProducts } from 'connectors/products/Products';
import { useGtmSliderProductListView } from 'hooks/gtm/useGtmSliderProductListView';
import { FC } from 'react';

const GTM_LIST_NAME = 'homepage promo products' as const;

const PromotedProducts: FC = () => {
    const promotedProducts = usePromotedProducts();
    useGtmSliderProductListView(promotedProducts, GTM_LIST_NAME);

    if (promotedProducts !== undefined) {
        return <ProductsSlider products={promotedProducts} gtmListName={GTM_LIST_NAME} />;
    }

    return null;
};

export default PromotedProducts;
