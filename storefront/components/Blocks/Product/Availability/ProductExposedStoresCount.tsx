import { FC } from 'react';
import { ProductExposedStoreCountStyled } from './ProductExposedStoresCount.style';
import { SliderProductItemType } from 'components/Blocks/Product/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductExposedStoresCount: FC<SliderProductItemType> = (props) => {
    const t = useTypedTranslationFunction();

    if (props.isMainVariant || props.exposedStoresCount === 0) {
        return null;
    }

    return (
        <ProductExposedStoreCountStyled>
            {t(
                '(1)[You can check this item in {{ count }} store];(2-inf)[You can check this item in {{ count }} stores];',
                { postProcess: 'interval', count: props.exposedStoresCount },
            )}
        </ProductExposedStoreCountStyled>
    );
};

export default ProductExposedStoresCount;
