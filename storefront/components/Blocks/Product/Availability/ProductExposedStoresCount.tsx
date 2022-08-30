import { ProductExposedStoreCountStyled } from './ProductExposedStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type ProductExposedStoresCountProps = {
    isMainVariant: boolean;
    exposedStoresCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-exposed';

export const ProductExposedStoresCount: FC<ProductExposedStoresCountProps> = ({
    exposedStoresCount,
    isMainVariant,
}) => {
    const t = useTypedTranslationFunction();

    if (isMainVariant || exposedStoresCount === 0) {
        return null;
    }

    return (
        <ProductExposedStoreCountStyled data-testid={TEST_IDENTIFIER}>
            {t('You can check this item in {{ count }} stores', { count: exposedStoresCount })}
        </ProductExposedStoreCountStyled>
    );
};
