import { FC } from 'react';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import ShopsysHeading from 'components/Basic/Heading';
import { SliderProductItemType } from 'components/Blocks/Product/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAccessoriesProps = {
    accessories: SliderProductItemType[];
};

const ProductDetailAccessories: FC<ProductDetailAccessoriesProps> = (props) => {
    const t = useTypedTranslationFunction();

    if (props.accessories.length === 0) {
        return null;
    }

    return (
        <>
            <ShopsysHeading type="h2">{t('You can also buy')}</ShopsysHeading>
            <ProductsSlider products={props.accessories} />
        </>
    );
};

export default ProductDetailAccessories;
