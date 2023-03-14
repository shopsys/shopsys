import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

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
        <div className="mb-3 text-sm text-inStock" data-testid={TEST_IDENTIFIER}>
            {t('This item is available immediately in {{ count }} stores', { count: availableStoresCount })}
        </div>
    );
};
