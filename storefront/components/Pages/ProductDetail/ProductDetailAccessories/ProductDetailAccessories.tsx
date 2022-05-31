import ShopsysHeading from 'components/Basic/Heading';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { SliderProductItemType } from 'types/product';

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
            <ProductsSlider products={props.accessories} gtmListName="accessory" />
        </>
    );
};

export default ProductDetailAccessories;
