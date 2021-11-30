import { FC } from 'react';
import { ProductAvailableStoreCountStyled } from './ProductAvailableStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductAvailableStoresCountProps = {
    isMainVariant: boolean;
    availableStoresCount: number;
};

const ProductAvailableStoresCount: FC<ProductAvailableStoresCountProps> = (props) => {
    const t = useTypedTranslationFunction();

    if (props.isMainVariant || props.availableStoresCount === 0) {
        return null;
    }

    return (
        <ProductAvailableStoreCountStyled>
            {t(
                '(1)[This item is available immediately in {{ count }} store];(2-inf)[This item is available immediately in {{ count }} stores];',
                { postProcess: 'interval', count: props.availableStoresCount },
            )}
        </ProductAvailableStoreCountStyled>
    );
};

export default ProductAvailableStoresCount;
