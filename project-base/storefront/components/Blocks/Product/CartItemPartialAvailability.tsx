import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type CartItemPartialAvailabilityProps = {
    stockQuantity: number;
    unitName: string;
    expectedRestockingDate: string | null;
};

export const CartItemPartialAvailability: FC<CartItemPartialAvailabilityProps> = ({
    className,
    stockQuantity,
    unitName,
    expectedRestockingDate,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    return (
        <div className={twMergeCustom('flex flex-col text-left', className)}>
            <span className="text-availability-in-stock">
                {t('In stock {{ quantity }} {{ unit }}', { quantity: stockQuantity, unit: unitName })}
            </span>
            {expectedRestockingDate ? (
                <span className="text-availability-expected-restock">
                    {t('More expected on {{ date }}', { date: formatDate(expectedRestockingDate) })}
                </span>
            ) : (
                <span className="text-availability-out-of-stock">{t('More currently unavailable')}</span>
            )}
        </div>
    );
};
