import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { useId } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { getAvailabilityTextColorClassName } from 'utils/ui/getAvailabilityTextColorClassName';

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
            className={twMergeCustom(
                'flex text-left text-sm',
                getAvailabilityTextColorClassName(availability.status),
                className,
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
        availability.status === TypeAvailabilityStatusEnum.InStock && availableStoresCount !== null
            ? `, ${t('ready to ship immediately')} ${availableStoresCount !== 0 ? t('or at {{ count }} stores', { count: availableStoresCount }) : ''}`
            : ''
    }`;
};
