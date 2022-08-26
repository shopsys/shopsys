import { ProductAvailableStoreCountStyled } from './ProductAvailableStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

type ProductAvailableStoresCountProps = {
    isMainVariant: boolean;
    availableStoresCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-availability';

export const ProductAvailableStoresCount: FC<ProductAvailableStoresCountProps> = ({
    availableStoresCount,
    isMainVariant,
}) => {
    const t = useTypedTranslationFunction();

    if (isMainVariant || availableStoresCount === 0) {
        return null;
    }

    return (
        <ProductAvailableStoreCountStyled data-testid={TEST_IDENTIFIER}>
            {t('This item is available immediately in {{ count }} stores', { count: availableStoresCount })}
        </ProductAvailableStoreCountStyled>
    );
};
