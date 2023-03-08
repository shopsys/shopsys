import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { usePromotedProductsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

const GTM_LIST_NAME = 'homepage promo products' as const;

export const PromotedProducts: FC = () => {
    const [{ data: promotedProductsData }] = useQueryError(usePromotedProductsQueryApi());

    if (promotedProductsData?.promotedProducts === undefined) {
        return null;
    }

    return <ProductsSlider products={promotedProductsData.promotedProducts} gtmListName={GTM_LIST_NAME} />;
};
