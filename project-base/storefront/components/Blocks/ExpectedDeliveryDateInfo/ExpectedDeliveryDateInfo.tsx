import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { useExpectedDeliveryDateMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TIDs } from 'cypress/tids';
import { useId } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ExpectedDeliveryDateInfoProps = {
    expectedDeliveryDate: string | null;
    isPersonalPickup?: boolean;
    unknownDeliveryDateExplanation?: string;
};

export const ExpectedDeliveryDateInfo: FC<ExpectedDeliveryDateInfoProps> = ({
    expectedDeliveryDate,
    isPersonalPickup = false,
    unknownDeliveryDateExplanation,
    className,
}) => {
    const { t } = useTranslation();
    const explanationId = useId();
    const expectedDeliveryDateMessage = useExpectedDeliveryDateMessage(expectedDeliveryDate, isPersonalPickup);

    if (expectedDeliveryDateMessage !== null) {
        return (
            <div
                className={twMergeCustom('font-secondary font-semibold text-sm text-text-success', className)}
                data-tid={TIDs.expected_delivery_date_message}
            >
                {expectedDeliveryDateMessage}
            </div>
        );
    }

    const resolvedUnknownDeliveryDateExplanation =
        unknownDeliveryDateExplanation ??
        t('The cart contains goods that are out of stock and we do not know their restocking date yet');

    return (
        <Tooltip label={resolvedUnknownDeliveryDateExplanation} placement="bottom">
            <div
                aria-describedby={explanationId}
                className={twMergeCustom('flex items-center gap-1 text-text-less text-xs', className)}
                // biome-ignore lint/a11y/noNoninteractiveTabindex: The focus makes the tooltip explanation reachable for keyboard users.
                tabIndex={0}
            >
                <InfoIcon className="size-3.5 shrink-0" />
                {isPersonalPickup
                    ? t('The pickup date cannot be determined')
                    : t('The delivery date cannot be determined')}
                <span className="sr-only" id={explanationId}>
                    {resolvedUnknownDeliveryDateExplanation}
                </span>
            </div>
        </Tooltip>
    );
};
