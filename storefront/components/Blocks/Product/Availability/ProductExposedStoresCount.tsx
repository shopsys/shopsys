import { FC } from 'react';
import { ProductExposedStoreCountStyled } from './ProductExposedStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductExposedStoresCountProps = {
    isMainVariant: boolean;
    exposedStoresCount: number;
};

const ProductExposedStoresCount: FC<ProductExposedStoresCountProps> = (props) => {
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
