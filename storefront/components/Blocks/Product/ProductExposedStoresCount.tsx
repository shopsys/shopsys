import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

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
        <div className="mb-3 text-sm text-black" data-testid={TEST_IDENTIFIER}>
            {t('You can check this item in {{ count }} stores', { count: exposedStoresCount })}
        </div>
    );
};
