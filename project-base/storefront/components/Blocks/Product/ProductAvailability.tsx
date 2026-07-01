import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { useId } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

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
    const availabilityId = useId();
    const availabilityText = getProductAvailabilityText(availability, availableStoresCount, isInquiryType, t);

    const handleKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (e.key === 'Enter' || e.key === ' ') {
            onClick?.();
        }
    };

    const isInteractive = onClick !== undefined && availability.status === TypeAvailabilityStatusEnum.InStock;

    return (
        <div
            {...(isInteractive && {
                'aria-describedby': availabilityId,
                'aria-haspopup': 'dialog',
                'aria-label': t('Open stores availability popup', { ns: 'accessibility' }),
                role: 'button',
                tabIndex: 0,
                title: t('Show stores availability'),
                onClick: onClick,
                onKeyDown: handleKeyDown,
            })}
            className={twJoin(
                className,
                'flex text-left text-sm',
                availability.status === TypeAvailabilityStatusEnum.InStock && 'text-availability-in-stock',
                availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-availability-out-of-stock',
            )}
        >
            {availabilityText && <span id={availabilityId}>{availabilityText}</span>}
        </div>
    );
};

const getProductAvailabilityText = (
    availability: TypeAvailability,
    availableStoresCount: number | null,
    isInquiryType: boolean,
    t: ReturnType<typeof useTranslation>['t'],
): string | null => {
    if (isInquiryType) {
        return null;
    }

    return `${availability.name}${
        availability.status !== TypeAvailabilityStatusEnum.OutOfStock && availableStoresCount !== null
            ? `, ${t('ready to ship immediately')} ${availableStoresCount !== 0 ? t('or at {{ count }} stores', { count: availableStoresCount }) : ''}`
            : ''
    }`;
};
