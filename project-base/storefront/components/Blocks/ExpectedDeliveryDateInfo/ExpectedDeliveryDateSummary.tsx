import { ClockIcon } from 'components/Basic/Icon/ClockIcon';
import { TIDs } from 'cypress/tids';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type ExpectedDeliveryDateSummaryProps = {
    expectedDeliveryDate: string | null | undefined;
    className?: string;
};

export const ExpectedDeliveryDateSummary: FC<ExpectedDeliveryDateSummaryProps> = ({
    expectedDeliveryDate,
    className,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    if (expectedDeliveryDate === undefined) {
        return null;
    }

    return (
        <div
            className={twMergeCustom(
                'flex items-center justify-between gap-3 rounded-xl bg-background-more p-4 font-secondary text-sm',
                className,
            )}
            data-tid={TIDs.expected_delivery_date_summary}
        >
            <span className="flex items-center gap-2 font-semibold">
                <ClockIcon className="size-5 shrink-0" />
                {t('Expected delivery date')}
            </span>

            <span className="text-right font-bold">
                {expectedDeliveryDate ? formatDate(expectedDeliveryDate) : t('The delivery date cannot be determined')}
            </span>
        </div>
    );
};
