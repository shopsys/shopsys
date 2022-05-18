import { ProductAvailableStoreCountStyled } from './ProductAvailableStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

type ProductAvailableStoresCountProps = {
    isMainVariant: boolean;
    availableStoresCount: number;
};

const ProductAvailableStoresCount: FC<ProductAvailableStoresCountProps> = (props) => {
    const testIdentifier = 'blocks-product-availability';

    const t = useTypedTranslationFunction();

    if (props.isMainVariant || props.availableStoresCount === 0) {
        return null;
    }

    return (
        <ProductAvailableStoreCountStyled data-testid={testIdentifier}>
            {t('This item is available immediately in {{ count }} stores', { count: props.availableStoresCount })}
        </ProductAvailableStoreCountStyled>
    );
};

export default ProductAvailableStoresCount;
