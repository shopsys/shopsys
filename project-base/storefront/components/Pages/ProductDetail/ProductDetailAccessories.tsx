import { Heading } from 'components/Basic/Heading/Heading';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { ListedProductFragmentApi } from 'graphql/requests/products/fragments/ListedProductFragment.generated';

type ProductDetailAccessoriesProps = {
    accessories: ListedProductFragmentApi[];
};

export const ProductDetailAccessories: FC<ProductDetailAccessoriesProps> = ({ accessories }) => {
    const t = useTypedTranslationFunction();

    if (accessories.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h2">{t('You can also buy')}</Heading>
            <ProductsSlider
                products={accessories}
                gtmProductListName={GtmProductListNameType.product_detail_accessories}
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
            />
        </>
    );
};
