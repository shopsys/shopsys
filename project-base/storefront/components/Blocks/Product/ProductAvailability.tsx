import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductAvailabilityProps = {
    availability: TypeAvailability;
    availableStoresCount: number | null;
    isInquiryType: boolean;
    onClick?: () => void;
    tabIndex?: number;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    availability,
    availableStoresCount,
    className,
    isInquiryType,
    onClick,
    tabIndex,
}) => {
    const { t } = useTranslation();

    return (
        <button
            tabIndex={tabIndex}
            title={t('Show stores availability')}
            className={twJoin(
                className,
                'text-left text-sm',
                availability.status === TypeAvailabilityStatusEnum.InStock && 'text-availability-in-stock',
                availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-availability-out-of-stock',
            )}
            onClick={onClick}
        >
            {!isInquiryType &&
                `${availability.name}${
                    availability.status !== TypeAvailabilityStatusEnum.OutOfStock && availableStoresCount !== null
                        ? `, ${t('ready to ship immediately')} ${availableStoresCount !== 0 ? t('or at {{ count }} stores', { count: availableStoresCount }) : ''}`
                        : ''
                }`}
        </button>
    );
};
