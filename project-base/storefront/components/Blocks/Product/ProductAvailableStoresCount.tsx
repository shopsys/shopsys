import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';

type ProductAvailableStoresCountProps = {
    isMainVariant: boolean;
    availableStoresCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-availability';

export const ProductAvailableStoresCount: FC<ProductAvailableStoresCountProps> = ({
    availableStoresCount,
    isMainVariant,
    className,
}) => {
    const { t } = useTranslation();

    if (isMainVariant || availableStoresCount === 0) {
        return null;
    }

    return (
        <div className={twMergeCustom('text-sm  text-inStock', className)} data-testid={TEST_IDENTIFIER}>
            {t('This item is available immediately in {{ count }} stores', { count: availableStoresCount })}
        </div>
    );
};
