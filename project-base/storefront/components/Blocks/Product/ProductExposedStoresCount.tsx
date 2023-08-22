import useTranslation from 'next-translate/useTranslation';

type ProductExposedStoresCountProps = {
    isMainVariant: boolean;
    exposedStoresCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-exposed';

export const ProductExposedStoresCount: FC<ProductExposedStoresCountProps> = ({
    exposedStoresCount,
    isMainVariant,
}) => {
    const { t } = useTranslation();

    if (isMainVariant || exposedStoresCount === 0) {
        return null;
    }

    return (
        <div className="mb-3 text-sm text-black" data-testid={TEST_IDENTIFIER}>
            {t('You can check this item in {{ count }} stores', { count: exposedStoresCount })}
        </div>
    );
};
