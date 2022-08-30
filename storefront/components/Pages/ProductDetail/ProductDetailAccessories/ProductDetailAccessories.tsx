import { Heading } from 'components/Basic/Heading/Heading';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { SliderProductItemType } from 'types/product';

type ProductDetailAccessoriesProps = {
    accessories: SliderProductItemType[];
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
