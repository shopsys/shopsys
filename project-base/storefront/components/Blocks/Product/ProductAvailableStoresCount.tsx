import useTranslation from 'next-translate/useTranslation';

type ProductAvailableStoresCountProps = {
    isMainVariant: boolean;
    availableStoresCount: number;
    name: string;
};

export const ProductAvailableStoresCount: FC<ProductAvailableStoresCountProps> = ({
    availableStoresCount,
    isMainVariant,
    name,
}) => {
    const { t } = useTranslation();

    if (isMainVariant) {
        return null;
    }

    return (
        <div className="text-sm text-availabilityInStock">
            {`${name}, ${t('ready to ship immediately')} ${availableStoresCount !== 0 ? t('or at {{ count }} stores', { count: availableStoresCount }) : ''}`}
        </div>
    );
};
