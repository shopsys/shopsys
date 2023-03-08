import { Heading } from 'components/Basic/Heading/Heading';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

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
            <ProductsSlider products={accessories} gtmListName="accessory" />
        </>
    );
};
