import { ProductExposedStoreCountStyled } from './ProductExposedStoresCount.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';

type ProductExposedStoresCountProps = {
    isMainVariant: boolean;
    exposedStoresCount: number;
};

const ProductExposedStoresCount: FC<ProductExposedStoresCountProps> = (props) => {
    const testIdentifier = 'blocks-product-exposed';

    const t = useTypedTranslationFunction();

    if (props.isMainVariant || props.exposedStoresCount === 0) {
        return null;
    }

    return (
        <ProductExposedStoreCountStyled data-testid={testIdentifier}>
            {t('You can check this item in {{ count }} stores', { count: props.exposedStoresCount })}
        </ProductExposedStoreCountStyled>
    );
};

export default ProductExposedStoresCount;
