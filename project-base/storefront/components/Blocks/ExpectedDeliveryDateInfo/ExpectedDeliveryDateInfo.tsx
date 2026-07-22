import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { useExpectedDeliveryDateMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
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
    const expectedDeliveryDateMessage = useExpectedDeliveryDateMessage(expectedDeliveryDate, isPersonalPickup);

    if (expectedDeliveryDateMessage !== null) {
        return (
            <div className={twMergeCustom('text-sm text-text-success', className)}>{expectedDeliveryDateMessage}</div>
        );
    }

    return (
        <div
            className={twMergeCustom('flex items-center gap-1 text-sm text-text-less', className)}
            title={t('The cart contains goods that are out of stock and we do not know their restocking date yet')}
        >
            <InfoIcon className="size-4 shrink-0" />
            {t('The delivery date cannot be determined')}
        </div>
    );
};
