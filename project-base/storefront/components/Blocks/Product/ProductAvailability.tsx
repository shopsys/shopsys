import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductAvailabilityProps = {
    availability: TypeAvailability;
    availableStoresCount: number | null;
    isInquiryType: boolean;
    onClick?: () => void;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    availability,
    availableStoresCount,
    className,
    isInquiryType,
    onClick,
}) => {
    const { t } = useTranslation();

    return (
        <div
            className={twJoin(
                className,
                'text-sm',
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
        </div>
    );
};
