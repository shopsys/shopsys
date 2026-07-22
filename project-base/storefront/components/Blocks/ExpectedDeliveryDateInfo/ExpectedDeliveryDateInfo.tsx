import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { useExpectedDeliveryDateMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { useId } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ExpectedDeliveryDateInfoProps = {
    expectedDeliveryDate: string | null;
    isPersonalPickup?: boolean;
};

export const ExpectedDeliveryDateInfo: FC<ExpectedDeliveryDateInfoProps> = ({
    expectedDeliveryDate,
    isPersonalPickup = false,
    className,
}) => {
    const { t } = useTranslation();
    const explanationId = useId();
    const expectedDeliveryDateMessage = useExpectedDeliveryDateMessage(expectedDeliveryDate, isPersonalPickup);

    if (expectedDeliveryDateMessage !== null) {
        return (
            <div className={twMergeCustom('text-sm text-text-success', className)}>{expectedDeliveryDateMessage}</div>
        );
    }

    const unknownDeliveryDateExplanation = t(
        'The cart contains goods that are out of stock and we do not know their restocking date yet',
    );

    return (
        <Tooltip label={unknownDeliveryDateExplanation} placement="bottom">
            <div
                aria-describedby={explanationId}
                className={twMergeCustom('flex items-center gap-1 text-text-less text-xs', className)}
                tabIndex={0}
            >
                <InfoIcon className="size-3.5 shrink-0" />
                {isPersonalPickup
                    ? t('The pickup date cannot be determined')
                    : t('The delivery date cannot be determined')}
                <span className="sr-only" id={explanationId}>
                    {unknownDeliveryDateExplanation}
                </span>
            </div>
        </Tooltip>
    );
};
